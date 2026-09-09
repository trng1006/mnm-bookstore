@extends('admin.layouts.master')

@section('title', 'Quản lý Người dùng')

@section('content')
<div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
    <div class="p-4 border-b border-brand-border flex justify-between items-center bg-gray-50">
        <h2 class="font-semibold text-brand-text">Danh sách người dùng</h2>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-brand-accent text-white rounded hover:bg-brand-accent-hover text-sm font-medium">
            + Thêm người dùng
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-brand-muted text-xs uppercase tracking-wider border-b border-brand-border">
                    <th class="px-6 py-3 font-medium">ID</th>
                    <th class="px-6 py-3 font-medium">Họ Tên</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Vai trò</th>
                    <th class="px-6 py-3 font-medium">Ngày tạo</th>
                    <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border text-sm">
                @forelse($users as $user)
                <tr class="hover:bg-brand-surface/50">
                    <td class="px-6 py-4 text-brand-muted">{{ $user->id }}</td>
                    <td class="px-6 py-4 font-medium text-brand-text">{{ $user->name }}</td>
                    <td class="px-6 py-4">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @if($user->isAdmin())
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Admin</span>
                        @elseif($user->isStaff())
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">Staff</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">Customer</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-500 hover:underline">Sửa</a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">Xóa</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-brand-muted">Không có dữ liệu người dùng.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-brand-border">
        {{ $users->links() }}
    </div>
</div>
@endsection
