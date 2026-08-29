<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Support\Str;

$jsonPath = __DIR__.'/database/data/fahasa_books.json';
if (!file_exists($jsonPath)) {
    $jsonPath = '/Users/tranbaonguyen/Documents/MNM/Cào dữ liệu /fahasa_books.json';
}

$data = json_decode(file_get_contents($jsonPath), true);

$defaultAuthor = Author::firstOrCreate(['name' => 'Chưa rõ tác giả', 'slug' => 'chua-ro-tac-gia']);
$defaultCategory = Category::firstOrCreate(['name' => 'Sách Fahasa', 'slug' => 'sach-fahasa']);

$count = 0;
foreach ($data as $item) {
    if (!isset($item['title'])) continue;
    
    $slug = Str::slug($item['title']);
    // Ensure unique slug
    $originalSlug = $slug;
    $counter = 1;
    while (Book::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter;
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
echo "Imported {$count} books from JSON!\n";
