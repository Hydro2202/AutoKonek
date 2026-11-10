@php
    $columns = [
        (auth()->check() ? 'Explore' : 'Get started') => auth()->check()
            ? [
                ['Find services', route('establishments.index')],
                ['Cities and regions', route('places.index')],
                ['About AutoKonek', route('about')],
                ['Contact us', route('contact')],
            ]
            : [
                ['Log in', route('login')],
                ['Create account', route('register')],
            ],
        'For businesses' => [
            ['List your business', route('register')],
            ['Owner login', route('login')],
        ],
        'Legal' => [
            ['Privacy Policy', route('legal.privacy')],
            ['Terms of Service', route('legal.terms')],
            ['Cookie Policy', route('legal.cookies')],
        ],
    ];
@endphp
<footer class="bg-navy-950 text-white/75">
    <div class="roadline mx-auto max-w-7xl"></div>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_repeat(3,1fr)]">
        <div>
            <x-brand.logo :light="true" />
            <p class="mt-4 max-w-xs text-[0.9375rem] leading-relaxed">Find the services your vehicle needs. Gas, wash, repair, tires, parking and batteries, all in one directory.</p>
            <p class="mt-4 text-sm text-white/50">{{ config('autokonek.location') }}</p>
        </div>
        @foreach ($columns as $heading => $links)
            <nav aria-label="{{ $heading }}">
                <h2 class="font-display text-lg font-semibold tracking-wide text-white">{{ $heading }}</h2>
                <ul class="mt-3 space-y-2 text-[0.9375rem]">
                    @foreach ($links as [$label, $url])
                        <li><a href="{{ $url }}" class="hover:text-white hover:underline hover:decoration-accent hover:underline-offset-4">{{ $label }}</a></li>
                    @endforeach
                    @if ($heading === 'Legal')
                        <li><a href="#" data-cookie-settings class="hover:text-white hover:underline hover:decoration-accent hover:underline-offset-4">Cookie settings</a></li>
                    @endif
                </ul>
            </nav>
        @endforeach
    </div>
    <div class="border-t border-white/10 px-4 py-5 text-center text-sm text-white/50">
        &copy; {{ date('Y') }} AutoKonek
    </div>
</footer>
