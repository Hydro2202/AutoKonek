@extends('layouts.panel')

@section('title', 'Edit category')
@section('heading', 'Edit category')

@section('content')
<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card max-w-xl space-y-4 p-6">
    @csrf @method('PUT')
    <x-input name="name" label="Name" :value="$category->name" required />
    <x-textarea name="description" label="Description" :value="$category->description" rows="3" />
    <div class="flex gap-2">
        <button type="submit" class="btn-primary">Save changes</button>
        <a href="{{ route('admin.categories.index') }}" class="btn-outline">Cancel</a>
    </div>
</form>
@endsection
