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
        $categories = Category::all();

        $heroBanners = [
            [
                'image' => asset('images/banners/banner-1.webp'),
                'title' => 'Bách Khoa Tri Thức',
                'subtitle' => 'Mở rộng thế giới qua từng trang sách khoa học và tư duy',
                'link' => route('books.index', ['category' => 'khoa-hoc']),
            ],
            [
                'image' => asset('images/banners/banner-2.webp'),
                'title' => 'Saigon Books - Phát Triển Bản Thân',
                'subtitle' => 'Bộ sách kỹ năng sống và tư duy hiện đại dành cho giới trẻ',
                'link' => route('books.index', ['category' => 'tam-ly-ky-nang-song']),
            ],
            [
                'image' => asset('images/banners/banner-3.webp'),
                'title' => 'Omega Plus - Tinh Hoa Tri Thức',
                'subtitle' => 'Dòng sách lịch sử, văn hóa và tư tưởng kinh điển thế giới',
                'link' => route('books.index', ['category' => 'lich-su']),
            ],
            [
                'image' => asset('images/banners/banner-4.webp'),
                'title' => 'MCBooks - Tự Tin Hội Nhập',
                'subtitle' => 'Học ngoại ngữ và phương pháp đọc sách hiệu quả mỗi ngày',
                'link' => route('books.index', ['category' => 'sach-giao-khoa-tham-khao']),
            ],
            [
                'image' => asset('images/banners/banner-5.webp'),
                'title' => 'Đại Tiệc Sách MNM Bookstore',
                'subtitle' => 'Ưu đãi ngập tràn lên đến 30% cho hàng trăm tựa sách tuyển chọn',
                'link' => route('books.index'),
            ],
        ];

        $bestSellersByCategory = Category::with(['books' => function ($q) {
            $q->with('author')
                ->orderByDesc('is_best_seller')
                ->orderByDesc('reviews_count')
                ->orderByDesc('rating')
                ->limit(5);
        }])->get()->filter(function ($cat) {
            return $cat->books->isNotEmpty();
        })->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'books' => $cat->books->map(function ($book, $index) {
                    $soldInWeek = max(25, (int) ($book->reviews_count * 18 + ($book->rating * 10) - ($index * 12)));
                    return [
                        'id' => $book->id,
                        'rank' => $index + 1,
                        'title' => $book->title,
                        'slug' => $book->slug,
                        'author' => $book->author->name ?? 'Nhiều tác giả',
                        'publisher' => $book->publisher ?? 'NXB Trẻ',
                        'price' => (float) $book->price_current,
                        'price_formatted' => number_format($book->price_current, 0, ',', '.') . ' ₫',
                        'original_price' => (float) $book->price_old,
                        'original_price_formatted' => $book->price_old ? number_format($book->price_old, 0, ',', '.') . ' ₫' : null,
                        'discount_percent' => (int) $book->discount_percent,
                        'cover' => $book->cover_image,
                        'rating' => (float) $book->rating,
                        'reviews_count' => (int) $book->reviews_count,
                        'sold_in_week' => $soldInWeek,
                        'description' => strip_tags($book->description ?? ''),
                        'short_description' => \Illuminate\Support\Str::limit(strip_tags($book->description ?? ''), 180),
                        'stock' => (int) $book->stock,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

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
                    'tagColor' => 'bg-[#4A6B22] text-white',
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

        return view('client.pages.home', compact(
            'categories',
            'heroBanners',
            'bestSellersByCategory',
            'bestSellers',
            'newBooks',
            'features',
            'sort'
        ));
    }

    /**
     * Hiển thị trang Trung tâm hỗ trợ (Vận chuyển, Đổi trả, Thanh toán, FAQ).
     */
    public function showSupportPage(): View
    {
        return view('client.pages.support');
    }

    /**
     * Hiển thị trang Chính sách bảo mật & Điều khoản dịch vụ.
     */
    public function showPolicyPage(): View
    {
        return view('client.pages.policy');
    }
}
