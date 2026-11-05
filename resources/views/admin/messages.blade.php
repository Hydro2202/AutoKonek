@extends('layouts.admin')

@section('title', 'Messages')
@section('heading', 'Messages')
@section('subheading', 'Feedback sent through the Contact page.')

@section('content')
<div class="space-y-3">
    @forelse ($messages as $m)
        <article class="rounded-md border border-line bg-white p-5 text-sm">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-base font-semibold text-navy">{{ $m->subject }}</p>
                <p class="text-xs text-muted">{{ $m->created_at->format('M j, Y g:i A') }}</p>
            </div>
            <p class="mt-0.5 text-xs text-muted">{{ $m->name }}, <a href="mailto:{{ $m->email }}" class="text-steel hover:underline">{{ $m->email }}</a></p>
            <p class="mt-3 max-w-3xl whitespace-pre-line">{{ $m->message }}</p>
        </article>
    @empty
        <div class="rounded-md border border-dashed border-line-strong bg-white px-6 py-14 text-center text-sm text-muted">No messages yet.</div>
    @endforelse
</div>
<div class="mt-6">{{ $messages->links() }}</div>
@endsection
