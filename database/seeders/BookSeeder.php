<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'title' => 'Nhà Giả Kim (Tái bản 2024)',
                'slug' => 'nha-gia-kim-2024',
                'author' => 'Paulo Coelho',
                'category' => 'Tiểu thuyết',
                'price_current' => 69000,
                'price_old' => null,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780062315007-L.jpg',
                'discount_percent' => null,
                'is_new' => true,
            ],
            [
                'title' => 'Tư Duy Nhanh Và Chậm',
                'slug' => 'tu-duy-nhanh-va-cham',
                'author' => 'Daniel Kahneman',
                'category' => 'Tâm lý học',
                'price_current' => 159000,
                'price_old' => 199000,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780374533557-L.jpg',
                'discount_percent' => 20,
                'is_new' => false,
            ],
            [
                'title' => 'Thói Quen Nguyên Tử',
                'slug' => 'thoi-quen-nguyen-tu',
                'author' => 'James Clear',
                'category' => 'Kỹ năng sống',
                'price_current' => 135000,
                'price_old' => 170000,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780735211292-L.jpg',
                'discount_percent' => 21,
                'is_new' => true,
            ],
            [
                'title' => 'Tâm Lý Học Tội Phạm',
                'slug' => 'tam-ly-hoc-toi-pham',
                'author' => 'Stanton E. Samenow',
                'category' => 'Tâm lý học',
                'price_current' => 145000,
                'price_old' => 189000,
                'cover_image' => 'https://covers.openlibrary.org/b/isbn/9780804139908-L.jpg',
                'discount_percent' => 23,
                'is_new' => false,
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}
