@extends('admin.layouts.master')

@section('title', 'Thêm Người dùng mới')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="p-2 rounded-lg bg-white border border-brand-border text-brand-muted hover:text-brand-text hover:bg-brand-surface transition-colors" title="Quay lại">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-xl font-bold font-display text-brand-text">Thêm Người dùng mới</h1>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded-xl border border-brand-border shadow-sm p-6 space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Họ tên -->
            <div>
                <label for="name" class="block text-sm font-medium text-brand-text mb-1">Họ tên <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Nhập họ tên đầy đủ"
                       class="w-full rounded-lg border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 transition-all">
                @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-brand-text mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="example@gmail.com"
                       class="w-full rounded-lg border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 transition-all">
                @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Phân quyền -->
            <div>
                <label for="role" class="block text-sm font-medium text-brand-text mb-1">Phân quyền <span class="text-red-500">*</span></label>
                <select name="role" id="role" required class="w-full rounded-lg border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 transition-all">
                    <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Khách hàng</option>
                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Nhân viên (Staff)</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                </select>
                @error('role') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Mật khẩu mới -->
            <div>
                <label for="password" class="block text-sm font-medium text-brand-text mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" required placeholder="Nhập mật khẩu"
                       class="w-full rounded-lg border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 transition-all">
                @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Điện thoại -->
            <div>
                <label for="phone" class="block text-sm font-medium text-brand-text mb-1">Số điện thoại</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại"
                       class="w-full rounded-lg border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 transition-all">
                @error('phone') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Địa chỉ -->
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-medium text-brand-text mb-1">Địa chỉ</label>
                <textarea name="address" id="address" rows="3" placeholder="Nhập địa chỉ"
                          class="w-full rounded-lg border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 transition-all">{{ old('address') }}</textarea>
                @error('address') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-5 border-t border-brand-border">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-brand-text bg-white border border-brand-border rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Hủy Bỏ
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-brand-accent rounded-lg hover:bg-brand-accent-hover transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Lưu Người Dùng
            </button>
        </div>
    </form>
</div>
@endsection
