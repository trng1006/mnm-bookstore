<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Users (Admin, Staff, Customer)
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Quản Trị',
                'password' => Hash::make('123'),
                'role' => 'admin',
                'phone' => '0988888888',
                'address' => 'Văn phòng MNM Bookstore, Tầng 5, 45 Lê Duẩn, Quận 1, TP.HCM',
            ]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@gmail.com'],
            [
                'name' => 'Nhân Viên Kho',
                'password' => Hash::make('123'),
                'role' => 'staff',
                'phone' => '0977777777',
                'address' => 'Kho hàng MNM Bookstore, Thủ Đức, TP.HCM',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Nguyễn Văn A',
                'password' => Hash::make('123'),
                'role' => 'customer',
                'phone' => '0901234567',
                'address' => '123 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
            ]
        );

        // 1. Categories
        $categories = [
            'Văn học', 'Tiểu thuyết', 'Tâm lý – Kỹ năng sống', 'Kinh tế',
            'Thiếu nhi', 'Trinh thám', 'Khoa học', 'Lịch sử',
        ];

        $categoryMap = [];
        foreach ($categories as $name) {
            $categoryMap[$name] = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        // 2. Authors
        $authors = [
            'Paulo Coelho', 'Haruki Murakami', 'José Mauro de Vasconcelos',
            'Thomas Harris', 'Carlos Ruiz Zafón', 'Nguyên Phong',
            'Fyodor Dostoevsky', 'Luis Sepúlveda', 'Daniel Kahneman',
        ];

        $authorMap = [];
        foreach ($authors as $name) {
            $authorMap[$name] = Author::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        // 3. Books
        $booksData = [
            [
                'title' => 'Nhà Giả Kim',
                'slug' => 'nha-gia-kim',
                'author_id' => $authorMap['Paulo Coelho']->id,
                'category_id' => $categoryMap['Văn học']->id,
                'price_current' => 79000,
                'price_old' => 99000,
                'stock' => 120,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780062315007-L.jpg',
                'description' => 'Nhà giả kim (tựa gốc tiếng Bồ Đào Nha: O Alquimista) là cuốn tiểu thuyết nổi tiếng nhất của nhà văn Paulo Coelho. Tất cả những trải nghiệm trong chuyến phiêu lưu theo đuổi vận mệnh của chàng chăn cừu Santiago đã giúp cậu thấu hiểu được ý nghĩa sâu xa nhất của hạnh phúc, hòa hợp với vũ trụ và con người.',
                'publisher' => 'NXB Hội Nhà Văn',
                'pages' => 228,
                'publication_year' => 2020,
                'discount_percent' => 20,
                'rating' => 4.8,
                'reviews_count' => 150,
                'is_new' => false,
                'is_best_seller' => true,
            ],
            [
                'title' => 'Rừng Na Uy',
                'slug' => 'rung-na-uy',
                'author_id' => $authorMap['Haruki Murakami']->id,
                'category_id' => $categoryMap['Tiểu thuyết']->id,
                'price_current' => 105000,
                'price_old' => 135000,
                'stock' => 85,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780375704024-L.jpg',
                'description' => 'Rừng Na Uy là một trong những tác phẩm kinh điển của Haruki Murakami. Lấy bối cảnh Tokyo những năm 1960, câu chuyện kể về những hoài niệm, tình yêu, sự mất mát và những trăn trở của tuổi trẻ giữa hai bóng hình thiếu nữ Naoko và Midori.',
                'publisher' => 'NXB Hội Nhà Văn',
                'pages' => 552,
                'publication_year' => 2019,
                'discount_percent' => 22,
                'rating' => 4.9,
                'reviews_count' => 320,
                'is_new' => false,
                'is_best_seller' => true,
            ],
            [
                'title' => 'Cây Cam Ngọt Của Tôi',
                'slug' => 'cay-cam-ngot-cua-toi',
                'author_id' => $authorMap['José Mauro de Vasconcelos']->id,
                'category_id' => $categoryMap['Văn học']->id,
                'price_current' => 85000,
                'price_old' => 108000,
                'stock' => 60,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780763690753-L.jpg',
                'description' => 'Cây cam ngọt của tôi mở ra những rung động tinh tế nhất của tuổi thơ qua góc nhìn của chú bé Zezé thông minh và nghịch ngợm. Một tác phẩm cảm động về tình người, sự thấu hiểu và nỗi đau trưởng thành khiến hàng triệu trái tim thổn thức.',
                'publisher' => 'NXB Hội Nhà Văn',
                'pages' => 244,
                'publication_year' => 2021,
                'discount_percent' => 21,
                'rating' => 5.0,
                'reviews_count' => 890,
                'is_new' => false,
                'is_best_seller' => true,
            ],
            [
                'title' => 'Sự Im Lặng Của Bầy Cừu',
                'slug' => 'su-im-lang-cua-bay-cuu',
                'author_id' => $authorMap['Thomas Harris']->id,
                'category_id' => $categoryMap['Trinh thám']->id,
                'price_current' => 110000,
                'price_old' => 140000,
                'stock' => 45,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780312924584-L.jpg',
                'description' => 'Một trong những kiệt tác trinh thám tâm lý kinh dị hay nhất mọi thời đại. Cuộc đấu trí căng thẳng giữa nữ đặc vụ FBI Clarice Starling và bác sĩ tâm thần thông thái bệnh hoạn Hannibal Lecter.',
                'publisher' => 'NXB Lao Động',
                'pages' => 380,
                'publication_year' => 2018,
                'discount_percent' => 21,
                'rating' => 4.7,
                'reviews_count' => 420,
                'is_new' => false,
                'is_best_seller' => true,
            ],
            [
                'title' => 'Bóng Hình Của Gió',
                'slug' => 'bong-hinh-cua-gio',
                'author_id' => $authorMap['Carlos Ruiz Zafón']->id,
                'category_id' => $categoryMap['Tiểu thuyết']->id,
                'price_current' => 120000,
                'price_old' => null,
                'stock' => 30,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780143034902-L.jpg',
                'description' => 'Câu chuyện kỳ bí diễn ra tại Barcelona sau Thế chiến thứ hai, nơi cậu bé Daniel được cha dẫn tới Nghĩa địa của những cuốn sách bị quên lãng. Một cuốn sách đưa cậu vào vòng xoáy của đam mê, bí mật đen tối và định mệnh.',
                'publisher' => 'NXB Văn Học',
                'pages' => 540,
                'publication_year' => 2022,
                'discount_percent' => null,
                'rating' => 4.6,
                'reviews_count' => 85,
                'is_new' => true,
                'is_best_seller' => false,
            ],
            [
                'title' => 'Muôn Kiếp Nhân Sinh',
                'slug' => 'muon-kiep-nhan-sinh',
                'author_id' => $authorMap['Nguyên Phong']->id,
                'category_id' => $categoryMap['Tâm lý – Kỹ năng sống']->id,
                'price_current' => 168000,
                'price_old' => 210000,
                'stock' => 90,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780735211292-L.jpg',
                'description' => 'Muôn kiếp nhân sinh là bức tranh toàn cảnh về luật Nhân Quả và Luân Hồi, thông qua câu chuyện có thật của một doanh nhân tài chính tại New York với ký ức về nhiều tiền kiếp trải dài từ nền văn minh Atlantis đến Ai Cập cổ đại.',
                'publisher' => 'NXB Tổng Hợp TP.HCM',
                'pages' => 420,
                'publication_year' => 2020,
                'discount_percent' => 20,
                'rating' => 4.8,
                'reviews_count' => 500,
                'is_new' => true,
                'is_best_seller' => false,
            ],
            [
                'title' => 'Tội Ác Và Hình Phạt',
                'slug' => 'toi-ac-va-hinh-phat',
                'author_id' => $authorMap['Fyodor Dostoevsky']->id,
                'category_id' => $categoryMap['Văn học']->id,
                'price_current' => 145000,
                'price_old' => null,
                'stock' => 25,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780140449136-L.jpg',
                'description' => 'Kiệt tác vĩ đại của đại văn hào Nga Dostoevsky, khắc họa chiều sâu tâm lý giằng xé của chàng sinh viên Raskolnikov sau khi phạm tội sát nhân, hành trình chuộc tội và sự cứu rỗi của tình yêu thương.',
                'publisher' => 'NXB Văn Học',
                'pages' => 680,
                'publication_year' => 2021,
                'discount_percent' => null,
                'rating' => 4.9,
                'reviews_count' => 300,
                'is_new' => true,
                'is_best_seller' => false,
            ],
            [
                'title' => 'Chuyện Con Mèo Dạy Hải Âu Bay',
                'slug' => 'chuyen-con-meo-day-hai-au-bay',
                'author_id' => $authorMap['Luis Sepúlveda']->id,
                'category_id' => $categoryMap['Thiếu nhi']->id,
                'price_current' => 49000,
                'price_old' => 60000,
                'stock' => 150,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780143119395-L.jpg',
                'description' => 'Câu chuyện cảm động về chú mèo mập Zorba tại bến cảng Hamburg và ba lời hứa danh dự: không ăn quả trứng hải âu, chăm sóc chú chim non và dạy nó bay. Cuốn sách gieo vào lòng người đọc bài học về lòng nhân ái và tình yêu thương dị biệt.',
                'publisher' => 'NXB Hội Nhà Văn',
                'pages' => 140,
                'publication_year' => 2022,
                'discount_percent' => 18,
                'rating' => 4.9,
                'reviews_count' => 1500,
                'is_new' => true,
                'is_best_seller' => false,
            ],
        ];

        foreach ($booksData as $bookData) {
            Book::updateOrCreate(['slug' => $bookData['slug']], $bookData);
        }

        // 4. Seed Coupons
        $coupons = [
            [
                'code' => 'GIAM10',
                'type' => 'percent',
                'value' => 10,
                'min_order_value' => 100000,
                'is_active' => true,
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'fixed',
                'value' => 30000,
                'min_order_value' => 150000,
                'is_active' => true,
            ],
            [
                'code' => 'MNM50K',
                'type' => 'fixed',
                'value' => 50000,
                'min_order_value' => 250000,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }

        // 5. Seed Reviews
        $nhaGiaKim = Book::where('slug', 'nha-gia-kim')->first();
        if ($nhaGiaKim) {
            Review::firstOrCreate(
                ['user_id' => $customer->id, 'book_id' => $nhaGiaKim->id],
                [
                    'rating' => 5,
                    'comment' => 'Cuốn sách tuyệt vời đã làm thay đổi cách nhìn của tôi về ước mơ và vận mệnh cá nhân. Rất đáng đọc nhiều lần!',
                ]
            );
        }

        $cayCamNgot = Book::where('slug', 'cay-cam-ngot-cua-toi')->first();
        if ($cayCamNgot) {
            Review::firstOrCreate(
                ['user_id' => $customer->id, 'book_id' => $cayCamNgot->id],
                [
                    'rating' => 5,
                    'comment' => 'Đọc mà rơi nước mắt vì tình cảm của chú bé Zezé và ông Bồ Đào Nha. Một câu chuyện quá đỗi xúc động.',
                ]
            );
        }

        // 6. Seed a sample completed order for the demo customer
        $sampleOrder = Order::firstOrCreate(
            ['order_number' => 'MNM-20260909-DEMO1'],
            [
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'shipping_address' => $customer->address,
                'shipping_notes' => 'Giao hàng giờ hành chính',
                'payment_method' => 'cod',
                'payment_status' => 'paid',
                'status' => Order::STATUS_COMPLETED,
                'subtotal' => 164000,
                'shipping_fee' => 0,
                'discount_amount' => 16400,
                'total_amount' => 147600,
                'coupon_code' => 'GIAM10',
            ]
        );

        if ($nhaGiaKim && $cayCamNgot) {
            OrderItem::firstOrCreate(
                ['order_id' => $sampleOrder->id, 'book_id' => $nhaGiaKim->id],
                [
                    'book_title' => $nhaGiaKim->title,
                    'book_cover' => $nhaGiaKim->cover_image,
                    'price' => $nhaGiaKim->price_current,
                    'quantity' => 1,
                    'total' => $nhaGiaKim->price_current,
                ]
            );

            OrderItem::firstOrCreate(
                ['order_id' => $sampleOrder->id, 'book_id' => $cayCamNgot->id],
                [
                    'book_title' => $cayCamNgot->title,
                    'book_cover' => $cayCamNgot->cover_image,
                    'price' => $cayCamNgot->price_current,
                    'quantity' => 1,
                    'total' => $cayCamNgot->price_current,
                ]
            );
        }

        // 7. Import raw Fahasa data
        $this->call(FahasaBookSeeder::class);
    }
}
