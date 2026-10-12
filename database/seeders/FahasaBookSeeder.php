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
            $jsonPath = '/Volumes/Hybridd06/Mac_Backup/Documents/HUIT_Nam4/MNM/Cào dữ liệu /fahasa_books.json';
        }

        if (! file_exists($jsonPath)) {
            $this->command->warn('JSON file not found: ' . $jsonPath);
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $faker = Faker::create('vi_VN');

        $count = 0;
        foreach ($data as $item) {
            if (! isset($item['title'])) {
                continue;
            }

            // Map Category
            $categoryName = $item['category_name'] ?? 'Sách Hay';
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );

            // Map Author
            $authorName = !empty($item['author']) ? $item['author'] : 'Nhiều tác giả';
            $author = Author::firstOrCreate(
                ['slug' => Str::slug($authorName)],
                ['name' => $authorName]
            );

            $slug = Str::slug($item['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (Book::where('slug', $slug)->where('title', '!=', $item['title'])->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }

            $price_current = $item['price'] ?? 0;
            $price_old = $item['original_price'] ?? null;
            $discount_percent = null;
            if ($price_old && $price_old > $price_current && $price_current > 0) {
                $discount_percent = round((($price_old - $price_current) / $price_old) * 100);
            }

            $description = !empty($item['description']) 
                ? strip_tags($item['description']) 
                : implode("\n\n", $faker->paragraphs(3));

            Book::updateOrCreate(
                ['title' => $item['title']],
                [
                    'slug' => $slug,
                    'author_id' => $author->id,
                    'category_id' => $category->id,
                    'price_current' => $price_current,
                    'price_old' => $price_old,
                    'discount_percent' => $discount_percent,
                    'stock' => $item['available_stock'] ?? $faker->numberBetween(10, 100),
                    'cover_image' => $item['image_url'] ?? null,
                    'description' => $description,
                    'publisher' => $item['publisher'] ?? 'NXB Trẻ',
                    'pages' => $faker->numberBetween(120, 500),
                    'publication_year' => $faker->numberBetween(2020, 2024),
                    'rating' => $faker->randomFloat(1, 4.0, 5.0),
                    'reviews_count' => $faker->numberBetween(5, 200),
                    'is_new' => $faker->boolean(25),
                    'is_best_seller' => $faker->boolean(20),
                ]
            );
            $count++;
        }
        $this->command->info("Imported {$count} books from JSON and enriched with fake data!");
    }
}
