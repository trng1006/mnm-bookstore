@if ($paginator->hasPages())
    <div class="flex items-center justify-center w-full my-8">
        <div class="inline-flex items-center gap-1 bg-white rounded-full shadow-soft px-4 py-2">
            
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="w-10 h-10 flex items-center justify-center text-brand-muted opacity-40 cursor-not-allowed" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-10 h-10 flex items-center justify-center text-brand-muted hover:text-brand-accent hover:bg-brand-surface rounded-full transition-colors" aria-label="@lang('pagination.previous')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="w-10 h-10 flex items-center justify-center text-brand-muted font-ui tracking-widest" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-accent text-white font-bold font-ui shadow-soft" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center rounded-full text-brand-muted hover:text-brand-accent hover:bg-brand-surface transition-colors font-ui">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-10 h-10 flex items-center justify-center text-brand-muted hover:text-brand-accent hover:bg-brand-surface rounded-full transition-colors" aria-label="@lang('pagination.next')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            @else
                <span class="w-10 h-10 flex items-center justify-center text-brand-muted opacity-40 cursor-not-allowed" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </span>
            @endif

            {{-- Go to Page Input --}}
            <div class="flex items-center gap-3 ml-2 pl-4 border-l border-brand-border">
                <span class="text-sm font-ui text-brand-muted whitespace-nowrap">Đến trang</span>
                <form onsubmit="event.preventDefault(); const url = new URL(window.location.href); url.searchParams.set('page', this.page.value); window.location.href = url.toString();" class="flex items-center">
                    <input type="number" name="page" min="1" max="{{ $paginator->lastPage() }}" placeholder="{{ $paginator->currentPage() }}" class="w-14 h-9 text-center bg-brand-surface border-none rounded-xl focus:ring-2 focus:ring-brand-accent text-sm font-ui text-brand-text font-bold outline-none appearance-none transition-shadow m-0 p-0" style="-moz-appearance: textfield;">
                </form>
            </div>
            
        </div>
    </div>
@endif
