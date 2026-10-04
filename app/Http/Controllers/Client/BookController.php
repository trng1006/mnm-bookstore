<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'category']);

        // Lọc theo danh mục
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->get('category'));
            });
        }

        // Lọc theo từ khóa tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $searchSlug = \Illuminate\Support\Str::slug($searchTerm);
            
            $query->where(function ($q) use ($searchTerm, $searchSlug) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('slug', 'like', '%' . $searchSlug . '%')
                  ->orWhereHas('author', function ($q2) use ($searchTerm, $searchSlug) {
                      $q2->where('name', 'like', '%' . $searchTerm . '%')
                         ->orWhere('slug', 'like', '%' . $searchSlug . '%');
                  });
            });
        }

        // Sắp xếp
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price_current', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_current', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'popular':
                $query->orderBy('reviews_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Phân trang 12 quyển
        $books = $query->paginate(12)->withQueryString();

        return view('client.pages.books.index', compact('books', 'sort'));
    }

    /**
     * Hiển thị trang chi tiết sách
     */
    public function showBookDetails(string $slug)
    {
        /** @var Book $book */
        $book = Book::with(['author', 'category', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // 4 sách liên quan (ưu tiên cùng danh mục, loại trừ sách hiện tại)
        $relatedBooks = Book::with(['author', 'category'])
            ->where('id', '!=', $book->id)
            ->where(function ($query) use ($book) {
                if ($book->category_id) {
                    $query->where('category_id', $book->category_id);
                } else {
                    $query->where('author_id', $book->author_id);
                }
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Nếu ít hơn 4 cuốn, lấy thêm các sách nổi bật khác
        if ($relatedBooks->count() < 4) {
            $extraBooks = Book::with(['author', 'category'])
                ->where('id', '!=', $book->id)
                ->whereNotIn('id', $relatedBooks->pluck('id'))
                ->take(4 - $relatedBooks->count())
                ->get();
            $relatedBooks = $relatedBooks->concat($extraBooks);
        }

        // Thống kê phân bổ sao
        $ratingCounts = [
            5 => $book->reviews->where('rating', 5)->count(),
            4 => $book->reviews->where('rating', 4)->count(),
            3 => $book->reviews->where('rating', 3)->count(),
            2 => $book->reviews->where('rating', 2)->count(),
            1 => $book->reviews->where('rating', 1)->count(),
        ];

        return view('client.pages.books.show', compact('book', 'relatedBooks', 'ratingCounts'));
    }

    /**
     * Lưu đánh giá / nhận xét của người dùng
     */
    public function storeReview(Request $request, string $slug)
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để gửi đánh giá.');
        }

        /** @var Book $book */
        $book = Book::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1000',
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'comment.required' => 'Vui lòng nhập nội dung nhận xét.',
            'comment.min' => 'Nội dung nhận xét tối thiểu 5 ký tự.',
        ]);

        Review::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'book_id' => $book->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]
        );

        $book->updateRatingStats();

        return back()->with('success', 'Cảm ơn bạn! Đánh giá của bạn đã được ghi nhận.');
    }

    /**
     * Tìm kiếm AJAX API
     */
    public function ajaxSearch(Request $request)
    {
        $keyword = $request->get('q');
        
        if (!$keyword || strlen($keyword) < 2) {
            return response()->json(['books' => []]);
        }

        $searchSlug = \Illuminate\Support\Str::slug($keyword);
        
        $books = Book::with('author')
            ->where(function($q) use ($keyword, $searchSlug) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('slug', 'like', '%' . $searchSlug . '%')
                  ->orWhereHas('author', function ($q2) use ($keyword, $searchSlug) {
                      $q2->where('name', 'like', '%' . $keyword . '%')
                         ->orWhere('slug', 'like', '%' . $searchSlug . '%');
                  });
            })
            ->select('id', 'title', 'slug', 'cover_image', 'price_current', 'author_id')
            ->take(5)
            ->get();

        $formattedBooks = $books->map(function($book) {
            return [
                'title' => $book->title,
                'slug' => $book->slug,
                'url' => route('books.show', $book->slug),
                'cover_image' => $book->cover_image ?? '',
                'price' => number_format($book->price_current, 0, ',', '.') . ' ₫',
                'author' => $book->author ? $book->author->name : 'Chưa rõ'
            ];
        });

        return response()->json(['books' => $formattedBooks]);
    }
}
