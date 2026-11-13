<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::paginate(10);

        return view('admin.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('admin.authors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:authors',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string|max:255',
        ]);

        Author::create($validated);

        return redirect()->route('admin.authors.index')->with('success', 'Tác giả đã được thêm.');
    }

    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:authors,slug,'.$author->id,
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string|max:255',
        ]);

        $author->update($validated);

        return redirect()->route('admin.authors.index')->with('success', 'Tác giả đã được cập nhật.');
    }

    public function destroy(Author $author)
    {
        if ($author->books()->count() > 0) {
            return redirect()->route('admin.authors.index')
                ->with('error', 'Không thể xóa tác giả đang có sách trong hệ thống. Vui lòng chuyển hoặc xóa sách trước.');
        }

        $author->delete();

        return redirect()->route('admin.authors.index')->with('success', 'Tác giả đã bị xóa.');
    }
}
