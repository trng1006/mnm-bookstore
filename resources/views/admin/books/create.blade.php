@extends('admin.layouts.master')
@section('title', 'Thêm Sách Mới')
@section('content')
<div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm">
    <p>Form thêm sách (Chưa có giao diện chi tiết, vui lòng xây dựng sau)</p>
    <a href="{{ route('admin.books.index') }}" class="text-blue-500 hover:underline mt-4 block">Quay lại danh sách</a>
</div>
@endsection
