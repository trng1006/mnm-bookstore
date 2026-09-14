<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBooks = Book::count();
        $totalUsers = User::count();
        // Assuming we have orders later
        $totalOrders = 0;

        return view('admin.dashboard', compact('totalBooks', 'totalUsers', 'totalOrders'));
    }
}
