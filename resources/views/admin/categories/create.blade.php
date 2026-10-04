@extends('admin.layouts.master')

@section('title', 'Thêm Danh mục Mới')

@section('content')
<div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm max-w-2xl">
    <h2 class="font-semibold text-brand-text mb-6">Thêm danh mục mới</h2>

    <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-brand-text mb-1">Tên danh mục</label>
            <input type="text" name="name" class="w-full rounded border-brand-border px-3 py-2 text-sm focus:ring-brand-accent focus:border-brand-accent" required>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-brand-text mb-1">Slug (URL)</label>
            <input type="text" name="slug" class="w-full rounded border-brand-border px-3 py-2 text-sm focus:ring-brand-accent focus:border-brand-accent" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-brand-text mb-1">Mô tả (Tùy chọn)</label>
            <textarea name="description" rows="3" class="w-full rounded border-brand-border px-3 py-2 text-sm focus:ring-brand-accent focus:border-brand-accent"></textarea>
        </div>

        <div class="flex items-center gap-4 mt-6">
            <button type="submit" class="px-4 py-2 bg-brand-accent text-white rounded hover:bg-brand-accent-hover text-sm font-medium">Lưu danh mục</button>
            <a href="{{ route('admin.categories.index') }}" class="text-brand-muted hover:underline text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection
