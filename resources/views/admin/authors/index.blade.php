@extends('admin.layouts.master')

@section('title', 'Quản lý Tác giả')

@section('content')
<div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
    <div class="p-4 border-b border-brand-border flex justify-between items-center bg-gray-50">
        <h2 class="font-semibold text-brand-text">Danh sách tác giả</h2>
        <a href="{{ route('admin.authors.create') }}" class="px-4 py-2 bg-brand-accent text-white rounded hover:bg-brand-accent-hover text-sm font-medium">
            + Thêm tác giả
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-brand-muted text-xs uppercase tracking-wider border-b border-brand-border">
                    <th class="px-6 py-3 font-medium">ID</th>
                    <th class="px-6 py-3 font-medium">Tên tác giả</th>
                    <th class="px-6 py-3 font-medium">Slug</th>
                    <th class="px-6 py-3 font-medium">Tiểu sử</th>
                    <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border text-sm">
                @forelse($authors as $author)
                <tr class="hover:bg-brand-surface/50">
                    <td class="px-6 py-4 text-brand-muted">{{ $author->id }}</td>
                    <td class="px-6 py-4 font-medium text-brand-text">{{ $author->name }}</td>
                    <td class="px-6 py-4">{{ $author->slug }}</td>
                    <td class="px-6 py-4 truncate max-w-xs" title="{{ $author->bio }}">{{ Str::limit($author->bio, 50) }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.authors.edit', $author) }}" class="text-blue-500 hover:underline">Sửa</a>
                        <form action="{{ route('admin.authors.destroy', $author) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa tác giả này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-brand-muted">Không có tác giả nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-brand-border">
        {{ $authors->links() }}
    </div>
</div>
@endsection
