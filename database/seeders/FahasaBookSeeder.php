<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class FahasaBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/fahasa_books.json');
        if (! file_exists($jsonPath)) {
            $jsonPath = '/Users/tranbaonguyen/Documents/MNM/Cào dữ liệu /fahasa_books.json';
        }

        if (! file_exists($jsonPath)) {
            $this->command->warn('JSON file not found: ' . $jsonPath);
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $faker = Faker::create('vi_VN');

        // Get existing categories and authors to assign randomly
        $categoryIds = Category::pluck('id')->toArray();
        $authorIds = Author::pluck('id')->toArray();
        
        // Publishers list to pick randomly
        $publishers = ['NXB Trẻ', 'NXB Kim Đồng', 'NXB Tổng Hợp TP.HCM', 'NXB Nhã Nam', 'NXB Phụ Nữ', 'NXB Văn Học', 'NXB Thanh Niên'];

        $count = 0;
        foreach ($data as $item) {
            if (! isset($item['title'])) {
                continue;
            }

            $slug = Str::slug($item['title']);
            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (Book::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }

            // Create a fake discount 30% of the time
            $hasDiscount = $faker->boolean(30);
            $price_current = $item['price'] ?? 0;
            $price_old = null;
            $discount_percent = null;
            
            if ($hasDiscount && $price_current > 0) {
                // Discount from 5% to 30%
                $discount_percent = $faker->numberBetween(5, 30);
                $price_old = intval($price_current / (1 - ($discount_percent / 100)));
                // Round up to nearest 1000
                $price_old = ceil($price_old / 1000) * 1000;
            }

            Book::create([
                'title' => $item['title'],
                'slug' => $slug,
                'author_id' => $faker->randomElement($authorIds),
                'category_id' => $faker->randomElement($categoryIds),
                'price_current' => $price_current,
                'price_old' => $price_old,
                'discount_percent' => $discount_percent,
                'stock' => $faker->numberBetween(10, 200),
                'cover_image' => $item['image_url'] ?? null,
                'description' => '<p>' . implode('</p><p>', $faker->paragraphs($faker->numberBetween(2, 4))) . '</p>',
                'publisher' => $faker->randomElement($publishers),
                'pages' => $faker->numberBetween(100, 800),
                'publication_year' => $faker->numberBetween(2010, 2024),
                'rating' => $faker->randomFloat(1, 3.5, 5.0),
                'reviews_count' => $faker->numberBetween(0, 500),
                'is_new' => $faker->boolean(20),
                'is_best_seller' => $faker->boolean(15),
            ]);
            $count++;
        }
        $this->command->info("Imported {$count} books from JSON and enriched with fake data!");
    }
}
