<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with(['category', 'author'])->paginate(10);
        return view('admin.books.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::all();
        $authors = Author::all();
        return view('admin.books.create', compact('categories', 'authors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:books',
            'category_id' => 'required|exists:categories,id',
            'author_id' => 'required|exists:authors,id',
            'price_current' => 'required|integer|min:0',
            'price_old' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|string|max:255',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'is_new' => 'boolean',
            'is_best_seller' => 'boolean',
        ]);

        Book::create($validated);
        return redirect()->route('admin.books.index')->with('success', 'Sách đã được thêm.');
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        $authors = Author::all();
        return view('admin.books.edit', compact('book', 'categories', 'authors'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:books,slug,' . $book->id,
            'category_id' => 'required|exists:categories,id',
            'author_id' => 'required|exists:authors,id',
            'price_current' => 'required|integer|min:0',
            'price_old' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|string|max:255',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'is_new' => 'boolean',
            'is_best_seller' => 'boolean',
        ]);

        $book->update($validated);
        return redirect()->route('admin.books.index')->with('success', 'Sách đã được cập nhật.');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Sách đã bị xóa.');
    }
}
