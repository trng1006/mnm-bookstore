<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    public string $oldStatus;

    public string $newStatus;

    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->order->status_label;

        return (new MailMessage)
            ->subject('[MNM Bookstore] Cập nhật đơn hàng #'.$this->order->order_number.': '.$statusLabel)
            ->greeting('Xin chào '.$this->order->customer_name.',')
            ->line('Đơn hàng **#'.$this->order->order_number.'** của bạn vừa được cập nhật trạng thái mới:')
            ->line('Trạng thái hiện tại: **'.$statusLabel.'**')
            ->action('Xem chi tiết tiến trình đơn', route('account.orders.show', $this->order->order_number))
            ->line('Cảm ơn bạn đã đồng hành cùng MNM Bookstore!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Đơn hàng #'.$this->order->order_number.' - '.$this->order->status_label,
            'message' => 'Đơn hàng của bạn đã chuyển sang trạng thái: '.$this->order->status_label,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'url' => route('account.orders.show', $this->order->order_number),
            'type' => 'status_updated',
        ];
    }
}
