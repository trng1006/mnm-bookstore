<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    public bool $isForAdmin;

    public function __construct(Order $order, bool $isForAdmin = false)
    {
        $this->order = $order;
        $this->isForAdmin = $isForAdmin;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isForAdmin) {
            return (new MailMessage)
                ->subject('[MNM Bookstore] Đơn hàng mới #'.$this->order->order_number)
                ->greeting('Xin chào Quản trị viên,')
                ->line('Khách hàng '.$this->order->customer_name.' vừa đặt một đơn hàng mới.')
                ->line('Mã đơn hàng: '.$this->order->order_number)
                ->line('Tổng thanh toán: '.number_format($this->order->total_amount, 0, ',', '.').' ₫')
                ->line('Phương thức thanh toán: '.$this->order->payment_method_label)
                ->action('Xem chi tiết đơn hàng', route('admin.orders.show', $this->order->id))
                ->line('Vui lòng kiểm tra và xử lý đơn hàng sớm nhất!');
        }

        return (new MailMessage)
            ->subject('[MNM Bookstore] Đặt hàng thành công #'.$this->order->order_number)
            ->greeting('Xin chào '.$this->order->customer_name.',')
            ->line('Cảm ơn bạn đã đặt hàng tại MNM Bookstore!')
            ->line('Mã đơn hàng của bạn là: **#'.$this->order->order_number.'**')
            ->line('Tổng thanh toán: **'.number_format($this->order->total_amount, 0, ',', '.').' ₫**')
            ->line('Địa chỉ nhận hàng: '.$this->order->shipping_address)
            ->line('Phương thức thanh toán: '.$this->order->payment_method_label)
            ->action('Theo dõi đơn hàng', route('account.orders.show', $this->order->order_number))
            ->line('Chúng tôi sẽ nhanh chóng chuẩn bị và giao sách đến bạn!');
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->isForAdmin) {
            return [
                'title' => 'Đơn hàng mới #'.$this->order->order_number,
                'message' => 'Khách hàng '.$this->order->customer_name.' vừa đặt đơn hàng trị giá '.number_format($this->order->total_amount, 0, ',', '.').' ₫',
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'url' => route('admin.orders.show', $this->order->id),
                'type' => 'new_order',
            ];
        }

        return [
            'title' => 'Đặt hàng thành công #'.$this->order->order_number,
            'message' => 'Đơn hàng của bạn đã được ghi nhận và đang chờ xử lý.',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'url' => route('account.orders.show', $this->order->order_number),
            'type' => 'order_placed',
        ];
    }
}
