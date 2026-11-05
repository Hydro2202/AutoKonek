@extends('layouts.admin')

@section('title', 'Edit category')
@section('heading', 'Edit category')

@section('content')
<x-panel.card title="Category details" class="max-w-xl">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-4">
        @csrf @method('PUT')
        <x-form.input name="name" label="Name" :value="$category->name" required />
        <x-form.textarea name="description" label="Description" :value="$category->description" rows="3" />
        <div class="flex gap-2">
            <button type="submit" class="btn-navy">Save changes</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</x-panel.card>
@endsection
