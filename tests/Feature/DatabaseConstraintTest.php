<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_email_must_be_unique()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/UNIQUE constraint failed|Duplicate entry/i');
        
        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_book_slug_must_be_unique()
    {
        $author = clone Author::create(['name' => 'Author 1', 'slug' => 'author-1']);
        $category = clone Category::create(['name' => 'Category 1', 'slug' => 'cat-1']);

        Book::create([
            'title' => 'Book 1',
            'slug' => 'book-1',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'price_current' => 100000,
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/UNIQUE constraint failed|Duplicate entry/i');

        Book::create([
            'title' => 'Book 2',
            'slug' => 'book-1',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'price_current' => 120000,
        ]);
    }

    public function test_foreign_key_constraint_book_author()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/foreign key constraint|FOREIGN KEY/i');

        Book::create([
            'title' => 'Book 1',
            'slug' => 'book-1',
            'author_id' => 9999, // Non-existent author
            'price_current' => 100000,
        ]);
    }
    
    public function test_book_stock_cannot_be_negative()
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite does not enforce unsigned checks automatically.');
        }

        $author = clone Author::create(['name' => 'Author 1', 'slug' => 'author-1']);
        
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/CHECK constraint|Out of range|constraint failed/i');

        Book::create([
            'title' => 'Book 1',
            'slug' => 'book-1',
            'author_id' => $author->id,
            'price_current' => 100000,
            'stock' => -10,
        ]);
    }

    public function test_book_price_cannot_be_negative()
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite does not enforce unsigned checks automatically.');
        }

        $author = clone Author::create(['name' => 'Author 1', 'slug' => 'author-1']);
        
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/CHECK constraint|Out of range|constraint failed/i');

        Book::create([
            'title' => 'Book 1',
            'slug' => 'book-1',
            'author_id' => $author->id,
            'price_current' => -10000,
        ]);
    }
}
