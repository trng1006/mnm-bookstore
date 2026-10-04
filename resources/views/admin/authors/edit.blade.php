@extends('admin.layouts.master')

@section('title', 'Cập nhật Tác giả')

@section('content')
<div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm max-w-2xl">
    <h2 class="font-semibold text-brand-text mb-6">Cập nhật tác giả: {{ $author->name }}</h2>

    <form action="{{ route('admin.authors.update', $author) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm font-medium text-brand-text mb-1">Tên tác giả</label>
            <input type="text" name="name" value="{{ $author->name }}" class="w-full rounded border-brand-border px-3 py-2 text-sm focus:ring-brand-accent focus:border-brand-accent" required>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-brand-text mb-1">Slug (URL)</label>
            <input type="text" name="slug" value="{{ $author->slug }}" class="w-full rounded border-brand-border px-3 py-2 text-sm focus:ring-brand-accent focus:border-brand-accent" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-brand-text mb-1">Tiểu sử (Tùy chọn)</label>
            <textarea name="bio" rows="4" class="w-full rounded border-brand-border px-3 py-2 text-sm focus:ring-brand-accent focus:border-brand-accent">{{ $author->bio }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-brand-text mb-1">Ảnh đại diện (URL - Tùy chọn)</label>
            <input type="text" name="avatar" value="{{ $author->avatar }}" class="w-full rounded border-brand-border px-3 py-2 text-sm focus:ring-brand-accent focus:border-brand-accent">
        </div>

        <div class="flex items-center gap-4 mt-6">
            <button type="submit" class="px-4 py-2 bg-brand-accent text-white rounded hover:bg-brand-accent-hover text-sm font-medium">Cập nhật</button>
            <a href="{{ route('admin.authors.index') }}" class="text-brand-muted hover:underline text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection
