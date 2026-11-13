<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Hiển thị danh sách thông báo của người dùng
     */
    public function listNotifications(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $user->unreadNotifications()->take(5)->get(),
                'unreadCount' => $user->unreadNotifications()->count(),
            ]);
        }

        return view('client.pages.account.notifications', compact('notifications'));
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(string $id)
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $targetUrl = $notification->data['url'] ?? null;

        if ($targetUrl) {
            return redirect($targetUrl);
        }

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unreadCount' => 0,
            ]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
