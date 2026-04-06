{{-- resources/views/components/page-header.blade.php --}}
@props(['pageTitle' => null, 'description' => null, 'breadcrumbs' => []])

<div class="mb-8">
    {{-- 1. Breadcrumbs --}}
    @if (count($breadcrumbs) > 0)
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm text-secondary">
                @foreach ($breadcrumbs as $crumb)
                    <li class="inline-flex items-center">
                        {{-- Separator Icon (Hide on first item) --}}
                        @if (!$loop->first)
                            <svg class="w-3 h-3 text-gray-400 mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 9 4-4-4-4" />
                            </svg>
                        @endif

                        {{-- Link or Active Text --}}
                        @if (isset($crumb['url']) && !$loop->last)
                            <a href="{{ $crumb['url'] }}" class="hover:text-primary transition-colors">
                                {{ $crumb['label'] }}
                            </a>
                        @else
                            {{-- The last item shouldn't be a link --}}
                            <span class="text-black font-semibold" aria-current="page">
                                {{ $crumb['label'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    {{-- 2. Page Heading --}}
    @if ($pageTitle)
        <h1 class="text-3xl font-semibold tracking-tight text-black sm:text-4xl">
            {{ $pageTitle }}
        </h1>
    @endif

    {{-- 3. Short Description --}}
    @if ($description)
        <p class="mt-2 text-lg text-gray-600 max-w-3xl">
            {{ $description }}
        </p>
    @endif
</div>
