@extends('loanengine-auth::layouts.guest')
@section('title', 'Admin dashboard')
@section('kicker', 'ADMIN WORKSPACE')
@section('heading', 'Welcome, ' . auth()->user()->name)
@section('description', 'You are signed in with administrator access.')
@section('content')
<div class="role-card"><span class="role-label">CURRENT ROLE</span><span class="role-value">Administrator</span><p>This page is restricted to users with the <code>admin</code> role.</p></div>
<form method="POST" action="{{ route('auth.logout') }}">@csrf<button class="btn-primary" type="submit">Sign out →</button></form>
@endsection
