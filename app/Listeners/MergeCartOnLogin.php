<?php
namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Book;

class MergeCartOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $sessionCart = session()->get('cart', []);

        // Get or create DB cart for user
        $dbCart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Merge session items into DB cart
        foreach ($sessionCart as $bookId => $item) {
            $cartItem = CartItem::firstOrNew([
                'cart_id' => $dbCart->id,
                'book_id' => $bookId
            ]);
            $cartItem->quantity += $item['quantity'];
            $cartItem->save();
        }

        // Now load all DB items back to session so CartController still works
        $dbCart->load('items.book.author');
        $mergedCart = [];

        foreach ($dbCart->items as $item) {
            $book = $item->book;
            if (!$book) continue;
            
            // Adjust quantity to stock limit if needed
            $qty = min($item->quantity, $book->stock);
            if ($qty !== $item->quantity) {
                $item->update(['quantity' => $qty]);
            }

            $mergedCart[$book->id] = [
                'id' => $book->id,
                'title' => $book->title,
                'slug' => $book->slug,
                'cover_image' => $book->cover_image,
                'author' => $book->author->name ?? 'Chưa rõ tác giả',
                'price' => $book->price_current,
                'stock' => $book->stock,
                'quantity' => $qty,
            ];
        }

        session()->put('cart', $mergedCart);
    }
}
