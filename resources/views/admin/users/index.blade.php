@extends('layouts.panel')

@section('title', 'Users')
@section('heading', 'User management')
@section('subheading', 'Activate or deactivate customer and business-owner accounts.')

@section('content')
<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="lg:col-span-2"><label for="q" class="label">Search</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email" class="input"></div>
    <div>
        <label for="role" class="label">Role</label>
        <select id="role" name="role" class="input">
            <option value="">All roles</option>
            @foreach (['customer' => 'Customer', 'business_owner' => 'Business owner', 'admin' => 'Administrator'] as $v => $l)
                <option value="{{ $v }}" @selected(($filters['role'] ?? '') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="status" class="label">Status</label>
        <select id="status" name="status" class="input">
            <option value="">Any</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
        </select>
    </div>
    <div class="flex gap-2 sm:col-span-2 lg:col-span-4">
        <button class="btn-navy" type="submit">Filter</button>
        @if (array_filter($filters)) <a href="{{ route('admin.users.index') }}" class="btn-outline">Clear</a> @endif
    </div>
</form>

@if ($users->isEmpty())
    <x-empty-state icon="users" title="No users match" text="Try changing the filters." />
@else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-right">Action</th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td><p class="font-semibold">{{ $u->name }}</p><p class="text-xs text-muted">{{ $u->email }}</p></td>
                        <td><x-badge :status="$u->role" /></td>
                        <td><x-badge :status="$u->is_active ? 'active' : 'inactive'" /></td>
                        <td class="whitespace-nowrap">{{ $u->created_at->format('M j, Y') }}</td>
                        <td class="text-right">
                            @if ($u->isAdmin() || $u->id === auth()->id())
                                <span class="text-xs text-muted">Protected</span>
                            @else
                                <form method="POST" action="{{ route('admin.users.toggle', $u) }}"
                                      @if ($u->is_active) data-confirm="Deactivate {{ $u->name }}? They will be signed out and unable to log in." @endif>
                                    @csrf @method('PATCH')
                                    <button type="submit" class="{{ $u->is_active ? 'btn-outline text-danger' : 'btn-navy' }} btn-sm">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
@endif
@endsection
