@extends('layouts.admin')

@section('title', 'Categories')
@section('heading', 'Categories')
@section('subheading', 'Inactive categories are hidden from filters and from new submissions.')

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_21rem]">
    <x-panel.card title="All categories" :flush="true">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Category</th><th>Listings</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($categories as $c)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-[5px] bg-steel-50 text-steel"><x-ui.icon :name="$c->icon()" class="size-4" /></span>
                                    <div><p class="font-medium text-navy">{{ $c->name }}</p><p class="max-w-xs truncate text-xs text-muted">{{ $c->description }}</p></div>
                                </div>
                            </td>
                            <td><a href="{{ route('admin.categories.show', $c) }}" class="font-medium text-steel hover:underline">{{ $c->establishments_count }}</a></td>
                            <td><x-ui.status :status="$c->is_active ? 'active' : 'inactive'" /></td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.categories.edit', $c) }}" class="btn-icon" title="Edit" aria-label="Edit {{ $c->name }}"><x-ui.icon name="pencil" class="size-4" /></a>
                                    <form method="POST" action="{{ route('admin.categories.toggle', $c) }}">@csrf @method('PATCH')
                                        <button type="submit" class="btn-outline btn-sm">{{ $c->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" data-confirm="Delete the category &quot;{{ $c->name }}&quot;?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon text-danger-700" title="Delete" aria-label="Delete {{ $c->name }}"><x-ui.icon name="trash" class="size-4" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-panel.card>

    <x-panel.card title="New category" class="h-fit">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
            @csrf
            <x-form.input name="name" label="Name" required />
            <x-form.textarea name="description" label="Description" rows="3" />
            <button type="submit" class="btn-navy w-full">Create category</button>
        </form>
    </x-panel.card>
</div>
@endsection
