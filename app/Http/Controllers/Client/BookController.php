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
        $searchTerm = $request->get('search') ?? $request->get('q');
        if (!empty($searchTerm)) {
            $searchTerm = trim($searchTerm);
            $searchSlug = \Illuminate\Support\Str::slug($searchTerm);
            
            $query->where(function ($q) use ($searchTerm, $searchSlug) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('slug', 'like', '%' . $searchSlug . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('author', function ($q2) use ($searchTerm, $searchSlug) {
                      $q2->where('name', 'like', '%' . $searchTerm . '%')
                         ->orWhere('slug', 'like', '%' . $searchSlug . '%');
                  })
                  ->orWhereHas('category', function ($q3) use ($searchTerm, $searchSlug) {
                      $q3->where('name', 'like', '%' . $searchTerm . '%')
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
        $categories = \App\Models\Category::withCount('books')->orderBy('name')->get();
        $selectedCategory = $request->get('category');

        return view('client.pages.books.index', compact('books', 'sort', 'categories', 'selectedCategory'));
    }

    /**
     * Hiển thị trang chi tiết sách
     */
    public function showBookDetails(string $slug)
    {
        /** @var Book $book */
        $book = Book::with(['author', 'category', 'visibleReviews.user', 'visibleReviews.repliedBy'])
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

        // Thống kê phân bổ sao chỉ tính trên các bình luận đang hiển thị
        $ratingCounts = [
            5 => $book->visibleReviews->where('rating', 5)->count(),
            4 => $book->visibleReviews->where('rating', 4)->count(),
            3 => $book->visibleReviews->where('rating', 3)->count(),
            2 => $book->visibleReviews->where('rating', 2)->count(),
            1 => $book->visibleReviews->where('rating', 1)->count(),
        ];

        // Kiểm tra quyền đánh giá của user hiện tại (kể cả trường hợp đánh giá bị Admin ẩn)
        $userCanReview = auth()->check() ? auth()->user()->hasPurchasedBook($book->id) : false;
        $userExistingReview = auth()->check()
            ? Review::where('book_id', $book->id)->where('user_id', auth()->id())->first()
            : null;
        $isReviewHiddenByAdmin = $userExistingReview && ! $userExistingReview->is_visible;

        return view('client.pages.books.show', compact(
            'book',
            'relatedBooks',
            'ratingCounts',
            'userCanReview',
            'userExistingReview',
            'isReviewHiddenByAdmin'
        ));
    }

    /**
     * Lưu đánh giá / nhận xét của người dùng (Chỉ khi đã mua sách trong đơn hoàn tất)
     */
    public function storeReview(Request $request, string $slug)
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để gửi đánh giá.');
        }

        /** @var Book $book */
        $book = Book::where('slug', $slug)->firstOrFail();

        // Kiểm tra điều kiện: Mua xong (đơn hàng hoàn thành) mới được đánh giá
        if (! auth()->user()->hasPurchasedBook($book->id)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và đơn hàng được giao thành công.',
                ], 403);
            }

            return back()->with('error', 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và đơn hàng được giao thành công.');
        }

        // Khóa chỉnh sửa nếu đánh giá trước đó đã bị quản trị viên ẩn
        $existingReview = Review::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->first();

        if ($existingReview && ! $existingReview->is_visible) {
            $hiddenMessage = 'Đánh giá của bạn cho sách này đang bị quản trị viên ẩn và không thể chỉnh sửa.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $hiddenMessage,
                ], 403);
            }

            return back()->with('error', $hiddenMessage);
        }

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
                'is_visible' => true,
            ]
        );

        $book->updateRatingStats();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn! Đánh giá của bạn đã được ghi nhận.',
            ]);
        }

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
