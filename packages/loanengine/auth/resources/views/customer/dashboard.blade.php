@extends('loanengine-auth::layouts.guest')
@section('title', 'Customer dashboard')
@section('kicker', 'YOUR WORKSPACE')
@section('heading', 'Welcome, ' . auth()->user()->name)
@section('description', 'You are signed in to your LoanEngine customer account.')
@section('content')
<div class="role-card"><span class="role-label">CURRENT ROLE</span><span class="role-value">Customer</span><p>This page is restricted to users with the <code>customer</code> role.</p></div>
<form method="POST" action="{{ route('auth.logout') }}">@csrf<button class="btn-primary" type="submit">Sign out →</button></form>
@endsection
