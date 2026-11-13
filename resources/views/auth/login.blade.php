@extends('client.layouts.master')

@section('title', 'Đăng nhập')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white p-8 border border-brand-border rounded-[var(--radius-card)] shadow-sm">
    <h1 class="text-2xl font-display font-bold text-center mb-6">Đăng nhập</h1>
    
    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-3 rounded mb-4 text-sm">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="block text-brand-text font-bold mb-2">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border border-brand-border rounded focus:outline-none focus:border-brand-accent">
        </div>
        <div class="mb-4">
            <label for="password" class="block text-brand-text font-bold mb-2">Mật khẩu</label>
            <input id="password" type="password" name="password" required class="w-full px-3 py-2 border border-brand-border rounded focus:outline-none focus:border-brand-accent">
        </div>
        <div class="mb-4 flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="mr-2 rounded text-brand-accent focus:ring-brand-accent">
                <span class="text-sm text-brand-muted">Ghi nhớ đăng nhập</span>
            </label>
        </div>
        <button type="submit" class="w-full bg-brand-accent text-white font-bold py-2 px-4 rounded hover:bg-brand-accent-hover transition-colors">Đăng nhập</button>
    </form>
    <div class="mt-4 text-center text-sm">
        Chưa có tài khoản? <a href="{{ route('register') }}" class="text-brand-accent underline hover:text-brand-accent-hover">Đăng ký ngay</a>
    </div>
</div>
@endsection
