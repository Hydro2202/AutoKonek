@extends('layouts.panel')

@section('title', 'Categories')
@section('heading', 'Categories')
@section('subheading', 'Inactive categories are hidden from filters and from new submissions.')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="table-wrap lg:col-span-2">
        <table class="table">
            <thead><tr><th>Category</th><th>Listings</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($categories as $c)
                    <tr>
                        <td><p class="font-semibold text-navy">{{ $c->name }}</p><p class="text-xs text-muted">{{ $c->description }}</p></td>
                        <td><a href="{{ route('admin.categories.show', $c) }}" class="font-semibold text-steel hover:underline">{{ $c->establishments_count }}</a></td>
                        <td><x-badge :status="$c->is_active ? 'active' : 'inactive'" /></td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $c) }}" class="btn-outline btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.update', $c) }}">
                                    @csrf @method('PUT')<input type="hidden" name="toggle" value="1">
                                    <button type="submit" class="btn-outline btn-sm">{{ $c->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" data-confirm="Delete the category &quot;{{ $c->name }}&quot;?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline btn-sm text-danger" aria-label="Delete {{ $c->name }}"><x-icon name="trash" class="size-3.5" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="card h-fit space-y-3 p-5">
        @csrf
        <h2 class="text-base">New category</h2>
        <x-input name="name" label="Name" required />
        <x-textarea name="description" label="Description" rows="3" />
        <button type="submit" class="btn-primary w-full">Create category</button>
    </form>
</div>
@endsection
