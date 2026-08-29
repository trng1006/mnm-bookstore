<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
    @forelse($newBooks as $book)
        <a href="{{ route('books.show', $book->slug) }}" class="group block bg-white p-4 rounded-[var(--radius-card)] transition-all duration-500 hover:shadow-hover hover:-translate-y-2 border border-brand-border border-opacity-50 flex flex-col h-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent">
            
            <div class="relative bg-brand-surface aspect-[3/4] mb-5 rounded-2xl flex items-center justify-center text-brand-muted text-sm overflow-hidden">
                @if($book->isNew)
                    <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded-full z-10 shadow-sm {{ $book->tagColor }}">
                        {{ $book->tagText }}
                    </span>
                @endif
                @if($book->cover)
                    <img src="{{ $book->cover }}" alt="{{ $book->title }}" 
                         loading="lazy"
                         width="300" height="400"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                         onerror="this.outerHTML='<span class=\'font-ui font-medium opacity-50 group-hover:scale-105 transition-transform duration-700\'>Hình Bìa</span>'">
                @else
                    <span class="font-ui font-medium opacity-50 group-hover:scale-105 transition-transform duration-700">Hình Bìa</span>
                @endif
                <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-r from-black/5 to-transparent"></div>
            </div>
            
            <div class="px-2 flex flex-col flex-grow min-w-0">
                <h3 class="font-display text-lg font-bold mb-1 group-hover:text-brand-accent transition-colors truncate" title="{{ $book->title }}">{{ $book->title }}</h3>
                <div class="text-brand-muted text-sm font-ui mb-3 truncate">{{ $book->author }}</div>
                <div class="flex justify-between items-center mt-auto">
                    <span class="font-bold text-lg text-brand-text">{{ number_format($book->price, 0, ',', '.') }}&nbsp;₫</span>
                    <button class="w-10 h-10 rounded-full bg-brand-surface text-brand-text flex items-center justify-center group-hover:bg-brand-accent group-hover:text-white transition-colors duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent" aria-label="Thêm '{{ $book->title }}' vào giỏ">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </a>
    @empty
        <div class="col-span-full py-12 text-center text-brand-muted font-ui">
            Chưa có sách mới nào.
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="flex justify-center mt-10">
    {{ $newBooks->appends(['sort' => request('sort')])->links('vendor.pagination.minimal') }}
</div>
