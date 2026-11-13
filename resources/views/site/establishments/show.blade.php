@extends('layouts.site')

@section('title', $establishment->name)
@section('description', Str::limit($establishment->description, 150))

@php
    $average = round((float) ($establishment->reviews_avg_rating ?? 0), 1);
    $total = (int) $establishment->reviews_count;
    $user = auth()->user();
    $images = $establishment->images;
    $count = $images->count();
@endphp

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <nav class="flex flex-wrap items-center gap-1.5 text-sm text-muted" aria-label="Breadcrumb">
        <a href="{{ route('establishments.index') }}" class="hover:text-navy hover:underline">Find services</a>
        <x-ui.icon name="chevron-right" class="size-3.5" />
        <a href="{{ route('categories.show', $establishment->category) }}" class="hover:text-navy hover:underline">{{ $establishment->category->name }}</a>
    </nav>

    @unless ($establishment->isApproved())
        <div class="mt-4 flex items-start gap-3 rounded-md border border-warning/30 bg-warning-50 px-4 py-3 text-sm text-warning-700" role="status">
            <x-ui.icon name="info" class="mt-0.5 size-5 shrink-0" />
            <p>This listing is <strong>{{ $establishment->status }}</strong>. Only its owner and administrators can see this page.</p>
        </div>
    @endunless

    {{-- Title --}}
    <header class="mt-5 flex flex-wrap items-start justify-between gap-5">
        <div class="flex items-start gap-4">
            <span class="sign mt-1 hidden size-14 sm:inline-flex"><x-ui.icon :name="$establishment->category->icon()" class="size-7" /></span>
            <div>
                <p class="text-[0.9375rem] font-semibold text-steel">{{ $establishment->category->name }}</p>
                <h1 class="text-5xl sm:text-6xl">{{ $establishment->name }}</h1>
                <p class="mt-2 flex items-center gap-1.5 text-muted"><x-ui.icon name="map-pin" class="size-4 shrink-0" /> {{ $establishment->address }}, {{ $establishment->locationLabel() }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <x-ui.plate :rating="$average" :count="$total" class="!text-3xl" />
            <div class="text-sm text-muted">
                <p class="font-semibold text-ink">{{ $total }} {{ Str::plural('review', $total) }}</p>
                @if ($total) <a href="#reviews" class="text-steel underline underline-offset-2">Read them</a> @endif
            </div>
        </div>
    </header>

    {{-- Gallery --}}
    <div class="mt-6">
        @if ($count <= 1)
            <img src="{{ $establishment->imageUrl() }}" alt="{{ $establishment->name }}" class="aspect-[21/9] w-full rounded-lg object-cover">
        @else
            <div class="grid h-72 grid-cols-4 grid-rows-2 gap-2 overflow-hidden rounded-lg sm:h-[26rem]">
                @foreach ($images->take(5) as $image)
                    @php
                        $span = match (true) {
                            $loop->first => 'col-span-2 row-span-2',
                            $count === 2 => 'col-span-2 row-span-2',
                            $count === 3 => 'col-span-2',
                            default => '',
                        };
                    @endphp
                    <img src="{{ $image->url() }}" alt="{{ $establishment->name }}, photo {{ $loop->iteration }}" class="size-full object-cover {{ $span }}">
                @endforeach
            </div>
        @endif

        {{-- Photos from free sources must credit their author and licence. --}}
        @foreach ($images->take($count <= 1 ? 1 : 5)->whereNotNull('credit') as $image)
            <p class="mt-2 text-xs text-muted">
                @if ($image->credit_url)
                    <a href="{{ $image->credit_url }}" target="_blank" rel="noopener" class="underline underline-offset-2 hover:text-navy">{{ $image->credit }}</a>
                @else
                    {{ $image->credit }}
                @endif
            </p>
        @endforeach
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_21rem]">
        <div class="space-y-12">
            <section aria-labelledby="about-title">
                <h2 id="about-title" class="text-3xl">About this place</h2>
                <p class="mt-3 max-w-2xl leading-relaxed whitespace-pre-line text-ink/90">{{ $establishment->description }}</p>
            </section>

            {{-- Services as a menu board --}}
            <section aria-labelledby="services-title">
                <h2 id="services-title" class="text-3xl">Services and prices</h2>
                @if ($establishment->services->isEmpty())
                    <p class="mt-3 text-muted">This business has not listed its services yet. Call them to ask what they offer.</p>
                @else
                    <ul class="mt-4 max-w-2xl rounded-lg border border-line bg-white px-5 py-2">
                        @foreach ($establishment->services as $service)
                            <li class="py-3 {{ ! $loop->last ? 'border-b border-line' : '' }}">
                                <div class="flex items-end gap-3">
                                    <span class="font-semibold text-navy">{{ $service->name }}</span>
                                    <span class="leader"></span>
                                    <span class="font-display text-2xl leading-none font-semibold text-navy">{{ $service->price !== null ? '₱'.number_format($service->price, 2) : 'Ask' }}</span>
                                </div>
                                @if ($service->description)<p class="mt-0.5 text-sm text-muted">{{ $service->description }}</p>@endif
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-sm text-muted">Prices are set by the business and may change. Confirm before you go.</p>
                @endif
            </section>

            {{-- Getting there: the real route is drawn on the page; map apps are one click away --}}
            @php $map = $establishment->mapLinks(); @endphp
            <section id="directions" aria-labelledby="directions-title" class="scroll-mt-24">
                <h2 id="directions-title" class="text-3xl">Getting there</h2>
                <p class="mt-2 flex items-start gap-2 text-[0.9375rem] text-ink/90">
                    <x-ui.icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-steel" />
                    <span>{{ $establishment->address }}, {{ $establishment->locationLabel() }}@if ($establishment->city?->region), {{ $establishment->city->region->name }}@endif</span>
                </p>

                <div @if ($map->hasCoordinates()) data-route-panel data-lat="{{ $establishment->latitude }}" data-lng="{{ $establishment->longitude }}" data-name="{{ $establishment->name }}" data-auto="{{ request()->boolean('directions') ? '1' : '0' }}" @endif
                     class="mt-4 overflow-hidden rounded-lg border border-line bg-white">
                    @if ($map->hasCoordinates())
                        <div data-route-stage class="relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-navy-900 text-white sm:aspect-[16/9]">
                            <div class="px-4 text-center">
                                <span class="sign mx-auto size-12 !bg-navy-800"><x-ui.icon name="navigation" class="size-6" /></span>
                                <p class="mt-3 font-display text-2xl">See the route from where you are</p>
                                <p class="mx-auto mt-1 max-w-sm text-sm text-white/65">Your phone or computer shares its position with this page, and the route is drawn on the map with turn-by-turn steps.</p>
                                <div class="mt-4 flex flex-wrap justify-center gap-2">
                                    <button type="button" data-route-start data-no-loading class="btn-primary"><x-ui.icon name="navigation" class="size-4" /> Get directions</button>
                                    <button type="button" data-route-map-only data-no-loading class="btn !border !border-white/30 text-white hover:bg-white/10">Show map only</button>
                                </div>
                            </div>
                            <span class="roadline absolute inset-x-0 bottom-4"></span>
                        </div>
                        <p data-route-summary role="status" hidden class="border-t border-line bg-steel-50 px-5 py-3 font-semibold text-navy"></p>
                        <ol data-route-steps hidden class="max-h-72 space-y-2 overflow-y-auto border-t border-line px-5 py-4 text-[0.9375rem]"></ol>
                    @else
                        <p class="border-b border-line bg-page px-5 py-3 text-sm text-muted">This listing has no GPS pin yet, so the buttons below search for its address in your maps app.</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 border-t border-line p-4">
                        <a href="{{ $map->googleDirections() }}" data-google-link target="_blank" rel="noopener" class="btn-outline"><x-ui.icon name="navigation" class="size-4" /> Open in Google Maps</a>
                        <a href="{{ $map->waze() }}" target="_blank" rel="noopener" class="btn-outline">Open in Waze</a>
                        <a href="{{ $map->openStreetMap() }}" target="_blank" rel="noopener" class="btn-outline">OpenStreetMap <x-ui.icon name="external" class="size-3.5" /></a>
                    </div>
                    <p data-route-message role="status" class="px-4 pb-4 text-sm text-danger-700 empty:hidden"></p>
                </div>
                <p class="mt-2 text-sm text-muted">The route and map come from OpenStreetMap services and load only when you press a button. Your position is not saved by AutoKonek.</p>
                @if ($establishment->osm_ref)
                    <p class="mt-1 text-sm text-muted">This listing was created from public map data (<a href="https://www.openstreetmap.org/{{ $establishment->osm_ref }}" target="_blank" rel="noopener" class="font-semibold text-steel underline underline-offset-2">OpenStreetMap</a>, &copy; OpenStreetMap contributors, <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="font-semibold text-steel underline underline-offset-2">ODbL</a>). The business has not claimed it yet, so prices and hours may be missing.</p>
                @endif
            </section>

            {{-- Reviews --}}
            <section id="reviews" aria-labelledby="reviews-title" class="scroll-mt-24">
                <h2 id="reviews-title" class="text-3xl">Reviews</h2>

                <div class="mt-4 grid items-center gap-6 rounded-lg border border-line bg-white p-5 sm:grid-cols-[auto_1fr]">
                    <div class="text-center sm:px-4">
                        <x-ui.plate :rating="$average" :count="$total" class="!text-5xl !px-5" />
                        <div class="mt-3 flex justify-center"><x-ui.stars :rating="$average" size="size-5" /></div>
                        <p class="mt-1 text-sm text-muted">{{ $total }} {{ Str::plural('review', $total) }}</p>
                    </div>
                    <ul class="space-y-1.5" aria-label="Rating breakdown">
                        @foreach ($distribution as $star => $number)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="w-12 text-muted">{{ $star }} {{ Str::plural('star', $star) }}</span>
                                <span class="h-2.5 flex-1 overflow-hidden rounded-full bg-page"><span class="block h-full rounded-full bg-accent" style="width: {{ $total ? round($number / $total * 100) : 0 }}%"></span></span>
                                <span class="w-6 text-right tabular-nums text-muted">{{ $number }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($canReview)
                    @php $ratingWords = [1 => 'Poor', 2 => 'Fair', 3 => 'Okay', 4 => 'Good', 5 => 'Great']; @endphp
                    <form id="review-form" method="POST" action="{{ $myReview ? route('reviews.update', $myReview) : route('reviews.store', $establishment) }}"
                          class="mt-5 scroll-mt-24 space-y-4 rounded-lg border border-line bg-white p-5">
                        @csrf
                        @if ($myReview) @method('PUT') @endif
                        <h3 class="text-2xl">{{ $myReview ? 'Edit your review' : 'Write a review' }}</h3>

                        {{-- Star picker: the same stars used to show ratings everywhere else (see .star-picker in forms.css). --}}
                        <fieldset class="star-picker">
                            <legend class="label">Your rating <span class="text-danger-700" aria-hidden="true">*</span></legend>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="star-picker-stars">
                                    @foreach ($ratingWords as $value => $text)
                                        <label class="star-picker-star" title="{{ $value }} – {{ $text }}">
                                            <input type="radio" name="rating" value="{{ $value }}" class="sr-only" required @checked((int) old('rating', $myReview?->rating) === $value)>
                                            <x-ui.icon name="star" class="size-8" />
                                            <span class="sr-only">{{ $value }} {{ Str::plural('star', $value) }}, {{ $text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <span class="star-picker-caption" aria-hidden="true">
                                    <span data-caption="0">Tap a star</span>
                                    @foreach ($ratingWords as $value => $text)
                                        <span data-caption="{{ $value }}">{{ $value }} of 5 · {{ $text }}</span>
                                    @endforeach
                                </span>
                            </div>
                            @error('rating') <p class="error-text">{{ $message }}</p> @enderror
                        </fieldset>

                        <x-form.textarea name="comment" label="What was it like?" :value="$myReview?->comment" required rows="3" hint="At least 5 characters. Be specific and fair." />

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                            <button type="submit" class="btn-primary">{{ $myReview ? 'Save changes' : 'Post review' }}</button>
                            @if ($myReview)
                                <button type="submit" form="delete-review" class="btn-danger-outline btn-sm">
                                    <x-ui.icon name="trash" class="size-3.5" /> Delete review
                                </button>
                            @endif
                        </div>
                    </form>

                    @if ($myReview)
                        {{-- Kept outside the review form (forms cannot nest); the Delete button above submits it. --}}
                        <form id="delete-review" method="POST" action="{{ route('reviews.destroy', $myReview) }}" data-confirm="Delete your review? This cannot be undone." class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                @elseif (! $user)
                    <p class="mt-5 rounded-lg border border-dashed border-line-strong bg-white p-5 text-[0.9375rem] text-muted">
                        <a href="{{ route('login') }}" class="font-semibold text-steel underline underline-offset-2">Log in</a> or
                        <a href="{{ route('register') }}" class="font-semibold text-steel underline underline-offset-2">create a customer account</a> to leave a review.
                    </p>
                @elseif (! $user->isCustomer())
                    <p class="mt-5 rounded-lg border border-dashed border-line-strong bg-white p-5 text-[0.9375rem] text-muted">Only customer accounts can post reviews.</p>
                @endif

                <div class="mt-6">
                    @forelse ($reviews as $review)
                        <article class="border-t border-line py-5 first:border-t-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-10 items-center justify-center rounded-full bg-steel-100 font-display text-xl font-bold text-navy">{{ strtoupper(substr($review->user->name, 0, 1)) }}</span>
                                    <div>
                                        <p class="font-semibold text-navy">{{ $review->user->name }}</p>
                                        <p class="text-sm text-muted">{{ $review->created_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                                <x-ui.stars :rating="$review->rating" />
                            </div>
                            <p class="mt-3 max-w-2xl leading-relaxed whitespace-pre-line">{{ $review->comment }}</p>
                            @auth
                                @if ($review->user_id !== $user->id)
                                    <a href="{{ route('reports.create', ['type' => 'review', 'id' => $review->id]) }}" class="mt-2 inline-flex items-center gap-1.5 text-sm text-muted hover:text-danger-700"><x-ui.icon name="flag" class="size-3.5" /> Report this review</a>
                                @endif
                            @endauth
                        </article>
                    @empty
                        <p class="text-muted">No reviews yet. Be the first to share what it was like.</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $reviews->fragment('reviews')->links() }}</div>
            </section>
        </div>

        {{-- Visit card --}}
        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-lg border border-line bg-white p-5">
                <h2 class="text-2xl">Visit</h2>
                <dl class="mt-4 space-y-4 text-[0.9375rem]">
                    <div class="flex gap-3">
                        <x-ui.icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div><dt class="sr-only">Address</dt><dd>{{ $establishment->address }}<br>{{ $establishment->locationLabel() }}</dd></div>
                    </div>
                    <div class="flex gap-3">
                        <x-ui.icon name="phone" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div><dt class="sr-only">Phone</dt>
                            <dd>@if ($establishment->contact_number)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $establishment->contact_number) }}" class="font-semibold text-navy hover:underline">{{ $establishment->contact_number }}</a>@else<span class="text-muted">No phone number listed</span>@endif</dd>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <x-ui.icon name="mail" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div class="min-w-0"><dt class="sr-only">Email</dt>
                            <dd class="break-words">@if ($establishment->contact_email)<a href="mailto:{{ $establishment->contact_email }}" class="font-semibold text-navy hover:underline">{{ $establishment->contact_email }}</a>@else<span class="text-muted">No email listed</span>@endif</dd>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <x-ui.icon name="clock" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div><dt class="sr-only">Opening hours</dt>
                            <dd class="whitespace-pre-line">{{ filled($establishment->opening_hours) ? $establishment->opening_hours : 'Opening hours not listed' }}</dd>
                        </div>
                    </div>
                </dl>
                @if ($map->hasCoordinates())
                    <button type="button" data-route-start data-no-loading class="btn-primary mt-5 w-full"><x-ui.icon name="navigation" class="size-4" /> Get directions</button>
                @else
                    <a href="{{ $map->googleDirections() }}" target="_blank" rel="noopener" class="btn-primary mt-5 w-full"><x-ui.icon name="navigation" class="size-4" /> Get directions</a>
                @endif
                @if ($establishment->contact_number)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $establishment->contact_number) }}" class="btn-outline mt-2 w-full"><x-ui.icon name="phone" class="size-4" /> Call now</a>
                @endif
            </div>

            @if ($establishment->isApproved())
                <div class="rounded-lg border border-dashed border-line-strong bg-white p-5 text-[0.9375rem]">
                    <p class="font-semibold text-navy">Something not right?</p>
                    <p class="mt-1 text-muted">Tell the moderators if this listing is wrong or inappropriate.</p>
                    @auth
                        @if ($establishment->user_id !== $user->id)
                            <a href="{{ route('reports.create', ['type' => 'establishment', 'id' => $establishment->id]) }}" class="btn-outline mt-3 w-full"><x-ui.icon name="flag" class="size-4" /> Report this listing</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-outline mt-3 w-full">Log in to report</a>
                    @endauth
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
