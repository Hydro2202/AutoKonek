@extends('layouts.panel')

@section('title', $category->name)
@section('heading', $category->name)
@section('subheading', 'Establishments in this category.')
@section('actions')<a href="{{ route('admin.categories.index') }}" class="btn-outline">Back to categories</a>@endsection

@section('content')
@if ($establishments->isEmpty())
    <x-empty-state icon="building" title="No establishments in this category" />
@else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Establishment</th><th>Owner</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($establishments as $e)
                    <tr>
                        <td class="font-semibold text-navy">{{ $e->name }}</td>
                        <td>{{ $e->owner->name }}</td>
                        <td><x-badge :status="$e->status" /></td>
                        <td class="text-right"><a href="{{ route('admin.establishments.show', $e) }}" class="btn-outline btn-sm">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $establishments->links() }}</div>
@endif
@endsection
