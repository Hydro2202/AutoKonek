@extends('layouts.app')

@section('title', $establishment->name)
@section('description', Str::limit($establishment->description, 150))

@section('content')
@php
    $avg = round((float) ($establishment->reviews_avg_rating ?? 0), 1);
    $count = (int) $establishment->reviews_count;
    $user = auth()->user();
    $unavailable = fn ($v) => filled($v) ? $v : null;
@endphp

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <nav class="text-sm text-muted" aria-label="Breadcrumb">
        <a href="{{ route('establishments.index') }}" class="hover:underline">Find services</a> /
        <a href="{{ route('categories.show', $establishment->category) }}" class="hover:underline">{{ $establishment->category->name }}</a>
    </nav>

    @unless ($establishment->isApproved())
        <div class="mt-4 flex items-start gap-3 rounded-md border border-warning/30 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <x-icon name="info" class="mt-0.5 size-5 shrink-0" />
            <p>This listing is <strong>{{ $establishment->status }}</strong> and is only visible to its owner and administrators.</p>
        </div>
    @endunless

    <div class="mt-4 grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Images --}}
            <div class="card overflow-hidden">
                <img src="{{ $establishment->imageUrl() }}" alt="{{ $establishment->name }}" class="aspect-[16/9] w-full object-cover">
                @if ($establishment->images->count() > 1)
                    <div class="grid grid-cols-4 gap-2 p-2 sm:grid-cols-5">
                        @foreach ($establishment->images as $image)
                            <img src="{{ $image->url() }}" alt="{{ $establishment->name }} photo {{ $loop->iteration }}" loading="lazy" class="aspect-square w-full rounded object-cover">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card p-5 sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-steel">{{ $establishment->category->name }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl">{{ $establishment->name }}</h1>
                <x-stars class="mt-2" :rating="$avg" :count="$count" size="size-5" />
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-ink/90">{{ $establishment->description }}</p>
            </div>

            {{-- Services --}}
            <div class="card p-5 sm:p-6">
                <h2 class="text-lg">Services and prices</h2>
                @if ($establishment->services->isEmpty())
                    <p class="mt-3 text-sm text-muted">The business has not listed any services yet.</p>
                @else
                    <ul class="mt-3 divide-y divide-line">
                        @foreach ($establishment->services as $service)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold">{{ $service->name }}</p>
                                    @if ($service->description)<p class="text-xs text-muted">{{ $service->description }}</p>@endif
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-navy">
                                    {{ $service->price !== null ? 'PHP '.number_format($service->price, 2) : 'Ask for price' }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Reviews --}}
            <div class="card p-5 sm:p-6" id="reviews">
                <h2 class="text-lg">Customer reviews ({{ $count }})</h2>

                @if ($canReview)
                    <div class="mt-4 rounded-md bg-page p-4">
                        <h3 class="text-sm">{{ $myReview ? 'Edit your review' : 'Write a review' }}</h3>
                        <form method="POST" action="{{ $myReview ? route('reviews.update', $myReview) : route('reviews.store', $establishment) }}" class="mt-3 space-y-3">
                            @csrf
                            @if ($myReview) @method('PUT') @endif
                            <div>
                                <label for="rating" class="label">Rating <span class="text-danger">*</span></label>
                                <select id="rating" name="rating" required class="input sm:w-56 {{ $errors->has('rating') ? 'input-error' : '' }}">
                                    <option value="">Choose...</option>
                                    @foreach ([5 => 'Excellent', 4 => 'Good', 3 => 'Average', 2 => 'Poor', 1 => 'Terrible'] as $value => $text)
                                        <option value="{{ $value }}" @selected((int) old('rating', $myReview?->rating) === $value)>{{ $value }} - {{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-textarea name="comment" label="Your review" :value="$myReview?->comment" required rows="3" />
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary">{{ $myReview ? 'Save changes' : 'Post review' }}</button>
                            </div>
                        </form>
                        @if ($myReview)
                            <form method="POST" action="{{ route('reviews.destroy', $myReview) }}" data-confirm="Delete your review? This cannot be undone." class="mt-2">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-danger hover:underline">Delete my review</button>
                            </form>
                        @endif
                    </div>
                @elseif (! $user)
                    <p class="mt-3 rounded-md bg-page p-4 text-sm text-muted"><a href="{{ route('login') }}" class="font-semibold text-steel hover:underline">Log in</a> as a customer to write a review.</p>
                @elseif (! $user->isCustomer())
                    <p class="mt-3 rounded-md bg-page p-4 text-sm text-muted">Only customer accounts can post reviews.</p>
                @endif

                @forelse ($reviews as $review)
                    <article class="mt-5 border-t border-line pt-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold">{{ $review->user->name }}</p>
                                <p class="text-xs text-muted">{{ $review->created_at->format('M j, Y') }}</p>
                            </div>
                            <x-stars :rating="$review->rating" />
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm">{{ $review->comment }}</p>
                        @auth
                            @if ($review->user_id !== $user->id)
                                <a href="{{ route('reports.create', ['type' => 'review', 'id' => $review->id]) }}" class="mt-2 inline-flex items-center gap-1 text-xs text-muted hover:text-danger"><x-icon name="flag" class="size-3.5" /> Report review</a>
                            @endif
                        @endauth
                    </article>
                @empty
                    <p class="mt-4 text-sm text-muted">No reviews yet. Be the first to share your experience.</p>
                @endforelse

                <div class="mt-5">{{ $reviews->fragment('reviews')->links() }}</div>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-4">
            <div class="card p-5">
                <h2 class="text-lg">Contact and location</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex gap-3">
                        <x-icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div><dt class="sr-only">Address</dt><dd>{{ $establishment->address }}<br>{{ $establishment->locationLabel() }}</dd></div>
                    </div>
                    <div class="flex gap-3">
                        <x-icon name="phone" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div><dt class="sr-only">Phone</dt><dd>{{ $unavailable($establishment->contact_number) ?? 'Contact number not provided' }}</dd></div>
                    </div>
                    <div class="flex gap-3">
                        <x-icon name="mail" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div><dt class="sr-only">Email</dt><dd class="break-all">{{ $unavailable($establishment->contact_email) ?? 'Email not provided' }}</dd></div>
                    </div>
                    <div class="flex gap-3">
                        <x-icon name="clock" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <div><dt class="sr-only">Opening hours</dt><dd class="whitespace-pre-line">{{ $unavailable($establishment->opening_hours) ?? 'Opening hours not provided' }}</dd></div>
                    </div>
                </dl>
            </div>

            @if ($establishment->isApproved())
                <div class="card p-5 text-sm">
                    <p class="font-semibold text-navy">See something wrong?</p>
                    <p class="mt-1 text-muted">Report inaccurate or inappropriate information.</p>
                    @auth
                        @if ($establishment->user_id !== $user->id)
                            <a href="{{ route('reports.create', ['type' => 'establishment', 'id' => $establishment->id]) }}" class="btn-outline mt-3 w-full"><x-icon name="flag" class="size-4" /> Report this listing</a>
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
