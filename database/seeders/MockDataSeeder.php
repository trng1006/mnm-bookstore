<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;

class MockDataSeeder extends Seeder
{
    public function run()
    {
        $customer = User::where('email', 'customer@gmail.com')->first();
        if (!$customer) {
            echo "Customer not found!\n";
            return;
        }

        $books = Book::all();
        if ($books->isEmpty()) {
            echo "No books found!\n";
            return;
        }

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['cod', 'vnpay', 'momo'];
        $paymentStatuses = ['pending', 'paid', 'failed'];

        // 1. Generate 50 Random Orders for the Dashboard & various users
        echo "Generating Orders...\n";
        for ($i = 0; $i < 50; $i++) {
            $isCustomerOrder = ($i % 3 === 0); // 1/3 of orders belong to customer@gmail.com
            
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $status === 'delivered' ? 'paid' : $paymentStatuses[array_rand($paymentStatuses)];
            if ($status === 'cancelled') $paymentStatus = 'failed';

            $orderDate = Carbon::now()->subDays(rand(0, 60))->subHours(rand(0, 23));

            $order = Order::create([
                'order_number' => 'MNM-' . $orderDate->format('Ymd') . '-' . strtoupper(Str::random(5)),
                'user_id' => $isCustomerOrder ? $customer->id : null,
                'customer_name' => $isCustomerOrder ? $customer->name : 'Khách vãng lai ' . $i,
                'customer_email' => $isCustomerOrder ? $customer->email : 'guest' . $i . '@example.com',
                'customer_phone' => $isCustomerOrder ? $customer->phone : '090' . rand(1000000, 9999999),
                'shipping_address' => $isCustomerOrder ? $customer->address : '123 Đường ' . $i . ', TP.HCM',
                'shipping_notes' => rand(0, 1) ? 'Giao giờ hành chính' : null,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'payment_status' => $paymentStatus,
                'status' => $status,
                'subtotal' => 0,
                'shipping_fee' => 30000,
                'discount_amount' => 0,
                'total_amount' => 0,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Add 1 to 4 items per order
            $itemCount = rand(1, 4);
            $subtotal = 0;
            
            for ($j = 0; $j < $itemCount; $j++) {
                $book = $books->random();
                $qty = rand(1, 2);
                $price = $book->price_current;
                $total = $price * $qty;
                $subtotal += $total;

                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'book_cover' => $book->cover_image,
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $total,
                ]);
            }

            // Apply random discount
            $discount = rand(0, 1) ? rand(10000, 50000) : 0;
            if ($discount > $subtotal) $discount = 0;
            
            $order->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $subtotal + 30000 - $discount,
            ]);
        }

        // 2. Generate Notifications for customer@gmail.com
        echo "Generating Notifications...\n";
        $notificationTypes = [
            'App\Notifications\OrderPlaced',
            'App\Notifications\OrderShipped',
            'App\Notifications\OrderDelivered',
            'App\Notifications\PromotionAlert'
        ];

        for ($i = 0; $i < 25; $i++) {
            $type = $notificationTypes[array_rand($notificationTypes)];
            $notifDate = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));
            
            $data = match($type) {
                'App\Notifications\OrderPlaced' => ['title' => 'Đơn hàng mới', 'message' => 'Đơn hàng #MNM-'.rand(1000,9999).' đã được đặt thành công.', 'url' => '/account/orders'],
                'App\Notifications\OrderShipped' => ['title' => 'Đang giao hàng', 'message' => 'Đơn hàng của bạn đang trên đường giao tới.', 'url' => '/account/orders'],
                'App\Notifications\OrderDelivered' => ['title' => 'Giao thành công', 'message' => 'Đơn hàng đã được giao thành công.', 'url' => '/account/orders'],
                'App\Notifications\PromotionAlert' => ['title' => 'Khuyến mãi hot', 'message' => 'Giảm ngay 50k cho đơn hàng từ 200k. Nhập mã MNM50K.', 'url' => '/books'],
            };

            DatabaseNotification::create([
                'id' => Str::uuid(),
                'type' => $type,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $customer->id,
                'data' => $data,
                'read_at' => rand(0, 1) ? $notifDate->copy()->addHours(1) : null,
                'created_at' => $notifDate,
                'updated_at' => $notifDate,
            ]);
        }

        echo "Mock data seeded successfully!\n";
    }
}
