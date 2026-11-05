@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Users')
@section('subheading', 'Activate or deactivate customer and business-owner accounts.')

@section('content')
<x-panel.card :title="$users->total().' '.Str::plural('user', $users->total())" :flush="true">
    <x-slot:actions>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label for="q" class="sr-only">Search</label>
            <div class="relative">
                <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted" />
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email" class="input !w-52 py-1.5 pl-8 text-sm">
            </div>
            <label for="role" class="sr-only">Role</label>
            <select id="role" name="role" class="input !w-40 py-1.5 text-sm">
                <option value="">All roles</option>
                @foreach (['customer' => 'Customer', 'business_owner' => 'Business owner', 'admin' => 'Administrator'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="input !w-32 py-1.5 text-sm">
                <option value="">Any status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            <button class="btn-navy btn-sm" type="submit">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">Reset</a>@endif
        </form>
    </x-slot:actions>

    @if ($users->isEmpty())
        <p class="px-5 py-14 text-center text-sm text-muted">No users match these filters.</p>
    @else
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-steel-100 text-xs font-semibold text-navy">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                    <div><p class="font-medium text-navy">{{ $u->name }}</p><p class="text-xs text-muted">{{ $u->email }}</p></div>
                                </div>
                            </td>
                            <td><x-ui.status :status="$u->role" /></td>
                            <td><x-ui.status :status="$u->is_active ? 'active' : 'inactive'" /></td>
                            <td class="whitespace-nowrap text-muted">{{ $u->created_at->format('M j, Y') }}</td>
                            <td class="text-right">
                                @if ($u->isAdmin() || $u->id === auth()->id())
                                    <span class="text-xs text-muted">Protected</span>
                                @else
                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}"
                                          @if ($u->is_active) data-confirm="Deactivate {{ $u->name }}? They will be signed out and cannot log in." @endif>
                                        @csrf @method('PATCH')
                                        <button type="submit" class="{{ $u->is_active ? 'btn-danger-outline' : 'btn-navy' }} btn-sm">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())<div class="border-t border-line px-5 py-3">{{ $users->links() }}</div>@endif
    @endif
</x-panel.card>
@endsection
