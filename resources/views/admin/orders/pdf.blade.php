<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hóa Đơn Đơn Hàng #{{ $order->order_number }}</title>
    <style>
        /* Sử dụng font DejaVu Sans mặc định của DOMPDF hỗ trợ tiếng Việt Unicode */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #e11d48; margin-bottom: 5px; }
        .title { font-size: 20px; font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .info-title { font-weight: bold; color: #666; font-size: 12px; text-transform: uppercase; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .total-row td { font-weight: bold; font-size: 14px; background-color: #f8f9fa; }
        .footer { margin-top: 50px; text-align: center; font-style: italic; color: #777; }
        .signature-box { width: 100%; margin-top: 50px; }
        .signature-box td { text-align: center; width: 50%; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">MNM BOOKSTORE</div>
        <div>Địa chỉ: 140 Lê Trọng Tấn, P. Tây Thạnh, Q. Tân Phú, TP.HCM</div>
        <div>Hotline: 0123 456 789 | Email: mnmbookstore@huit.edu.vn</div>
        <div class="title">HÓA ĐƠN BÁN LẺ</div>
        <div>Mã đơn: <strong>{{ $order->order_number }}</strong> - Ngày: {{ $order->created_at->format('d/m/Y') }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <div class="info-title">THÔNG TIN KHÁCH HÀNG</div>
                <strong>{{ $order->customer_name }}</strong><br>
                SĐT: {{ $order->customer_phone }}<br>
                Email: {{ $order->customer_email ?? 'Không có' }}
            </td>
            <td width="50%">
                <div class="info-title">ĐỊA CHỈ GIAO HÀNG</div>
                {{ $order->shipping_address }}<br>
                <div class="info-title" style="margin-top: 10px;">PHƯƠNG THỨC THANH TOÁN</div>
                {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : ($order->payment_method === 'vnpay' ? 'Thanh toán qua VNPAY' : 'Thanh toán qua MoMo') }}
                - <strong style="color: {{ $order->payment_status === 'paid' ? 'green' : 'red' }}">
                    {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                </strong>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">STT</th>
                <th width="45%">Tên sách</th>
                <th width="15%" class="text-center">Đơn giá</th>
                <th width="10%" class="text-center">SL</th>
                <th width="25%" class="text-right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->book_title }}</td>
                <td class="text-center">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-right">TỔNG CỘNG:</td>
                <td class="text-right" style="color: #e11d48;">{{ number_format($order->total_amount, 0, ',', '.') }} VNĐ</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-box">
        <tr>
            <td>Khách hàng<br><br><br><br><br></td>
            <td>Người lập phiếu<br><br><br><br><br></td>
        </tr>
    </table>

    <div class="footer">
        Cảm ơn quý khách đã mua hàng tại MNM Bookstore!<br>
        Xin vui lòng giữ lại hóa đơn để đối chiếu khi cần thiết.
    </div>
</body>
</html>
