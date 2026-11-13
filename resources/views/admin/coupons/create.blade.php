@extends('admin.layouts.master')

@section('title', 'Thêm Mã giảm giá')

@section('content')
<div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm max-w-3xl">
    <h2 class="font-semibold text-brand-text mb-6">Tạo mã giảm giá mới</h2>

    <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-4">
        @csrf
        @include('admin.coupons._form')

        <div class="flex items-center gap-4 pt-4 border-t border-brand-border">
            <button type="submit" class="px-5 py-2.5 bg-brand-accent text-white rounded-lg hover:bg-brand-accent-hover text-sm font-medium transition-colors cursor-pointer">Lưu mã giảm giá</button>
            <a href="{{ route('admin.coupons.index') }}" class="text-brand-muted hover:underline text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection
