@extends('layouts.admin')

@section('content')
@php
    $activeFilters = collect($filters ?? [])->filter(fn ($value) => $value !== null && $value !== '');
    $columnCount = 5 + (int) $hasMobile + (int) $hasRole + (int) $hasVerified;
@endphp

<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}?v=20261029">

<div class="le-users-page">
    <header class="le-users-heading">
        <div>
            <span class="le-users-eyebrow">USER MANAGEMENT</span>
            <h1>Users</h1>
            <p>Manage registered accounts, roles and email verification.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="le-users-btn le-users-btn--secondary">
            <span aria-hidden="true">←</span> Dashboard
        </a>
    </header>

    <section class="le-users-stats" aria-label="User statistics">
        <article class="le-users-stat"><span class="le-users-stat__icon">♙</span><div><span class="le-users-stat__label">Total users</span><strong>{{ number_format($totalUsers) }}</strong><small>All registered accounts</small></div></article>
        <article class="le-users-stat"><span class="le-users-stat__icon">✓</span><div><span class="le-users-stat__label">Email verified</span><strong>{{ $verifiedUsers === null ? '—' : number_format($verifiedUsers) }}</strong><small>Verified email addresses</small></div></article>
        <article class="le-users-stat"><span class="le-users-stat__icon">◷</span><div><span class="le-users-stat__label">Email unverified</span><strong>{{ $unverifiedUsers === null ? '—' : number_format($unverifiedUsers) }}</strong><small>Pending verification</small></div></article>
        <article class="le-users-stat"><span class="le-users-stat__icon">＋</span><div><span class="le-users-stat__label">Registered today</span><strong>{{ number_format($newUsers) }}</strong><small>New registrations today</small></div></article>
    </section>

    <section class="le-users-panel le-users-filter-panel">
        <div class="le-users-panel__heading">
            <div class="le-users-filter-title">
                <span class="le-users-filter-symbol" aria-hidden="true">⌕</span>
                <div><h2>Find users</h2><p>Search accounts or narrow by status and role.</p></div>
            </div>
            @if($activeFilters->isNotEmpty())
                <span class="le-users-filter-count">{{ $activeFilters->count() }} active {{ $activeFilters->count() === 1 ? 'filter' : 'filters' }}</span>
            @endif
        </div>

        <form action="{{ route('admin.users.index') }}" method="GET" class="le-users-filter-form">
            <div class="le-users-filter-row">
                <label class="le-users-search" for="q">
                    <span class="le-users-search__icon" aria-hidden="true">⌕</span>
                    <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, email, mobile or user ID" aria-label="Search users">
                </label>
                @if($hasRole)
                    <label class="le-users-field" for="role"><span>Role</span><select id="role" name="role"><option value="">All roles</option><option value="user" @selected(($filters['role'] ?? '') === 'user')>User</option><option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option></select></label>
                @endif
                @if($hasVerified)
                    <label class="le-users-field" for="verification"><span>Email status</span><select id="verification" name="verification"><option value="">Any status</option><option value="verified" @selected(($filters['verification'] ?? '') === 'verified')>Verified</option><option value="unverified" @selected(($filters['verification'] ?? '') === 'unverified')>Unverified</option></select></label>
                @endif
                <div class="le-users-filter-actions">
                    <button type="submit" class="le-users-btn le-users-btn--primary">Search users <span aria-hidden="true">→</span></button>
                    @if($activeFilters->isNotEmpty())
                        <a href="{{ route('admin.users.index') }}" class="le-users-reset">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="le-users-panel le-users-results">
        <div class="le-users-panel__heading le-users-results__heading">
            <div><h2>Registered users</h2><p>{{ number_format($users->total()) }} {{ $users->total() === 1 ? 'account' : 'accounts' }} found · Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</p></div>
        </div>
        <div class="le-users-table-wrap">
            <table class="le-users-table">
                <thead><tr><th>User</th><th>User ID</th><th>Email address</th>@if($hasMobile)<th>Mobile</th>@endif @if($hasRole)<th>Role</th>@endif @if($hasVerified)<th>Email status</th>@endif<th>Registered</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($users as $item)
                    <tr>
                        <td><div class="le-users-identity"><span class="le-users-avatar">{{ strtoupper(substr($item->name ?: 'U', 0, 1)) }}</span><span class="le-users-identity__text"><strong>{{ $item->name ?: 'Unnamed User' }}</strong><small>Registered account</small></span></div></td>
                        <td><span class="le-users-id">#{{ $item->id }}</span></td>
                        <td><span class="le-users-email">{{ $item->email }}</span></td>
                        @if($hasMobile)<td>{{ $item->mobile ?: '—' }}</td>@endif
                        @if($hasRole)<td><span class="le-users-role">{{ ucfirst($item->role ?? 'unknown') }}</span></td>@endif
                        @if($hasVerified)<td>@if($item->email_verified_at)<span class="le-users-status le-users-status--verified">Verified</span>@else<span class="le-users-status le-users-status--pending">Unverified</span>@endif</td>@endif
                        <td><span class="le-users-date">{{ $item->created_at?->format('d M Y') ?? '—' }}</span><small class="le-users-date__time">{{ $item->created_at?->format('h:i A') ?? '' }}</small></td>
                        <td><a href="{{ route('admin.users.show', $item) }}" class="le-users-view">View details <span aria-hidden="true">↗</span></a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $columnCount }}" class="le-users-empty"><span class="le-users-empty__icon">⌕</span><strong>No users found</strong><p>Try different search terms or reset your filters.</p><a href="{{ route('admin.users.index') }}">Clear filters</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="le-users-pagination">{{ $users->withQueryString()->links() }}</div>
        @endif
    </section>
</div>
@endsection
