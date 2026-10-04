@extends('admin.layouts.master')

@section('title', 'Quản lý Danh mục')

@section('content')
<div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
    <div class="p-4 border-b border-brand-border flex justify-between items-center bg-gray-50">
        <h2 class="font-semibold text-brand-text">Danh sách danh mục</h2>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 bg-brand-accent text-white rounded hover:bg-brand-accent-hover text-sm font-medium">
            + Thêm danh mục
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-brand-muted text-xs uppercase tracking-wider border-b border-brand-border">
                    <th class="px-6 py-3 font-medium">ID</th>
                    <th class="px-6 py-3 font-medium">Tên danh mục</th>
                    <th class="px-6 py-3 font-medium">Slug</th>
                    <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border text-sm">
                @forelse($categories as $category)
                <tr class="hover:bg-brand-surface/50">
                    <td class="px-6 py-4 text-brand-muted">{{ $category->id }}</td>
                    <td class="px-6 py-4 font-medium text-brand-text">{{ $category->name }}</td>
                    <td class="px-6 py-4">{{ $category->slug }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-500 hover:underline">Sửa</a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-brand-muted">Không có danh mục nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-brand-border">
        {{ $categories->links() }}
    </div>
</div>
@endsection
