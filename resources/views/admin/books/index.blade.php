@extends('admin.layouts.master')

@section('title', 'Quản lý Sách')

@section('content')
<div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
    <div class="p-4 border-b border-brand-border flex justify-between items-center bg-gray-50">
        <h2 class="font-semibold text-brand-text">Danh sách ấn phẩm</h2>
        <div class="flex gap-2">
            <form action="{{ route('admin.books.import') }}" method="POST" enctype="multipart/form-data" class="flex gap-2 items-center">
                @csrf
                <input type="file" name="csv_file" accept=".csv,.txt" required class="text-sm border border-brand-border rounded px-2 py-1 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-gray-100 file:text-brand-text hover:file:bg-gray-200" title="Định dạng: title, slug, category_id, author_id, price, stock">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Nhập CSV
                </button>
            </form>
            <a href="{{ route('admin.books.create') }}" class="px-4 py-2 bg-brand-accent text-white rounded hover:bg-brand-accent-hover text-sm font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Thêm sách mới
            </a>
        </div>
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
