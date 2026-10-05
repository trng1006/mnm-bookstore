<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
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
            'slug' => 'required|string|max:255|unique:books,slug,'.$book->id,
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

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048'
        ]);

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $handle = fopen($file->getPathname(), "r");
            $header = fgetcsv($handle, 1000, ","); // Bỏ qua dòng tiêu đề
            
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Cấu trúc dự kiến: title, slug, category_id, author_id, price_current, stock
                if (count($data) >= 6) {
                    Book::updateOrCreate(
                        ['slug' => $data[1]],
                        [
                            'title' => $data[0],
                            'category_id' => $data[2],
                            'author_id' => $data[3],
                            'price_current' => (int)$data[4],
                            'stock' => (int)$data[5],
                            'is_new' => true
                        ]
                    );
                    $count++;
                }
            }
            fclose($handle);
            
            return redirect()->route('admin.books.index')->with('success', "Nhập thành công $count cuốn sách từ file CSV.");
        }
        
        return redirect()->route('admin.books.index')->with('error', 'Không tìm thấy file hợp lệ.');
    }
}
