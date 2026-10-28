
@extends('layouts.admin')

@section('content')
<style>
    .ud-page {
        --ud-text: #171923;
        --ud-muted: #737b8c;
        --ud-border: #e8eaf0;
        --ud-bg: #f6f7fb;
        padding: 22px;
        color: var(--ud-text);
        font-family: inherit;
        min-width: 0;
    }

    .ud-page * { box-sizing: border-box; }

    .ud-page .ud-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }

    .ud-page .ud-eyebrow {
        margin: 0 0 5px;
        color: #596174;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .ud-page .ud-title {
        margin: 0;
        color: #11131b;
        font-size: clamp(24px, 3vw, 31px);
        font-weight: 800;
        letter-spacing: -.8px;
    }

    .ud-page .ud-description {
        margin: 5px 0 0;
        color: var(--ud-muted);
        font-size: 12px;
    }

    .ud-page .ud-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border: 1px solid #e1e4eb;
        border-radius: 8px;
        background: #fff;
        color: #292d38;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .ud-page .ud-back:hover { background: #f8fafc; }

    .ud-page .ud-card {
        min-width: 0;
        margin-bottom: 14px;
        border: 1px solid #eceef3;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
    }

    /* Profile */
    .ud-page .ud-profile {
        display: grid;
        grid-template-columns: 190px repeat(3, minmax(0, 1fr));
        align-items: stretch;
        padding: 18px 14px;
    }

    .ud-page .ud-identity {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4px 15px 12px;
        text-align: center;
    }

    .ud-page .ud-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 82px;
        height: 82px;
        margin-bottom: 12px;
        border-radius: 50%;
        background: #f0efff;
        color: #111827;
        font-size: 34px;
        font-weight: 800;
    }

    .ud-page .ud-name {
        max-width: 100%;
        margin: 0 0 7px;
        overflow-wrap: anywhere;
        font-size: 14px;
        font-weight: 800;
    }

    .ud-page .ud-role {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        background: #f0f1f5;
        color: #3c4250;
        font-size: 10px;
        font-weight: 750;
    }

    .ud-page .ud-edit {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 7px;
        width: 100%;
        max-width: 150px;
        margin-top: 13px;
        padding: 10px 12px;
        border: 0;
        border-radius: 8px;
        background: #111217;
        color: white;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .ud-page .ud-edit:hover {
        background: #292b33;
        color: white;
    }

    .ud-page .ud-profile-column {
        min-width: 0;
        padding: 9px 17px;
        border-left: 1px solid #eceef3;
    }

    .ud-page .ud-column-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f1f5;
        font-size: 12px;
        font-weight: 800;
    }

    .ud-page .ud-column-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 29px;
        width: 29px;
        height: 29px;
        border-radius: 50%;
        background: #f3f4f7;
        font-size: 14px;
    }

    .ud-page .ud-info {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-width: 0;
        margin-bottom: 13px;
    }

    .ud-page .ud-info:last-child { margin-bottom: 0; }

    .ud-page .ud-info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 29px;
        width: 29px;
        height: 29px;
        border-radius: 50%;
        background: #f5f6f9;
        font-size: 13px;
    }

    .ud-page .ud-info-content { min-width: 0; }

    .ud-page .ud-info-label {
        display: block;
        margin-bottom: 3px;
        color: var(--ud-muted);
        font-size: 10px;
    }

    .ud-page .ud-info-value {
        display: block;
        overflow-wrap: anywhere;
        color: #20232d;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.55;
    }

    .ud-page .ud-verification {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 750;
    }

    .ud-page .ud-verified {
        background: #dcfce7;
        color: #15803d;
    }

    .ud-page .ud-unverified {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Section heading */
    .ud-page .ud-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 17px;
    }

    .ud-page .ud-section-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 31px;
        width: 31px;
        height: 31px;
        border-radius: 8px;
        background: #f0f1f5;
        font-size: 17px;
    }

    .ud-page .ud-section-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: -.25px;
    }

    .ud-page .ud-section-description {
        margin: 4px 0 0;
        color: var(--ud-muted);
        font-size: 11px;
    }

    /* Statistics */
    .ud-page .ud-activity {
        padding: 17px;
    }

    .ud-page .ud-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 13px;
    }

    .ud-page .ud-stat {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 15px;
        border-radius: 10px;
    }

    .ud-page .ud-stat-total { background: #f3f4fa; }
    .ud-page .ud-stat-completed { background: #edfbf1; }
    .ud-page .ud-stat-failed { background: #fff0f0; }

    .ud-page .ud-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 39px;
        width: 39px;
        height: 39px;
        border-radius: 50%;
        background: rgba(255,255,255,.75);
        font-size: 20px;
    }

    .ud-page .ud-stat-label {
        display: block;
        margin-bottom: 5px;
        color: #6d7484;
        font-size: 10px;
    }

    .ud-page .ud-stat-number {
        display: block;
        font-size: 25px;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -.6px;
    }

    .ud-page .ud-stat-total .ud-stat-number { color: #171923; }
    .ud-page .ud-stat-completed .ud-stat-number { color: #16803d; }
    .ud-page .ud-stat-failed .ud-stat-number { color: #cf2525; }

    /* History */
    .ud-page .ud-history {
        padding: 17px;
    }

    .ud-page .ud-history-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 18px;
    }

    .ud-page .ud-filters {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ud-page .ud-search {
        position: relative;
        min-width: 205px;
    }

    .ud-page .ud-search input {
        width: 100%;
        min-height: 37px;
        padding: 9px 11px 9px 32px;
        border: 1px solid #e5e7ed;
        border-radius: 7px;
        outline: none;
        background: white;
        color: #242733;
        font: inherit;
        font-size: 10px;
    }

    .ud-page .ud-search input:focus,
    .ud-page .ud-filter-select:focus,
    .ud-page .ud-date-input:focus {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 3px rgba(99,102,241,.09);
    }

    .ud-page .ud-search-icon {
        position: absolute;
        top: 10px;
        left: 11px;
        color: #64748b;
        font-size: 13px;
        pointer-events: none;
    }

    .ud-page .ud-filter-select,
    .ud-page .ud-date-input {
        min-height: 37px;
        max-width: 100%;
        padding: 8px 10px;
        border: 1px solid #e5e7ed;
        border-radius: 7px;
        background: #fff;
        color: #343847;
        font: inherit;
        font-size: 10px;
    }

    .ud-page .ud-filter-select { width: 91px; }
    .ud-page .ud-date-input { width: 130px; }

    .ud-page .ud-table-wrap {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #e9ebf1;
        border-radius: 9px;
    }

    .ud-page .ud-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
        text-align: left;
        font-size: 11px;
    }

    .ud-page .ud-table thead { background: #f8f9fb; }

    .ud-page .ud-table th {
        padding: 12px 10px;
        border-bottom: 1px solid #e9ebf1;
        color: #667084;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ud-page .ud-table td {
        padding: 11px 10px;
        border-bottom: 1px solid #f0f1f5;
        color: #333746;
        vertical-align: middle;
    }

    .ud-page .ud-table tbody tr:hover > td {
        background: #fcfcfe;
    }

    .ud-page .ud-table tbody tr:last-child > td {
        border-bottom: 0;
    }

    .ud-page .ud-expand {
        width: 26px;
        color: #6b7280;
        text-align: center;
    }

    .ud-page .ud-expand summary {
        cursor: pointer;
        list-style: none;
        font-size: 15px;
    }

    .ud-page .ud-expand summary::-webkit-details-marker { display: none; }
    .ud-page .ud-expand summary::before { content: "›"; font-size: 19px; }
    .ud-page .ud-expand[open] summary::before { content: "⌄"; }

    .ud-page .ud-row-id {
        font-weight: 800;
        white-space: nowrap;
    }

    .ud-page .ud-calculator-name {
        min-width: 160px;
        color: #242735;
        font-weight: 700;
    }

    .ud-page .ud-event {
        display: inline-block;
        padding: 4px 7px;
        border-radius: 5px;
        background: #eef0ff;
        color: #4953a8;
        font-size: 10px;
        font-weight: 700;
    }

    .ud-page .ud-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 750;
        white-space: nowrap;
    }

    .ud-page .ud-status::before {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        content: "";
    }

    .ud-page .ud-status-completed {
        background: #dcfce7;
        color: #15803d;
    }

    .ud-page .ud-status-failed {
        background: #fee2e2;
        color: #b91c1c;
    }

    .ud-page .ud-status-other {
        background: #f1f5f9;
        color: #475569;
    }

    .ud-page .ud-date {
        min-width: 145px;
        color: #51586a;
        white-space: nowrap;
    }

    .ud-page .ud-view-link {
        display: inline-block;
        padding: 7px 10px;
        border-radius: 6px;
        background: #edf2ff;
        color: #3157b7;
        font-size: 10px;
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap;
    }

    .ud-page .ud-view-link:hover { background: #dfe8ff; }

    /* Expanded input and result panels */
    .ud-page .ud-detail-row > td {
        padding: 0 !important;
        background: #fbfcfe !important;
    }

    .ud-page .ud-detail-panels {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
        padding: 12px;
    }

    .ud-page .ud-detail-panel {
        min-width: 0;
        overflow: hidden;
        border: 1px solid #e5e8ef;
        border-radius: 8px;
        background: white;
    }

    .ud-page .ud-detail-panel-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        border-bottom: 1px solid #edf0f5;
        color: #303544;
        font-size: 11px;
        font-weight: 800;
    }

    .ud-page .ud-detail-panel-body {
        max-height: 320px;
        overflow: auto;
        padding: 11px;
    }

    .ud-page .ud-json {
        margin: 0;
        color: #3e4554;
        font-family: Consolas, monospace;
        font-size: 10px;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .ud-page .ud-no-data {
        padding: 32px 15px !important;
        color: #7a8190 !important;
        text-align: center;
    }

    .ud-page .ud-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
    }

    .ud-page .ud-pagination-info {
        color: #747b8b;
        font-size: 10px;
    }

    .ud-page .ud-pagination nav {
        max-width: 100%;
        overflow-x: auto;
    }

    .ud-page .ud-pagination nav p { margin: 0; }

    .ud-page .ud-pagination nav svg {
        width: 16px;
        height: 16px;
    }

    .ud-page .ud-pagination nav a,
    .ud-page .ud-pagination nav span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        min-height: 30px;
        margin: 2px;
        padding: 5px 9px;
        border: 1px solid #e5e7ed;
        border-radius: 6px;
        color: #4b5261;
        font-size: 10px;
        text-decoration: none;
    }

    .ud-page .ud-pagination nav [aria-current="page"] span {
        border-color: #171923;
        background: #171923;
        color: white;
    }

    @media (max-width: 1150px) {
        .ud-page .ud-profile {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ud-page .ud-identity {
            border-bottom: 1px solid #eceef3;
        }

        .ud-page .ud-profile-column {
            border-left: 1px solid #eceef3;
        }

        .ud-page .ud-profile-column:nth-child(2) {
            border-left: 0;
        }
    }

    @media (max-width: 720px) {
        .ud-page { padding: 13px; }

        .ud-page .ud-profile {
            grid-template-columns: 1fr;
            padding: 12px;
        }

        .ud-page .ud-identity {
            padding: 18px 10px;
            border-bottom: 1px solid #eceef3;
        }

        .ud-page .ud-profile-column,
        .ud-page .ud-profile-column:nth-child(2) {
            padding: 17px 5px;
            border-bottom: 1px solid #eceef3;
            border-left: 0;
        }

        .ud-page .ud-profile-column:last-child {
            border-bottom: 0;
        }

        .ud-page .ud-stats {
            grid-template-columns: 1fr;
        }

        .ud-page .ud-stat { padding: 13px; }

        .ud-page .ud-history-header {
            align-items: flex-start;
        }

        .ud-page .ud-filters {
            width: 100%;
            justify-content: flex-start;
        }

        .ud-page .ud-search {
            flex: 1 1 100%;
            min-width: 0;
        }

        .ud-page .ud-detail-panels {
            grid-template-columns: 1fr;
        }

        .ud-page .ud-history,
        .ud-page .ud-activity {
            padding: 13px;
        }
    }
</style>

<div class="ud-page">

    {{-- Page heading --}}
    <header class="ud-topbar">
        <div>
            <p class="ud-eyebrow">User Management</p>
            <h1 class="ud-title">User Details</h1>
            <p class="ud-description">
                View account information and calculator usage.
            </p>
        </div>

        <a href="{{ route('admin.users.index') }}" class="ud-back">
            ← Back to Users
        </a>
    </header>

    {{-- User profile --}}
    <section class="ud-card ud-profile">

        <div class="ud-identity">
            <div class="ud-avatar">
                {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
            </div>

            <h2 class="ud-name">{{ $user->name ?: 'Unnamed User' }}</h2>

            <span class="ud-role">
                {{ ucfirst($user->role ?? 'User') }}
            </span>

            {{-- Connect this link to your actual edit route if available. --}}
            @if(\Illuminate\Support\Facades\Route::has('admin.users.edit'))
                <a href="{{ route('admin.users.edit', $user) }}" class="ud-edit">
                    ✎ &nbsp; Edit User
                </a>
            @endif
        </div>

        <div class="ud-profile-column">
            <h3 class="ud-column-heading">
                <span class="ud-column-icon">♟</span>
                Personal Information
            </h3>

            <div class="ud-info">
                <span class="ud-info-icon">♟</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Full Name</span>
                    <strong class="ud-info-value">{{ $user->name ?: '—' }}</strong>
                </div>
            </div>

            <div class="ud-info">
                <span class="ud-info-icon">✉</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Email Address</span>
                    <strong class="ud-info-value">{{ $user->email ?: '—' }}</strong>
                </div>
            </div>

            @if($hasMobile ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">☎</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Mobile Number</span>
                        <strong class="ud-info-value">{{ $user->mobile ?: '—' }}</strong>
                    </div>
                </div>
            @endif
        </div>

        <div class="ud-profile-column">
            <h3 class="ud-column-heading">
                <span class="ud-column-icon">⚙</span>
                Account Information
            </h3>

            @if($hasRole ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">♙</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Role</span>
                        <strong class="ud-info-value">
                            {{ ucfirst($user->role ?? 'User') }}
                        </strong>
                    </div>
                </div>
            @endif

            @if($hasVerified ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">✓</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Email Verification</span>
                        <span class="ud-verification {{ $user->email_verified_at ? 'ud-verified' : 'ud-unverified' }}">
                            {{ $user->email_verified_at ? 'Verified' : 'Not verified' }}
                        </span>
                    </div>
                </div>
            @endif

            <div class="ud-info">
                <span class="ud-info-icon">▦</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Account Created</span>
                    <strong class="ud-info-value">
                        {{ $user->created_at?->format('d M Y, h:i A') ?? '—' }}
                    </strong>
                </div>
            </div>

            <div class="ud-info">
                <span class="ud-info-icon">◷</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Last Login</span>
                    <strong class="ud-info-value">
                        {{ $user->last_login_at?->format('d M Y, h:i A') ?? '—' }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="ud-profile-column">
            <h3 class="ud-column-heading">
                <span class="ud-column-icon">♦</span>
                Address Information
            </h3>

            @if($hasAddress ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">⌖</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Address</span>
                        <strong class="ud-info-value">{{ $user->address ?: '—' }}</strong>
                    </div>
                </div>
            @endif

            @if($hasCity ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">⌖</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">City</span>
                        <strong class="ud-info-value">{{ $user->city ?: '—' }}</strong>
                    </div>
                </div>
            @endif

            @if($hasEmploymentType ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">▤</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Employment Type</span>
                        <strong class="ud-info-value">
                            {{ $user->employment_type ?: '—' }}
                        </strong>
                    </div>
                </div>
            @endif

            @if($hasAnnualIncome ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">₹</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Annual Income</span>
                        <strong class="ud-info-value">
                            {{ $user->annual_income !== null
                                ? '₹ ' . number_format((float) $user->annual_income)
                                : '—' }}
                        </strong>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Calculator activity statistics --}}
    <section class="ud-card ud-activity">
        <div class="ud-section-heading">
            <span class="ud-section-icon">▦</span>
            <div>
                <h3 class="ud-section-title">Calculator Activity</h3>
                <p class="ud-section-description">
                    Overview of calculator usage and results.
                </p>
            </div>
        </div>

        <div class="ud-stats">
            <div class="ud-stat ud-stat-total">
                <span class="ud-stat-icon">▦</span>
                <div>
                    <span class="ud-stat-label">Total Calculations</span>
                    <strong class="ud-stat-number">
                        {{ number_format($totalCalculations) }}
                    </strong>
                </div>
            </div>

            <div class="ud-stat ud-stat-completed">
                <span class="ud-stat-icon">✓</span>
                <div>
                    <span class="ud-stat-label">Completed Successfully</span>
                    <strong class="ud-stat-number">
                        {{ number_format($completedCalculations) }}
                    </strong>
                </div>
            </div>

            <div class="ud-stat ud-stat-failed">
                <span class="ud-stat-icon">×</span>
                <div>
                    <span class="ud-stat-label">Failed Calculations</span>
                    <strong class="ud-stat-number">
                        {{ number_format($failedCalculations) }}
                    </strong>
                </div>
            </div>
        </div>
    </section>

    {{-- Calculation history --}}
    <section class="ud-card ud-history">
        <div class="ud-history-header">
            <div class="ud-section-heading" style="margin-bottom:0">
                <span class="ud-section-icon">◷</span>
                <div>
                    <h3 class="ud-section-title">Calculation History</h3>
                    <p class="ud-section-description">
                        View the calculator activities performed by this user.
                    </p>
                </div>
            </div>

            <div class="ud-filters">
                <label class="ud-search">
                    <span class="ud-search-icon">⌕</span>
                    <input
                        type="search"
                        id="udHistorySearch"
                        placeholder="Search calculator or event type..."
                        aria-label="Search calculation history"
                    >
                </label>

                <input
                    type="date"
                    id="udHistoryDate"
                    class="ud-date-input"
                    aria-label="Filter by date"
                    title="Filter by date"
                >

                <select id="udRowsPerPage" class="ud-filter-select" aria-label="Rows per page">
                    <option value="10">10 rows</option>
                    <option value="15" selected>15 rows</option>
                    <option value="25">25 rows</option>
                    <option value="50">50 rows</option>
                </select>
            </div>
        </div>

        <div class="ud-table-wrap">
            <table class="ud-table" id="udHistoryTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Calculator</th>
                        <th>Event Type</th>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentCalculations as $usage)
                        @php
                            $status = strtolower((string) ($usage->status ?? 'unknown'));

                            $statusClass = match ($status) {
                                'completed' => 'ud-status-completed',
                                'failed' => 'ud-status-failed',
                                default => 'ud-status-other',
                            };

                            $calculatorName = $usage->calculator?->name
                                ?? 'Calculator #' . ($usage->calculator_id ?? '—');

                            $usageDate = $usage->created_at;

                            $inputData = is_array($usage->inputs)
                                ? $usage->inputs
                                : (is_object($usage->inputs)
                                    ? (array) $usage->inputs
                                    : $usage->inputs);

                            $resultData = is_array($usage->result)
                                ? $usage->result
                                : (is_object($usage->result)
                                    ? (array) $usage->result
                                    : $usage->result);
                        @endphp

                        <tr class="ud-data-row"
                            data-calculator="{{ strtolower($calculatorName) }}"
                            data-event="{{ strtolower((string) $usage->event_type) }}"
                            data-status="{{ $status }}"
                            data-date="{{ $usageDate?->format('Y-m-d') ?? '' }}">

                            <td class="ud-expand">
                                <details>
                                    <summary aria-label="Expand calculation details"></summary>
                                </details>
                            </td>

                            <td class="ud-row-id">#{{ $usage->id }}</td>

                            <td class="ud-calculator-name">
                                {{ $calculatorName }}
                            </td>

                            <td>
                                <span class="ud-event">
                                    {{ ucfirst($usage->event_type ?: '—') }}
                                </span>
                            </td>

                            <td>
                                <span class="ud-status {{ $statusClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td class="ud-date">
                                {{ $usageDate?->format('d M Y, h:i A') ?? '—' }}
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="ud-view-link ud-toggle-details"
                                >
                                    View Details →
                                </button>
                            </td>
                        </tr>

                        <tr class="ud-detail-row" hidden>
                            <td colspan="7">
                                <div class="ud-detail-panels">
                                    <div class="ud-detail-panel">
                                        <div class="ud-detail-panel-heading">
                                            ▤ &nbsp; Input Data
                                        </div>
                                        <div class="ud-detail-panel-body">
                                            <pre class="ud-json">{{ is_string($inputData)
                                                ? $inputData
                                                : (json_encode($inputData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'No input data available') }}</pre>
                                        </div>
                                    </div>

                                    <div class="ud-detail-panel">
                                        <div class="ud-detail-panel-heading">
                                            ▥ &nbsp; Result Data
                                        </div>
                                        <div class="ud-detail-panel-body">
                                            <pre class="ud-json">{{ is_string($resultData)
                                                ? $resultData
                                                : (json_encode($resultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'No result data available') }}</pre>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ud-no-data">
                                No calculator activity found for this user.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="udNoMatches" hidden>
                        <td colspan="7" class="ud-no-data">
                            No calculations match your search or date filter.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="ud-pagination">
            <span class="ud-pagination-info" id="udVisibleCount">
                Showing {{ $recentCalculations->firstItem() ?? 0 }}
                to {{ $recentCalculations->lastItem() ?? 0 }}
                of {{ $recentCalculations->total() }} results on this page
            </span>

            <div>
                {{ $recentCalculations->links() }}
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('udHistoryTable');

    if (!table) return;

    const search = document.getElementById('udHistorySearch');
    const dateFilter = document.getElementById('udHistoryDate');
    const rowsSelect = document.getElementById('udRowsPerPage');
    const noMatches = document.getElementById('udNoMatches');
    const visibleCount = document.getElementById('udVisibleCount');

    const dataRows = Array.from(table.querySelectorAll('.ud-data-row'));
    const detailRows = dataRows.map(row => row.nextElementSibling);
    let expandedIds = new Set();

    function updateHistory() {
        const query = search.value.trim().toLowerCase();
        const selectedDate = dateFilter.value;
        const limit = Number(rowsSelect.value) || 15;

        const matchingRows = dataRows.filter(row => {
            const matchesSearch =
                row.dataset.calculator.includes(query) ||
                row.dataset.event.includes(query) ||
                row.dataset.status.includes(query) ||
                row.querySelector('.ud-row-id').textContent.toLowerCase().includes(query);

            const matchesDate =
                !selectedDate || row.dataset.date === selectedDate;

            return matchesSearch && matchesDate;
        });

        const visibleRows = new Set(matchingRows.slice(0, limit));

        dataRows.forEach((row, index) => {
            const detailRow = detailRows[index];
            const isVisible = visibleRows.has(row);

            row.hidden = !isVisible;
            detailRow.hidden = !isVisible || !expandedIds.has(row);

            const details = row.querySelector('.ud-expand details');
            if (details) details.open = expandedIds.has(row) && isVisible;
        });

        noMatches.hidden = matchingRows.length !== 0;

        const displayed = Math.min(matchingRows.length, limit);

        visibleCount.textContent = matchingRows.length
            ? `Showing ${displayed} of ${matchingRows.length} matching records on this page`
            : 'No matching records on this page';
    }

    dataRows.forEach((row, index) => {
        const detailRow = detailRows[index];
        const expandDetails = row.querySelector('.ud-expand details');
        const button = row.querySelector('.ud-toggle-details');

        function toggleDetails() {
            if (expandedIds.has(row)) {
                expandedIds.delete(row);
            } else {
                expandedIds.add(row);
            }

            updateHistory();
        }

        expandDetails?.addEventListener('toggle', function () {
            if (expandDetails.open) {
                expandedIds.add(row);
            } else {
                expandedIds.delete(row);
            }

            updateHistory();
        });

        button?.addEventListener('click', toggleDetails);
    });

    search.addEventListener('input', updateHistory);
    dateFilter.addEventListener('change', updateHistory);
    rowsSelect.addEventListener('change', updateHistory);

    updateHistory();
});
</script>
@endsection
