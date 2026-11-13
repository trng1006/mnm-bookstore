<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayOSPaymentTest extends TestCase
{
    use RefreshDatabase;

    private string $checksumKey = 'test_checksum_key_64_chars_example_1234567890abcdef1234567890';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.payos', [
            'client_id' => 'test-client-id',
            'api_key' => 'test-api-key',
            'checksum_key' => $this->checksumKey,
            'base_url' => 'https://api-merchant.payos.vn',
        ]);
    }

    private function createBankTransferOrder(?User $user = null): Order
    {
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $user?->id,
            'customer_name' => $user?->name ?? 'Khach Test',
            'customer_email' => $user?->email ?? 'guest@example.com',
            'customer_phone' => '0901234567',
            'shipping_address' => '140 Le Trong Tan, Tan Phu',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'status' => Order::STATUS_PENDING,
            'subtotal' => 150000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 150000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => null,
            'book_title' => 'Lap trinh Ma nguon mo',
            'price' => 150000,
            'quantity' => 1,
            'total' => 150000,
        ]);

        return $order;
    }

    public function test_create_payment_link_renders_in_app_vietqr_page(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->createBankTransferOrder($user);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests' => Http::response([
                'code' => '00',
                'desc' => 'success',
                'data' => [
                    'bin' => '970422',
                    'accountNumber' => '113366668888',
                    'accountName' => 'NGUYEN VAN A',
                    'amount' => 150000,
                    'description' => 'TT ' . $order->order_number,
                    'paymentLinkId' => 'payos_link_001',
                    'qrCode' => '00020101021238570010A0000007270127000697042201131133666688886304ABCD',
                    'checkoutUrl' => 'https://pay.payos.vn/web/payos_link_001',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('checkout.payos.pay', $order->order_number));

        $response->assertOk();
        $response->assertViewIs('client.pages.checkout.payos');
        $response->assertSee('113366668888');
        $response->assertSee('NGUYEN VAN A');
        $response->assertSee('TT ' . $order->order_number);

        $order->refresh();
        $this->assertNotNull($order->payos_order_code);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'payos',
            'transaction_id' => 'payos_link_001',
            'status' => 'pending',
        ]);
    }

    public function test_check_payment_status_endpoint_marks_order_paid_when_payos_confirms(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->createBankTransferOrder($user);
        $order->update(['payos_order_code' => 261113123456555]);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests/261113123456555' => Http::response([
                'code' => '00',
                'desc' => 'success',
                'data' => [
                    'id' => 'payos_link_005',
                    'orderCode' => 261113123456555,
                    'amount' => 150000,
                    'amountPaid' => 150000,
                    'status' => 'PAID',
                    'transactions' => [
                        ['reference' => 'MB2611135555'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson(route('checkout.payos.status', $order->order_number));

        $response->assertOk()->assertJson([
            'paid' => true,
            'status' => 'PAID',
            'redirect' => route('checkout.success', $order->order_number),
        ]);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
    }

    public function test_return_url_verifies_paid_status_and_confirms_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->createBankTransferOrder($user);
        $order->update(['payos_order_code' => 261113123456111]);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'payos_link_002',
            'provider' => 'payos',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests/261113123456111' => Http::response([
                'code' => '00',
                'desc' => 'success',
                'data' => [
                    'id' => 'payos_link_002',
                    'orderCode' => 261113123456111,
                    'amount' => 150000,
                    'amountPaid' => 150000,
                    'status' => 'PAID',
                    'transactions' => [
                        ['reference' => 'FT2611139999'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('checkout.payos.return', [
            'orderCode' => 261113123456111,
            'status' => 'PAID',
        ]));

        $response->assertRedirect(route('checkout.success', $order->order_number));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'payos',
            'transaction_id' => 'FT2611139999',
            'status' => 'success',
        ]);
    }

    public function test_cancel_url_marks_order_payment_failed_for_retry(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->createBankTransferOrder($user);
        $order->update(['payos_order_code' => 261113123456222]);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests/261113123456222' => Http::response([
                'code' => '00',
                'desc' => 'success',
                'data' => [
                    'id' => 'payos_link_003',
                    'orderCode' => 261113123456222,
                    'amount' => 150000,
                    'amountPaid' => 0,
                    'status' => 'CANCELLED',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('checkout.payos.cancel', [
            'orderCode' => 261113123456222,
            'cancel' => 'true',
        ]));

        $response->assertRedirect(route('checkout.success', $order->order_number));

        $order->refresh();
        $this->assertSame('failed', $order->payment_status);
        $this->assertSame(Order::STATUS_PAYMENT_FAILED, $order->status);
    }

    public function test_webhook_rejects_invalid_signature_and_accepts_valid_signature(): void
    {
        $order = $this->createBankTransferOrder();
        $order->update(['payos_order_code' => 261113123456333]);

        $data = [
            'orderCode' => 261113123456333,
            'amount' => 150000,
            'description' => 'TT ' . $order->order_number,
            'accountNumber' => '123456789',
            'reference' => 'MB123456789',
            'transactionDateTime' => '2026-11-13 17:35:00',
            'currency' => 'VND',
            'paymentLinkId' => 'payos_link_004',
            'code' => '00',
            'desc' => 'success',
        ];

        $this->postJson(route('checkout.payos.webhook'), [
            'code' => '00',
            'desc' => 'success',
            'success' => true,
            'data' => $data,
            'signature' => 'invalid_signature',
        ])->assertStatus(401);

        $sorted = $data;
        ksort($sorted);
        $parts = [];
        foreach ($sorted as $k => $v) {
            $parts[] = $k . '=' . $v;
        }
        $validSignature = hash_hmac('sha256', implode('&', $parts), $this->checksumKey);

        $this->postJson(route('checkout.payos.webhook'), [
            'code' => '00',
            'desc' => 'success',
            'success' => true,
            'data' => $data,
            'signature' => $validSignature,
        ])->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
    }
}
