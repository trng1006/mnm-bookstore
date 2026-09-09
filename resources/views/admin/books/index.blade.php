@extends('admin.layouts.master')

@section('title', 'Quản lý Sách')

@section('content')
<div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
    <div class="p-4 border-b border-brand-border flex justify-between items-center bg-gray-50">
        <h2 class="font-semibold text-brand-text">Danh sách ấn phẩm</h2>
        <a href="{{ route('admin.books.create') }}" class="px-4 py-2 bg-brand-accent text-white rounded hover:bg-brand-accent-hover text-sm font-medium">
            + Thêm sách mới
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-brand-muted text-xs uppercase tracking-wider border-b border-brand-border">
                    <th class="px-6 py-3 font-medium">ID</th>
                    <th class="px-6 py-3 font-medium">Hình ảnh</th>
                    <th class="px-6 py-3 font-medium">Tên sách</th>
                    <th class="px-6 py-3 font-medium">Tác giả</th>
                    <th class="px-6 py-3 font-medium">Danh mục</th>
                    <th class="px-6 py-3 font-medium">Giá bán</th>
                    <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border text-sm">
                @forelse($books as $book)
                <tr class="hover:bg-brand-surface/50">
                    <td class="px-6 py-4 text-brand-muted">{{ $book->id }}</td>
                    <td class="px-6 py-4">
                        <img src="{{ $book->cover_image }}" alt="cover" class="w-12 h-16 object-cover rounded shadow-sm onerror-placeholder">
                    </td>
                    <td class="px-6 py-4 font-medium text-brand-text">{{ $book->title }}</td>
                    <td class="px-6 py-4">{{ $book->author->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $book->category->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ number_format($book->price_current) }} đ</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.books.edit', $book) }}" class="text-blue-500 hover:underline">Sửa</a>
                        <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa sách này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-brand-muted">Không có dữ liệu sách.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-brand-border">
        {{ $books->links() }}
    </div>
</div>
@endsection
