<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        $defaultAuthor = Author::firstOrCreate(['name' => 'Chưa rõ tác giả', 'slug' => 'chua-ro-tac-gia']);
        $defaultCategory = Category::firstOrCreate(['name' => 'Sách Fahasa', 'slug' => 'sach-fahasa']);

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

            Book::create([
                'title' => $item['title'],
                'slug' => $slug,
                'author_id' => $defaultAuthor->id,
                'category_id' => $defaultCategory->id,
                'price_current' => $item['price'] ?? 0,
                'cover_image' => $item['image_url'] ?? null,
                'is_new' => true,
            ]);
            $count++;
        }
        $this->command->info("Imported {$count} books from JSON!");
    }
}
