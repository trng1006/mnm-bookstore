@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-brand-text bg-white border border-brand-border cursor-not-allowed leading-5 rounded-md dark:text-brand-text dark:bg-gray-700 dark:border-gray-600">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-brand-text bg-white border border-brand-border leading-5 rounded-md hover:text-brand-text focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-brand-surface active:text-brand-text transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-brand-text dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-brand-text hover:bg-brand-surface dark:hover:bg-gray-900 dark:hover:text-brand-text">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-brand-text bg-white border border-brand-border leading-5 rounded-md hover:text-brand-text focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-brand-surface active:text-brand-text transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-brand-text dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-brand-text hover:bg-brand-surface dark:hover:bg-gray-900 dark:hover:text-brand-text">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-brand-text bg-white border border-brand-border cursor-not-allowed leading-5 rounded-md dark:text-brand-text dark:bg-gray-700 dark:border-gray-600">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
