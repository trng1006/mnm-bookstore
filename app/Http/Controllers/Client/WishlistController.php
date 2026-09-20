<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Book;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Hiển thị danh sách yêu thích
     */
    public function index()
    {
        $wishlists = Wishlist::with('book')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);
            
        return view('client.pages.account.wishlist', compact('wishlists'));
    }

    /**
     * Thêm/Xóa sách khỏi danh sách yêu thích (AJAX)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id'
        ]);

        $bookId = $request->book_id;
        $userId = auth()->id();

        $wishlist = Wishlist::where('user_id', $userId)->where('book_id', $bookId)->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'is_wished' => false,
                'message' => 'Đã xóa khỏi danh sách yêu thích'
            ]);
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'book_id' => $bookId
            ]);
            return response()->json([
                'success' => true,
                'is_wished' => true,
                'message' => 'Đã thêm vào danh sách yêu thích'
            ]);
        }
    }
}
