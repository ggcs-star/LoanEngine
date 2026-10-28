
@extends('layouts.admin')

@section('content')
@php
    $activeFilters = collect($filters ?? [])
        ->filter(fn ($value) => $value !== null && $value !== '');

    $columnCount = 4
        + (int) $hasMobile
        + (int) $hasRole
        + (int) $hasVerified;
@endphp

<style>
.users-page {
    --u-text: #181b25;
    --u-muted: #70798b;
    --u-border: #e6e9f0;
    --u-primary: #315bea;
    padding: 24px;
    color: var(--u-text);
    min-width: 0;
}

.users-page *,
.users-page *::before,
.users-page *::after {
    box-sizing: border-box;
}

.users-page .u-heading,
.users-page .u-panel-heading,
.users-page .u-actions,
.users-page .u-results-heading,
.users-page .u-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
}

.users-page .u-heading {
    margin-bottom: 23px;
}

.users-page .u-eyebrow {
    margin: 0 0 6px;
    color: var(--u-primary);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.users-page .u-heading h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -.7px;
}

.users-page .u-heading p,
.users-page .u-panel-heading p {
    margin: 6px 0 0;
    color: var(--u-muted);
    font-size: 12px;
}

.users-page .u-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 39px;
    padding: 9px 13px;
    border: 1px solid var(--u-border);
    border-radius: 8px;
    background: #fff;
    color: var(--u-text);
    font: inherit;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.users-page .u-btn-primary {
    border-color: var(--u-primary);
    background: var(--u-primary);
    color: #fff;
}

.users-page .u-btn-primary:hover {
    background: #254bd2;
}

.users-page .u-btn:hover:not(.u-btn-primary) {
    background: #f8fafc;
}

.users-page .u-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}

.users-page .u-stat,
.users-page .u-panel {
    min-width: 0;
    border: 1px solid var(--u-border);
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 3px 12px rgba(20, 35, 60, .025);
}

.users-page .u-stat {
    padding: 19px;
}

.users-page .u-stat-label {
    display: block;
    color: var(--u-muted);
    font-size: 11px;
    font-weight: 600;
}

.users-page .u-stat strong {
    display: block;
    margin: 9px 0 5px;
    font-size: 27px;
    font-weight: 800;
}

.users-page .u-stat small {
    color: var(--u-muted);
    font-size: 10px;
}

.users-page .u-panel {
    margin-bottom: 20px;
    overflow: hidden;
}

.users-page .u-panel-heading {
    padding: 20px 22px;
}

.users-page .u-panel-heading h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
}

.users-page .u-filter-count {
    padding: 6px 10px;
    border-radius: 20px;
    background: #eef2ff;
    color: #315bea;
    font-size: 11px;
    font-weight: 750;
}

.users-page .u-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 17px;
    padding: 0 22px 22px;
}

.users-page .u-field {
    min-width: 0;
}

.users-page .u-field-wide {
    grid-column: 1 / -1;
}

.users-page .u-field label {
    display: block;
    margin-bottom: 7px;
    font-size: 11px;
    font-weight: 750;
}

.users-page .u-field input,
.users-page .u-field select {
    display: block;
    width: 100%;
    min-height: 40px;
    padding: 9px 11px;
    border: 1px solid #dfe4ec;
    border-radius: 7px;
    outline: none;
    background: #fff;
    color: var(--u-text);
    font: inherit;
    font-size: 12px;
}

.users-page .u-field input:focus,
.users-page .u-field select:focus {
    border-color: var(--u-primary);
    box-shadow: 0 0 0 3px rgba(49, 91, 234, .09);
}

.users-page .u-field small {
    display: block;
    margin-top: 5px;
    color: var(--u-muted);
    font-size: 10px;
}

.users-page .u-actions {
    justify-content: flex-start;
    padding: 14px 22px;
    border-top: 1px solid var(--u-border);
    background: #fafbfc;
}

.users-page .u-results-heading {
    padding-bottom: 17px;
}

.users-page .u-table-wrap {
    overflow-x: auto;
    width: 100%;
}

.users-page .u-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    white-space: nowrap;
    font-size: 11px;
}

.users-page .u-table thead {
    background: #f8f9fc;
}

.users-page .u-table th {
    padding: 13px 14px;
    border-top: 1px solid var(--u-border);
    border-bottom: 1px solid var(--u-border);
    color: #687386;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .4px;
    text-transform: uppercase;
}

.users-page .u-table td {
    padding: 13px 14px;
    border-bottom: 1px solid #edf0f4;
    vertical-align: middle;
}

.users-page .u-table tbody tr:hover {
    background: #fafbff;
}

.users-page .u-identity {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 175px;
}

.users-page .u-avatar {
    display: grid;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    place-items: center;
    border-radius: 10px;
    background: #edf1ff;
    color: #315bea;
    font-size: 13px;
    font-weight: 800;
}

.users-page .u-identity strong {
    display: block;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 11px;
}

.users-page .u-identity small,
.users-page .u-date small {
    display: block;
    margin-top: 4px;
    color: var(--u-muted);
    font-size: 10px;
}

.users-page .u-id {
    color: var(--u-muted);
    font-weight: 750;
}

.users-page .u-email {
    color: #3c4c65;
}

.users-page .u-role,
.users-page .u-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
}

.users-page .u-role {
    border-radius: 6px;
    background: #f0f3f8;
    color: #48566b;
}

.users-page .u-status > span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.users-page .u-status-verified {
    background: #e8f8ee;
    color: #167442;
}

.users-page .u-status-verified > span {
    background: #18a058;
}

.users-page .u-status-pending {
    background: #fff4df;
    color: #986313;
}

.users-page .u-status-pending > span {
    background: #d89a25;
}

.users-page .u-view {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 10px;
    border: 1px solid #dce4ff;
    border-radius: 6px;
    background: #f6f8ff;
    color: var(--u-primary);
    font-size: 10px;
    font-weight: 750;
    text-decoration: none;
}

.users-page .u-view:hover {
    background: #eaf0ff;
}

.users-page .u-empty {
    padding: 42px 18px !important;
    color: var(--u-muted);
    text-align: center;
    white-space: normal;
}

.users-page .u-empty strong {
    display: block;
    margin: 9px 0;
    color: var(--u-text);
    font-size: 14px;
}

.users-page .u-empty p {
    margin: 0 0 10px;
    font-size: 11px;
}

.users-page .u-pagination {
    justify-content: flex-end;
    padding: 17px 22px;
}

.users-page .u-pagination nav svg {
    width: 16px;
    height: 16px;
}

.users-page .u-pagination nav p {
    margin: 0;
}

.users-page .u-pagination nav a,
.users-page .u-pagination nav span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    min-height: 30px;
    margin: 2px;
    padding: 5px 9px;
    border: 1px solid var(--u-border);
    border-radius: 6px;
    color: #4b5261;
    font-size: 11px;
    text-decoration: none;
}

.users-page .u-pagination nav [aria-current="page"] span {
    border-color: var(--u-primary);
    background: var(--u-primary);
    color: #fff;
}

@media(max-width: 1050px) {
    .users-page .u-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .users-page .u-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media(max-width: 600px) {
    .users-page {
        padding: 13px;
    }

    .users-page .u-heading h1 {
        font-size: 23px;
    }

    .users-page .u-stats,
    .users-page .u-filter-grid {
        grid-template-columns: 1fr;
    }

    .users-page .u-field-wide {
        grid-column: auto;
    }

    .users-page .u-panel-heading {
        padding: 16px;
    }

    .users-page .u-filter-grid {
        padding: 0 16px 17px;
    }

    .users-page .u-actions {
        padding: 13px 16px;
    }
}
</style>

<div class="users-page">

    <header class="u-heading">
        <div>
            <p class="u-eyebrow">ADMINISTRATION</p>
            <h1>Users Management</h1>
            <p>Search, filter and review registered user accounts.</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="u-btn">
            ← Dashboard
        </a>
    </header>

    {{-- Statistics --}}
    <div class="u-stats">
        <div class="u-stat">
            <span class="u-stat-label">Total registered users</span>
            <strong>{{ number_format($totalUsers) }}</strong>
            <small>All registered accounts</small>
        </div>

        <div class="u-stat">
            <span class="u-stat-label">Email verified</span>
            <strong>
                {{ $verifiedUsers === null ? '—' : number_format($verifiedUsers) }}
            </strong>
            <small>Verified email addresses</small>
        </div>

        <div class="u-stat">
            <span class="u-stat-label">Email unverified</span>
            <strong>
                {{ $unverifiedUsers === null ? '—' : number_format($unverifiedUsers) }}
            </strong>
            <small>Pending verification</small>
        </div>

        <div class="u-stat">
            <span class="u-stat-label">Registered today</span>
            <strong>{{ number_format($newUsers) }}</strong>
            <small>New registrations today</small>
        </div>
    </div>

    {{-- Server-side filters --}}
    <section class="u-panel">
        <div class="u-panel-heading">
            <div>
                <h2>Search &amp; Filters</h2>
                <p>Combine multiple filters to narrow down results.</p>
            </div>

            <span class="u-filter-count">
                {{ $activeFilters->count() }} active
            </span>
        </div>

        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="u-filter-grid">

                <div class="u-field u-field-wide">
                    <label for="q">Quick search</label>
                    <input
                        type="search"
                        id="q"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Search name, email, mobile or numeric ID..."
                    >
                    <small>Searches across available user fields.</small>
                </div>

                <div class="u-field">
                    <label for="name">Full name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ $filters['name'] ?? '' }}"
                        placeholder="Enter name"
                    >
                </div>

                <div class="u-field">
                    <label for="email">Email address</label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        value="{{ $filters['email'] ?? '' }}"
                        placeholder="Search email"
                    >
                </div>

                @if($hasMobile)
                    <div class="u-field">
                        <label for="mobile">Mobile number</label>
                        <input
                            type="search"
                            id="mobile"
                            name="mobile"
                            value="{{ $filters['mobile'] ?? '' }}"
                            placeholder="Enter mobile number"
                        >
                    </div>
                @endif

                <div class="u-field">
                    <label for="user_id">User ID</label>
                    <input
                        type="number"
                        min="1"
                        id="user_id"
                        name="user_id"
                        value="{{ $filters['user_id'] ?? '' }}"
                        placeholder="Enter user ID"
                    >
                </div>

                @if($hasRole)
                    <div class="u-field">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="">All roles</option>
                            <option value="user" @selected(($filters['role'] ?? '') === 'user')>
                                User
                            </option>
                            <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>
                                Admin
                            </option>
                        </select>
                    </div>
                @endif

                @if($hasVerified)
                    <div class="u-field">
                        <label for="verification">Email verification</label>
                        <select id="verification" name="verification">
                            <option value="">All statuses</option>
                            <option value="verified" @selected(($filters['verification'] ?? '') === 'verified')>
                                Verified
                            </option>
                            <option value="unverified" @selected(($filters['verification'] ?? '') === 'unverified')>
                                Unverified
                            </option>
                        </select>
                    </div>
                @endif

                <div class="u-field">
                    <label for="from">Registered from</label>
                    <input
                        type="date"
                        id="from"
                        name="from"
                        value="{{ $filters['from'] ?? '' }}"
                        max="{{ $filters['to'] ?? '' }}"
                    >
                </div>

                <div class="u-field">
                    <label for="to">Registered to</label>
                    <input
                        type="date"
                        id="to"
                        name="to"
                        value="{{ $filters['to'] ?? '' }}"
                        min="{{ $filters['from'] ?? '' }}"
                    >
                </div>

                <div class="u-field">
                    <label for="per_page">Rows per page</label>
                    <select id="per_page" name="per_page">
                        @foreach([10, 15, 25, 50] as $size)
                            <option
                                value="{{ $size }}"
                                @selected((int) ($filters['per_page'] ?? 15) === $size)
                            >
                                {{ $size }} rows
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="u-actions">
                <button type="submit" class="u-btn u-btn-primary">
                    Apply filters
                </button>

                <a href="{{ route('admin.users.index') }}" class="u-btn">
                    Reset all
                </a>
            </div>
        </form>
    </section>

    {{-- Results --}}
    <section class="u-panel">
        <div class="u-panel-heading u-results-heading">
            <div>
                <h2>Registered Users</h2>
                <p>
                    Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}
                    of {{ number_format($users->total()) }} matching users
                </p>
            </div>
        </div>

        <div class="u-table-wrap">
            <table class="u-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>User ID</th>
                        <th>Email Address</th>

                        @if($hasMobile)
                            <th>Mobile</th>
                        @endif

                        @if($hasRole)
                            <th>Role</th>
                        @endif

                        @if($hasVerified)
                            <th>Email Status</th>
                        @endif

                        <th>Registration Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $item)
                        <tr>
                            <td>
                                <div class="u-identity">
                                    <span class="u-avatar">
                                        {{ strtoupper(substr($item->name ?: 'U', 0, 1)) }}
                                    </span>
                                    <div>
                                        <strong>{{ $item->name ?: 'Unnamed User' }}</strong>
                                        <small>Registered account</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="u-id">#{{ $item->id }}</span>
                            </td>

                            <td class="u-email">{{ $item->email }}</td>

                            @if($hasMobile)
                                <td>{{ $item->mobile ?: '—' }}</td>
                            @endif

                            @if($hasRole)
                                <td>
                                    <span class="u-role">
                                        {{ ucfirst($item->role ?? 'unknown') }}
                                    </span>
                                </td>
                            @endif

                            @if($hasVerified)
                                <td>
                                    @if($item->email_verified_at)
                                        <span class="u-status u-status-verified">
                                            <span></span> Verified
                                        </span>
                                    @else
                                        <span class="u-status u-status-pending">
                                            <span></span> Unverified
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <td class="u-date">
                                {{ $item->created_at?->format('d M Y') ?? '—' }}
                                <small>
                                    {{ $item->created_at?->format('h:i A') ?? '' }}
                                </small>
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.users.show', $item) }}"
                                    class="u-view"
                                >
                                    View details →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}" class="u-empty">
                                <strong>No users found</strong>
                                <p>Try different search terms or reset the filters.</p>
                                <a href="{{ route('admin.users.index') }}">
                                    Clear filters
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="u-pagination">
            {{ $users->links() }}
        </div>
    </section>

</div>
@endsection
