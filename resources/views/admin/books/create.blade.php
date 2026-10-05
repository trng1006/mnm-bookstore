@extends('admin.layouts.master')
@section('title', 'Thêm Sách Mới')
@section('content')
<div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Thêm ấn phẩm mới</h2>
        <a href="{{ route('admin.books.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm font-medium">Quay lại</a>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.books.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên sách <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent focus:border-brand-accent outline-none" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug URL <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục <span class="text-red-500">*</span></label>
                        <select name="category_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tác giả <span class="text-red-500">*</span></label>
                        <select name="author_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none" required>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ old('author_id') == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá bán hiện tại (VNĐ) <span class="text-red-500">*</span></label>
                        <input type="number" name="price_current" value="{{ old('price_current') }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá cũ (VNĐ)</label>
                        <input type="number" name="price_old" value="{{ old('price_old') }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">% Giảm giá</label>
                        <input type="number" name="discount_percent" value="{{ old('discount_percent') }}" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kho (Stock)</label>
                        <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL Ảnh Bìa</label>
                    <input type="url" name="cover_image" value="{{ old('cover_image') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-accent outline-none" placeholder="https://...">
                </div>
            </div>
        </div>

        <div class="flex gap-6 mb-6 pt-4 border-t border-gray-200">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_new" value="0">
                <input type="checkbox" name="is_new" value="1" {{ old('is_new', true) ? 'checked' : '' }} class="w-4 h-4 text-brand-accent rounded focus:ring-brand-accent">
                <span class="text-sm font-medium text-gray-700">Sách Mới</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_best_seller" value="0">
                <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller') ? 'checked' : '' }} class="w-4 h-4 text-brand-accent rounded focus:ring-brand-accent">
                <span class="text-sm font-medium text-gray-700">Bán Chạy (Best Seller)</span>
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-brand-accent text-white rounded-lg hover:bg-brand-accent-hover font-medium shadow-sm">
                Thêm Sách Mới
            </button>
        </div>
    </form>
</div>
@endsection
