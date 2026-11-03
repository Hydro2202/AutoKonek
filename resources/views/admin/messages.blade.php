@extends('layouts.panel')

@section('title', 'Messages')
@section('heading', 'Contact messages')
@section('subheading', 'Feedback sent through the Contact page.')

@section('content')
@forelse ($messages as $m)
    <article class="card mb-3 p-5 text-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="font-semibold text-navy">{{ $m->subject }}</p>
            <p class="text-xs text-muted">{{ $m->created_at->format('M j, Y g:i A') }}</p>
        </div>
        <p class="text-xs text-muted">{{ $m->name }} &lt;<a href="mailto:{{ $m->email }}" class="text-steel hover:underline">{{ $m->email }}</a>&gt;</p>
        <p class="mt-2 whitespace-pre-line">{{ $m->message }}</p>
    </article>
@empty
    <x-empty-state icon="mail" title="No messages yet" />
@endforelse
<div class="mt-6">{{ $messages->links() }}</div>
@endsection
