<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categories = [
            'Văn học', 'Tiểu thuyết', 'Tâm lý – Kỹ năng sống', 'Kinh tế', 
            'Thiếu nhi', 'Trinh thám', 'Khoa học', 'Lịch sử'
        ];
        
        $categoryMap = [];
        foreach ($categories as $name) {
            $categoryMap[$name] = \App\Models\Category::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name)
            ]);
        }

        // 2. Authors
        $authors = [
            'Paulo Coelho', 'Haruki Murakami', 'José Mauro de Vasconcelos', 
            'Thomas Harris', 'Carlos Ruiz Zafón', 'Nguyên Phong', 
            'Fyodor Dostoevsky', 'Luis Sepúlveda', 'Daniel Kahneman'
        ];
        
        $authorMap = [];
        foreach ($authors as $name) {
            $authorMap[$name] = \App\Models\Author::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name)
            ]);
        }

        // 3. Books
        \App\Models\Book::insert([
            [
                'title' => 'Nhà Giả Kim', 
                'slug' => 'nha-gia-kim', 
                'author_id' => $authorMap['Paulo Coelho']->id,
                'category_id' => $categoryMap['Văn học']->id,
                'price_current' => 79000, 
                'price_old' => 99000,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780062315007-L.jpg',
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
                'price_old' => null,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780375704024-L.jpg',
                'discount_percent' => null,
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
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780763690753-L.jpg',
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
                'price_old' => null,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780312924584-L.jpg',
                'discount_percent' => null,
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
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780143034902-L.jpg',
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
                'price_old' => null,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780735211292-L.jpg',
                'discount_percent' => null,
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
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780140449136-L.jpg',
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
                'price_old' => null,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780143119395-L.jpg',
                'discount_percent' => null,
                'rating' => 4.9,
                'reviews_count' => 1500,
                'is_new' => true,
                'is_best_seller' => false,
            ],
        ]);
    }
}
