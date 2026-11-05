@extends('layouts.admin')

@section('title', $category->name)
@section('heading', $category->name)
@section('subheading', 'Establishments in this category.')
@section('actions')<a href="{{ route('admin.categories.index') }}" class="btn-outline">Back to categories</a>@endsection

@section('content')
<x-panel.card :title="$establishments->total().' '.Str::plural('establishment', $establishments->total())" :flush="true">
    @if ($establishments->isEmpty())
        <p class="px-5 py-12 text-center text-sm text-muted">No establishments use this category yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Establishment</th><th>Owner</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach ($establishments as $e)
                        <tr>
                            <td class="font-medium text-navy">{{ $e->name }}</td>
                            <td>{{ $e->owner->name }}</td>
                            <td><x-ui.status :status="$e->status" /></td>
                            <td class="text-right"><a href="{{ route('admin.establishments.show', $e) }}" class="btn-outline btn-sm">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($establishments->hasPages())<div class="border-t border-line px-5 py-3">{{ $establishments->links() }}</div>@endif
    @endif
</x-panel.card>
@endsection
