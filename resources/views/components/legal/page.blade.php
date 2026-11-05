{{-- Shell for the legal pages: title, "last updated", plain-language summary and a table of contents. --}}
@props(['title', 'summary', 'sections'])
<section class="border-b border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <h1 class="text-5xl sm:text-6xl">{{ $title }}</h1>
        <p class="mt-2 text-sm text-muted">Last updated {{ \Illuminate\Support\Carbon::parse(config('autokonek.legal_updated'))->format('F j, Y') }}</p>
        <p class="mt-5 max-w-2xl rounded-md border-l-4 border-accent bg-accent-100/60 px-4 py-3 text-[0.9375rem] leading-relaxed text-ink">
            <span class="font-semibold">In short:</span> {{ $summary }}
        </p>
    </div>
</section>

<div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-16">
    <nav aria-label="On this page" class="lg:sticky lg:top-24 lg:self-start">
        <p class="text-sm font-semibold text-navy">On this page</p>
        <ol class="mt-3 space-y-1.5 border-l border-line text-[0.9375rem]">
            @foreach ($sections as $id => $label)
                <li><a href="#{{ $id }}" class="-ml-px block border-l-2 border-transparent py-0.5 pl-3 text-muted hover:border-accent hover:text-navy">{{ $label }}</a></li>
            @endforeach
        </ol>
        <div class="mt-8 flex flex-col gap-1.5 text-sm">
            @unless (request()->routeIs('legal.privacy'))<a href="{{ route('legal.privacy') }}" class="font-semibold text-steel hover:underline">Privacy Policy</a>@endunless
            @unless (request()->routeIs('legal.terms'))<a href="{{ route('legal.terms') }}" class="font-semibold text-steel hover:underline">Terms of Service</a>@endunless
            @unless (request()->routeIs('legal.cookies'))<a href="{{ route('legal.cookies') }}" class="font-semibold text-steel hover:underline">Cookie Policy</a>@endunless
        </div>
    </nav>

    <article class="doc">{{ $slot }}</article>
</div>
