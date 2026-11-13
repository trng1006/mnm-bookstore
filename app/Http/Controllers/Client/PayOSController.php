<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PayOSController extends Controller
{
    /**
     * Hiển thị trang chuyển khoản VietQR trực tiếp trên web của cửa hàng (lấy số tài khoản, nội dung CK và QR từ payOS).
     */
    public function createPaymentLink(string $orderNumber): View|RedirectResponse
    {
        $order = $this->findAccessibleOrder($orderNumber);

        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('success', 'Đơn hàng này đã được thanh toán trước đó.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('error', 'Đơn hàng đã bị hủy, không thể tiếp tục thanh toán.');
        }

        if ($order->status === Order::STATUS_PAYMENT_FAILED) {
            try {
                DB::transaction(function () use ($order) {
                    $lockedOrder = Order::with('items')->where('id', $order->id)->lockForUpdate()->first();
                    if ($lockedOrder && $lockedOrder->status === Order::STATUS_PAYMENT_FAILED) {
                        $lockedOrder->reserveStockAndCoupon();
                        $lockedOrder->update([
                            'status' => Order::STATUS_PENDING,
                            'payment_status' => 'pending',
                        ]);
                        OrderStatusHistory::create([
                            'order_id' => $lockedOrder->id,
                            'changed_by' => auth()->id(),
                            'from_status' => Order::STATUS_PAYMENT_FAILED,
                            'to_status' => Order::STATUS_PENDING,
                            'note' => 'Khách hàng thử thanh toán lại qua payOS',
                        ]);
                    }
                });
                $order->refresh();
            } catch (\RuntimeException $e) {
                if (str_starts_with($e->getMessage(), 'InsufficientStock: ')) {
                    return redirect()
                        ->route('checkout.success', $order->order_number)
                        ->with('error', str_replace('InsufficientStock: ', '', $e->getMessage()));
                }
                throw $e;
            }
        }

        if ($order->total_amount <= 0) {
            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('info', 'Đơn hàng có tổng thanh toán bằng 0 ₫.');
        }

        if (!$this->isPayOSConfigured()) {
            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('error', 'Chưa cấu hình đầy đủ PAYOS_CLIENT_ID, PAYOS_API_KEY hoặc PAYOS_CHECKSUM_KEY trong file .env.');
        }

        // Nếu đơn đã có phiên QR đang chờ thanh toán (tránh tạo mã mới khi người dùng bấm F5), tái sử dụng lại
        $existingTransaction = PaymentTransaction::where('order_id', $order->id)
            ->where('provider', 'payos')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existingTransaction && !empty($existingTransaction->gateway_response['qrCode']) && $order->payos_order_code) {
            $statusResponse = $this->callPayOSApi('get', '/v2/payment-requests/' . $order->payos_order_code);
            $gatewayStatus = strtoupper((string) ($statusResponse['data']['status'] ?? ''));

            if ($gatewayStatus === 'PAID' && (int) ($statusResponse['data']['amountPaid'] ?? 0) >= (int) $order->total_amount) {
                $this->markOrderAsPaid($order, $statusResponse['data'], 'Thanh toán thành công qua payOS');

                return redirect()
                    ->route('checkout.success', $order->order_number)
                    ->with('success', 'Thanh toán thành công! Đơn hàng của bạn đã được xác nhận.');
            }

            if ($gatewayStatus === 'PENDING') {
                return $this->renderPaymentPage($order, $existingTransaction->gateway_response);
            }
        }

        // payOS yêu cầu orderCode là số nguyên dương duy nhất cho mỗi lần tạo yêu cầu thanh toán
        $payosOrderCode = (int) (now()->format('ymdHis') . random_int(100, 999));
        $order->update(['payos_order_code' => $payosOrderCode]);

        $amount = (int) $order->total_amount;
        $description = substr('TT ' . $order->order_number, 0, 25);
        $returnUrl = route('checkout.payos.return');
        $cancelUrl = route('checkout.payos.cancel');
        $checksumKey = (string) config('services.payos.checksum_key');

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => (string) $item->book_title,
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
            ];
        }

        $payload = [
            'orderCode' => $payosOrderCode,
            'amount' => $amount,
            'description' => $description,
            'items' => $items,
            'cancelUrl' => $cancelUrl,
            'returnUrl' => $returnUrl,
            'signature' => $this->buildPaymentSignature(
                $payosOrderCode,
                $amount,
                $description,
                $returnUrl,
                $cancelUrl,
                $checksumKey
            ),
        ];

        $responseBody = $this->callPayOSApi('post', '/v2/payment-requests', $payload);

        if (($responseBody['code'] ?? '') === '00' && !empty($responseBody['data'])) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'transaction_id' => $responseBody['data']['paymentLinkId'] ?? null,
                'provider' => 'payos',
                'amount' => $order->total_amount,
                'status' => 'pending',
                'gateway_response' => $responseBody['data'],
            ]);

            return $this->renderPaymentPage($order, $responseBody['data']);
        }

        Log::error('PayOS createPaymentLink failed', [
            'order_number' => $order->order_number,
            'response' => $responseBody,
        ]);

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('error', 'Không thể tạo thông tin chuyển khoản từ payOS: ' . ($responseBody['desc'] ?? 'Lỗi không xác định.'));
    }

    /**
     * API AJAX để trang thanh toán tự động kiểm tra tiền đã về chưa (polling mỗi 3 giây).
     */
    public function checkPaymentStatus(string $orderNumber): JsonResponse
    {
        $order = $this->findAccessibleOrder($orderNumber);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'paid' => true,
                'status' => 'PAID',
                'redirect' => route('checkout.success', $order->order_number),
            ]);
        }

        if (!$order->payos_order_code) {
            return response()->json(['paid' => false, 'status' => 'PENDING']);
        }

        $responseBody = $this->callPayOSApi('get', '/v2/payment-requests/' . $order->payos_order_code);
        $gatewayData = $responseBody['data'] ?? [];
        $gatewayStatus = strtoupper((string) ($gatewayData['status'] ?? 'PENDING'));
        $amountPaid = (int) ($gatewayData['amountPaid'] ?? 0);

        if ($gatewayStatus === 'PAID' && $amountPaid >= (int) $order->total_amount) {
            $this->markOrderAsPaid($order, $gatewayData, 'Thanh toán thành công qua chuyển khoản VietQR (payOS)');

            return response()->json([
                'paid' => true,
                'status' => 'PAID',
                'redirect' => route('checkout.success', $order->order_number),
            ]);
        }

        if ($gatewayStatus === 'CANCELLED') {
            $this->markOrderAsFailed($order, $gatewayData, 'Yêu cầu thanh toán đã bị hủy trên payOS');

            return response()->json([
                'paid' => false,
                'cancelled' => true,
                'status' => 'CANCELLED',
                'redirect' => route('checkout.success', $order->order_number),
            ]);
        }

        return response()->json([
            'paid' => false,
            'status' => $gatewayStatus,
        ]);
    }

    /**
     * Khách hàng chủ động bấm Hủy thanh toán ngay trên trang QR của cửa hàng.
     */
    public function cancelPayment(string $orderNumber): RedirectResponse
    {
        $order = $this->findAccessibleOrder($orderNumber);

        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('success', 'Đơn hàng này đã được thanh toán thành công.');
        }

        $gatewayData = [];
        if ($order->payos_order_code) {
            $responseBody = $this->callPayOSApi('post', '/v2/payment-requests/' . $order->payos_order_code . '/cancel', [
                'cancellationReason' => 'Khách hàng hủy giao dịch trên website',
            ]);
            $gatewayData = $responseBody['data'] ?? [];
        }

        $this->markOrderAsFailed($order, $gatewayData, 'Khách hàng hủy thanh toán chuyển khoản');

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('error', 'Bạn đã hủy giao dịch thanh toán. Bạn có thể bấm nút bên dưới để thanh toán lại.');
    }

    /**
     * Đón khách hàng quay lại sau khi thanh toán thành công trên payOS (fallback nếu mở link payOS trực tiếp).
     */
    public function handleReturn(Request $request): RedirectResponse
    {
        return $this->handleRedirectCallback($request, false);
    }

    /**
     * Đón khách hàng quay lại khi bấm Hủy thanh toán trên payOS (fallback).
     */
    public function handleCancel(Request $request): RedirectResponse
    {
        return $this->handleRedirectCallback($request, true);
    }

    /**
     * Nhận Webhook tự động từ payOS khi giao dịch chuyển khoản hoàn tất.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        if (!$this->isPayOSConfigured()) {
            return response()->json(['success' => false, 'message' => 'PayOS is not configured'], 500);
        }

        $payload = $request->all();
        $checksumKey = (string) config('services.payos.checksum_key');

        if (!$this->verifyWebhookSignature($payload, $checksumKey)) {
            Log::warning('PayOS webhook invalid signature', ['payload' => $payload]);

            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $data = $payload['data'] ?? [];
        $payosOrderCode = (int) ($data['orderCode'] ?? 0);
        $order = Order::where('payos_order_code', $payosOrderCode)->first();

        if (!$order) {
            return response()->json(['success' => true, 'message' => 'Webhook verified'], 200);
        }

        $isPaidEvent = ($payload['code'] ?? '') === '00' && (($payload['success'] ?? true) !== false);
        $paidAmount = (int) ($data['amount'] ?? 0);

        if ($isPaidEvent && $paidAmount >= (int) $order->total_amount) {
            $this->markOrderAsPaid($order, $data, 'Thanh toán tự động xác nhận qua Webhook payOS');
        }

        return response()->json(['success' => true], 200);
    }

    /**
     * Chuẩn bị dữ liệu ngân hàng và hiển thị trang thanh toán QR nội bộ của MNM Bookstore.
     */
    private function renderPaymentPage(Order $order, array $gatewayData): View
    {
        $bin = (string) ($gatewayData['bin'] ?? '');
        $bankName = $this->resolveBankName($bin);

        return view('client.pages.checkout.payos', [
            'order' => $order,
            'bankName' => $bankName,
            'bankBin' => $bin,
            'accountNumber' => (string) ($gatewayData['accountNumber'] ?? ''),
            'accountName' => (string) ($gatewayData['accountName'] ?? ''),
            'amount' => (int) ($gatewayData['amount'] ?? $order->total_amount),
            'description' => (string) ($gatewayData['description'] ?? ''),
            'qrCode' => (string) ($gatewayData['qrCode'] ?? ''),
        ]);
    }

    /**
     * Quy đổi mã BIN ngân hàng Việt Nam sang tên ngân hàng hiển thị cho khách.
     */
    private function resolveBankName(string $bin): string
    {
        $banks = [
            '970422' => 'MB Bank (Quân Đội)',
            '970415' => 'VietinBank',
            '970436' => 'Vietcombank',
            '970418' => 'BIDV',
            '970407' => 'Techcombank',
            '970416' => 'ACB (Á Châu)',
            '970448' => 'OCB (Phương Đông)',
            '970423' => 'TPBank',
            '970432' => 'VPBank',
            '970403' => 'Sacombank',
            '970405' => 'Agribank',
            '970437' => 'HDBank',
            '970441' => 'VIB',
            '970443' => 'SHB',
            '970426' => 'MSB',
            '970454' => 'BVBank / Timo',
            '970429' => 'SCB',
            '970431' => 'Eximbank',
            '970406' => 'DongA Bank',
            '970440' => 'SeABank',
        ];

        return $banks[$bin] ?? ('Ngân hàng (BIN: ' . $bin . ')');
    }

    /**
     * Xử lý chung cho Return URL và Cancel URL, luôn kiểm tra lại trạng thái thật từ API payOS.
     */
    private function handleRedirectCallback(Request $request, bool $isCancelFlow): RedirectResponse
    {
        $payosOrderCode = $request->integer('orderCode');

        if ($payosOrderCode <= 0) {
            return redirect()->route('home')->with('error', 'Thiếu mã giao dịch payOS.');
        }

        $order = Order::where('payos_order_code', $payosOrderCode)->firstOrFail();

        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('success', 'Thanh toán qua payOS thành công!');
        }

        $responseBody = $this->callPayOSApi('get', '/v2/payment-requests/' . $payosOrderCode);
        $gatewayData = $responseBody['data'] ?? [];
        $gatewayStatus = strtoupper((string) ($gatewayData['status'] ?? $request->input('status', '')));
        $amountPaid = (int) ($gatewayData['amountPaid'] ?? 0);

        if ($gatewayStatus === 'PAID' && $amountPaid >= (int) $order->total_amount) {
            $this->markOrderAsPaid($order, $gatewayData, 'Thanh toán thành công qua payOS');

            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('success', 'Thanh toán qua payOS thành công! Đơn hàng của bạn đã được xác nhận.');
        }

        if ($isCancelFlow || $gatewayStatus === 'CANCELLED' || $request->boolean('cancel')) {
            $this->markOrderAsFailed($order, $gatewayData, 'Khách hàng đã hủy thanh toán trên payOS');

            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('error', 'Bạn đã hủy giao dịch thanh toán. Bạn có thể bấm nút bên dưới để thanh toán lại.');
        }

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('warning', 'Giao dịch đang chờ ngân hàng xác nhận. Hệ thống sẽ tự động cập nhật khi nhận được tiền.');
    }

    /**
     * Đánh dấu đơn hàng đã thanh toán (đảm bảo idempotent khi nhiều luồng cùng cập nhật).
     */
    private function markOrderAsPaid(Order $order, array $gatewayData, string $note): void
    {
        DB::transaction(function () use ($order, $gatewayData, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if (!$lockedOrder || $lockedOrder->payment_status === 'paid') {
                return;
            }

            $previousStatus = $lockedOrder->status;
            $nextStatus = in_array($previousStatus, [Order::STATUS_PENDING, Order::STATUS_PAYMENT_FAILED], true)
                ? Order::STATUS_CONFIRMED
                : $previousStatus;

            if ($previousStatus === Order::STATUS_PAYMENT_FAILED) {
                $lockedOrder->reserveStockAndCoupon(allowPartialOnPaid: true);
            }

            $lockedOrder->update([
                'payment_status' => 'paid',
                'status' => $nextStatus,
            ]);

            if ($previousStatus !== $nextStatus) {
                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'changed_by' => null,
                    'from_status' => $previousStatus,
                    'to_status' => $nextStatus,
                    'note' => $note,
                ]);
            }

            $transactionId = $gatewayData['reference']
                ?? ($gatewayData['transactions'][0]['reference'] ?? null)
                ?? ($gatewayData['paymentLinkId'] ?? $gatewayData['id'] ?? null);

            $transaction = PaymentTransaction::where('order_id', $lockedOrder->id)
                ->where('provider', 'payos')
                ->latest()
                ->first();

            if ($transaction) {
                $transaction->update([
                    'transaction_id' => $transactionId ?: $transaction->transaction_id,
                    'status' => 'success',
                    'gateway_response' => $gatewayData,
                ]);
            } else {
                PaymentTransaction::create([
                    'order_id' => $lockedOrder->id,
                    'transaction_id' => $transactionId,
                    'provider' => 'payos',
                    'amount' => $lockedOrder->total_amount,
                    'status' => 'success',
                    'gateway_response' => $gatewayData,
                ]);
            }
        });
    }

    /**
     * Đánh dấu giao dịch bị hủy/thất bại để khách có thể thanh toán lại.
     */
    private function markOrderAsFailed(Order $order, array $gatewayData, string $note): void
    {
        DB::transaction(function () use ($order, $gatewayData, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if (!$lockedOrder || $lockedOrder->payment_status === 'paid' || $lockedOrder->status === Order::STATUS_CANCELLED) {
                return;
            }

            $previousStatus = $lockedOrder->status;

            $lockedOrder->update([
                'payment_status' => 'failed',
                'status' => $previousStatus === Order::STATUS_PENDING ? Order::STATUS_PAYMENT_FAILED : $previousStatus,
            ]);

            if ($previousStatus === Order::STATUS_PENDING) {
                $lockedOrder->releaseStockAndCoupon();

                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'changed_by' => null,
                    'from_status' => $previousStatus,
                    'to_status' => Order::STATUS_PAYMENT_FAILED,
                    'note' => $note,
                ]);
            }

            $transaction = PaymentTransaction::where('order_id', $lockedOrder->id)
                ->where('provider', 'payos')
                ->latest()
                ->first();

            if ($transaction && $transaction->status === 'pending') {
                $transaction->update([
                    'status' => 'failed',
                    'gateway_response' => $gatewayData,
                ]);
            }
        });
    }

    /**
     * Tìm đơn hàng đúng quyền sở hữu (user đang đăng nhập hoặc khách vãng lai).
     */
    private function findAccessibleOrder(string $orderNumber): Order
    {
        $query = Order::with('items')->where('order_number', $orderNumber);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereNull('user_id');
        }

        return $query->firstOrFail();
    }

    /**
     * Kiểm tra đã cấu hình đủ 3 khóa payOS hợp lệ hay chưa.
     */
    private function isPayOSConfigured(): bool
    {
        $clientId = (string) config('services.payos.client_id');
        $apiKey = (string) config('services.payos.api_key');
        $checksumKey = (string) config('services.payos.checksum_key');

        return $clientId !== ''
            && $apiKey !== ''
            && $checksumKey !== ''
            && !str_contains($checksumKey, 'PASTE_FULL_KEY');
    }

    /**
     * Gửi HTTP Request lên payOS Merchant API.
     */
    private function callPayOSApi(string $method, string $path, array $payload = []): array
    {
        $baseUrl = rtrim((string) config('services.payos.base_url', 'https://api-merchant.payos.vn'), '/');

        try {
            $request = Http::withHeaders([
                'x-client-id' => (string) config('services.payos.client_id'),
                'x-api-key' => (string) config('services.payos.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(15);

            $response = $method === 'post'
                ? $request->post($baseUrl . $path, $payload)
                : $request->get($baseUrl . $path);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('PayOS API exception: ' . $e->getMessage(), ['path' => $path]);

            return ['code' => 'ERROR', 'desc' => $e->getMessage()];
        }
    }

    /**
     * Tạo chữ ký HMAC-SHA256 theo chuẩn API tạo link thanh toán payOS.
     */
    private function buildPaymentSignature(
        int $orderCode,
        int $amount,
        string $description,
        string $returnUrl,
        string $cancelUrl,
        string $checksumKey
    ): string {
        $dataString = sprintf(
            'amount=%d&cancelUrl=%s&description=%s&orderCode=%d&returnUrl=%s',
            $amount,
            $cancelUrl,
            $description,
            $orderCode,
            $returnUrl
        );

        return hash_hmac('sha256', $dataString, $checksumKey);
    }

    /**
     * Kiểm tra chữ ký HMAC-SHA256 từ Webhook payOS gửi về.
     */
    private function verifyWebhookSignature(array $payload, string $checksumKey): bool
    {
        if (empty($payload['data']) || !is_array($payload['data']) || empty($payload['signature'])) {
            return false;
        }

        $data = $payload['data'];
        ksort($data);

        $parts = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                $stringValue = '';
            } elseif (is_bool($value)) {
                $stringValue = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $stringValue = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $stringValue = (string) $value;
            }

            $parts[] = $key . '=' . $stringValue;
        }

        $calculatedSignature = hash_hmac('sha256', implode('&', $parts), $checksumKey);

        return hash_equals($calculatedSignature, (string) $payload['signature']);
    }
}
