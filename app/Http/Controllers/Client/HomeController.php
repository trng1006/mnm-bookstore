<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Shows the home page with comprehensive bookstore sections.
     */
    public function showHomePage(Request $request): View
    {
        $categories = Category::pluck('name')->toArray();

        $bestSellers = Book::with('author', 'category')
            ->where('is_best_seller', true)
            ->get()
            ->map(function ($book) {
                return (object) [
                    'id' => $book->id,
                    'slug' => $book->slug,
                    'title' => $book->title,
                    'author' => $book->author->name ?? 'Unknown',
                    'price' => $book->price_current,
                    'originalPrice' => $book->price_old,
                    'cover' => $book->cover_image,
                    'rating' => $book->rating,
                    'isNew' => $book->is_new,
                    'hasDiscount' => $book->discount_percent > 0,
                    'tagText' => $book->discount_percent ? '-'.$book->discount_percent.'%' : '',
                    'tagColor' => $book->discount_percent ? 'bg-red-700 text-white' : '',
                ];
            });

        $sort = $request->get('sort', 'newest');
        $newBooksQuery = Book::with('author', 'category')->where('is_new', true);

        switch ($sort) {
            case 'price_asc':
                $newBooksQuery->orderBy('price_current', 'asc');
                break;
            case 'price_desc':
                $newBooksQuery->orderBy('price_current', 'desc');
                break;
            case 'name_asc':
                $newBooksQuery->orderBy('title', 'asc');
                break;
            case 'name_desc':
                $newBooksQuery->orderBy('title', 'desc');
                break;
            case 'popular':
                $newBooksQuery->orderBy('reviews_count', 'desc');
                break;
            case 'newest':
            default:
                $newBooksQuery->orderBy('created_at', 'desc');
                break;
        }

        $newBooks = $newBooksQuery->paginate(12)
            ->withQueryString()
            ->through(function ($book) {
                return (object) [
                    'id' => $book->id,
                    'slug' => $book->slug,
                    'title' => $book->title,
                    'author' => $book->author->name ?? 'Unknown',
                    'price' => $book->price_current,
                    'originalPrice' => $book->price_old,
                    'cover' => $book->cover_image,
                    'rating' => $book->rating,
                    'isNew' => $book->is_new,
                    'hasDiscount' => false,
                    'tagText' => 'MỚI',
                    'tagColor' => 'bg-[#6B8A44] text-white',
                ];
            });

        $features = [
            ['title' => 'Tuyển chọn kỹ lưỡng', 'desc' => 'Những tựa sách chất lượng nhất.', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['title' => 'Giá cả hợp lý', 'desc' => 'Ưu đãi thường xuyên.', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['title' => 'Đóng gói cẩn thận', 'desc' => 'Sách luôn mới và đẹp.', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['title' => 'Giao hàng toàn quốc', 'desc' => 'Nhanh chóng & tiện lợi.', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ];

        if ($request->ajax()) {
            return view('client.pages.partials.new_books_grid', compact('newBooks', 'sort'));
        }

        return view('client.pages.home', compact('categories', 'bestSellers', 'newBooks', 'features', 'sort'));
    }
}
