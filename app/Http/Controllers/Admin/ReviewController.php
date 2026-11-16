<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Hiển thị danh sách đánh giá & bình luận sản phẩm với bộ lọc trạng thái, số sao và từ khóa.
     */
    public function index(Request $request): View
    {
        $query = Review::with(['book', 'user', 'repliedBy'])->latest();

        $status = $request->get('status', 'all');
        if ($status === 'visible') {
            $query->where('is_visible', true);
        } elseif ($status === 'hidden') {
            $query->where('is_visible', false);
        } elseif ($status === 'unreplied') {
            $query->where(function ($q) {
                $q->whereNull('admin_reply')->orWhere('admin_reply', '');
            });
        }

        if ($request->filled('rating') && in_array((int) $request->rating, [1, 2, 3, 4, 5], true)) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhere('admin_reply', 'like', "%{$search}%")
                    ->orWhereHas('book', function ($qBook) use ($search) {
                        $qBook->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($qUser) use ($search) {
                        $qUser->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $reviews = $query->paginate(12)->withQueryString();

        $statusCounts = [
            'all' => Review::count(),
            'visible' => Review::where('is_visible', true)->count(),
            'hidden' => Review::where('is_visible', false)->count(),
            'unreplied' => Review::where(function ($q) {
                $q->whereNull('admin_reply')->orWhere('admin_reply', '');
            })->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'statusCounts'));
    }

    /**
     * Bật/tắt trạng thái hiển thị của bình luận và tính lại điểm sao cho sách.
     */
    public function toggleVisibility(Review $review): RedirectResponse
    {
        $review->is_visible = ! $review->is_visible;
        $review->save();

        $review->book?->updateRatingStats();

        $message = $review->is_visible
            ? 'Đã hiển thị lại bình luận trên trang sản phẩm.'
            : 'Đã ẩn bình luận khỏi trang sản phẩm.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Lưu hoặc cập nhật phản hồi của cửa hàng cho bình luận.
     */
    public function reply(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|min:2|max:1000',
        ], [
            'admin_reply.required' => 'Vui lòng nhập nội dung phản hồi.',
            'admin_reply.min' => 'Nội dung phản hồi tối thiểu 2 ký tự.',
            'admin_reply.max' => 'Nội dung phản hồi tối đa 1000 ký tự.',
        ]);

        $review->update([
            'admin_reply' => trim($validated['admin_reply']),
            'replied_by' => auth()->id(),
            'replied_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã lưu phản hồi của cửa hàng.');
    }

    /**
     * Xóa nội dung phản hồi của cửa hàng trên bình luận.
     */
    public function deleteReply(Review $review): RedirectResponse
    {
        $review->update([
            'admin_reply' => null,
            'replied_by' => null,
            'replied_at' => null,
        ]);

        return redirect()->back()->with('success', 'Đã xóa phản hồi của cửa hàng.');
    }

    /**
     * Xóa vĩnh viễn bình luận và cập nhật lại điểm đánh giá sách.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $book = $review->book;
        $review->delete();

        $book?->updateRatingStats();

        return redirect()->back()->with('success', 'Đã xóa bình luận khỏi hệ thống.');
    }
}
