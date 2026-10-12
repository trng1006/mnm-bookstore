<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo danh sách độc giả mẫu
        $readersData = [
            ['name' => 'Trần Bảo Ngọc', 'email' => 'ngoc.tran@gmail.com'],
            ['name' => 'Lê Minh Hoàng', 'email' => 'hoang.le@gmail.com'],
            ['name' => 'Hoàng Thùy Linh', 'email' => 'linh.hoang@gmail.com'],
            ['name' => 'Đặng Quốc Tuấn', 'email' => 'tuan.dang@gmail.com'],
            ['name' => 'Phạm Mai Anh', 'email' => 'maianh.pham@gmail.com'],
            ['name' => 'Vũ Đình Trọng', 'email' => 'trong.vu@gmail.com'],
            ['name' => 'Nguyễn Hải Đăng', 'email' => 'dang.nguyen@gmail.com'],
            ['name' => 'Bùi Phương Thảo', 'email' => 'thao.bui@gmail.com'],
            ['name' => 'Đỗ Kim Ngân', 'email' => 'ngan.do@gmail.com'],
            ['name' => 'Huỳnh Thanh Trúc', 'email' => 'truc.huynh@gmail.com'],
        ];

        $readers = [];
        foreach ($readersData as $data) {
            $readers[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('123'),
                    'role' => 'customer',
                    'phone' => '09' . rand(10000000, 99999999),
                    'address' => 'TP. Hồ Chí Minh',
                ]
            );
        }

        // 2. Mẫu bình luận chân thực theo số sao
        $comments5Star = [
            'Cuốn sách tuyệt vời, nội dung sâu sắc và ý nghĩa. Đọc một lèo không dứt ra được!',
            'Bìa sách đẹp, đóng gói cẩn thận, giấy in trắng ngà đọc rất êm mắt. 10/10!',
            'Tác phẩm rất đáng đọc, mang lại nhiều góc nhìn mới mẻ và bài học giá trị.',
            'Giao hàng siêu nhanh, sách thơm mùi mới. Cực kỳ hài lòng về cả nội dung lẫn chất lượng dịch vụ.',
            'Một trong những cuốn sách hay nhất mình từng đọc trong năm nay. Khuyên mọi người nên mua!',
            'Cốt truyện lôi cuốn, văn phong trau chuốt, các tình tiết rất hợp lý và cảm động.',
            'Đọc xong thấy mở mang nhiều điều. Rất cảm ơn tác giả và nhà sách đã đem đến tác phẩm tuyệt vời.',
            'Sách dày dặn, kiến thức thực tế, đọc đến đâu thấm đến đó.',
            'Mua cho em gái đọc mà mình mê luôn, hình ảnh minh họa và ngôn từ đều rất đẹp.',
            'Tuyệt tác! Một cuốn sách xứng đáng có mặt trong tủ sách của mọi gia đình.'
        ];

        $comments4Star = [
            'Sách hay, cách trình bày mạch lạc dễ hiểu. Trừ 1 sao nhỏ vì thời gian giao hàng hơi lâu một chút.',
            'Nội dung bổ ích, nhiều kiến thức hay. Một số đoạn hơi dài dòng xíu nhưng tổng thể rất ổn.',
            'Sách đọc khá cuốn, bìa đẹp. Rất thích hợp để đọc thư giãn vào dịp cuối tuần.',
            'Dịch thuật mượt mà, câu từ chau chuốt. Đáng để có một cuốn trong tủ sách.',
            'Chất lượng giấy tốt, nội dung hấp dẫn. Sẽ tiếp tục mua thêm sách từ shop.',
            'Sách giao đúng hẹn, bìa đẹp không bị gãy góc. Nội dung sâu sắc cần thời gian nghiền ngẫm.'
        ];

        $comments3Star = [
            'Nội dung ở mức khá, chưa thực sự bứt phá như mình kỳ vọng nhưng vẫn đáng tham khảo.',
            'Sách giao tới bị cấn nhẹ ở mép bìa, nội dung thì tạm ổn đối với người mới bắt đầu.',
            'Cách hành văn ổn, tuy nhiên ý tưởng chưa quá mới mẻ. Đọc giải trí thì khá phù hợp.'
        ];

        $books = Book::all();
        $totalReviewsCreated = 0;

        foreach ($books as $book) {
            // Mỗi sách tạo ngẫu nhiên từ 3 đến 6 đánh giá
            $numReviews = rand(3, 6);
            $selectedReaders = collect($readers)->shuffle()->take($numReviews);

            foreach ($selectedReaders as $reader) {
                // Xác suất: 60% 5 sao, 30% 4 sao, 10% 3 sao
                $randPercent = rand(1, 100);
                if ($randPercent <= 65) {
                    $rating = 5;
                    $comment = $comments5Star[array_rand($comments5Star)];
                } elseif ($randPercent <= 92) {
                    $rating = 4;
                    $comment = $comments4Star[array_rand($comments4Star)];
                } else {
                    $rating = 3;
                    $comment = $comments3Star[array_rand($comments3Star)];
                }

                $createdAt = Carbon::now()->subDays(rand(1, 40))->subHours(rand(1, 23))->subMinutes(rand(1, 59));

                Review::updateOrCreate(
                    [
                        'user_id' => $reader->id,
                        'book_id' => $book->id,
                    ],
                    [
                        'rating' => $rating,
                        'comment' => $comment,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );
                $totalReviewsCreated++;
            }

            // Tính toán lại điểm đánh giá trung bình cộng và tổng số review
            $avg = $book->reviews()->avg('rating') ?? 0;
            $count = $book->reviews()->count();

            $book->update([
                'rating' => round($avg, 1),
                'reviews_count' => $count,
            ]);
        }

        $this->command->info("Đã tạo {$totalReviewsCreated} đánh giá mẫu và cập nhật điểm trung bình chuẩn cho {$books->count()} cuốn sách!");
    }
}
