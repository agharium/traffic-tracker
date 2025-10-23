@extends('layouts.base')

@section('content')
<div class="space-y-8">
    <div class="hero bg-primary text-primary-content rounded-lg">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <h1 class="text-5xl font-bold">Traffic Tracker</h1>
                <p class="py-6">A simple, privacy-focused website analytics solution. Track unique visitors without compromising user privacy.</p>
                @if(isset($user) && $user)
                    <a href="/dashboard" class="btn btn-secondary" hx-push-url="true">View Dashboard</a>
                @else
                    <a href="/login" class="btn btn-accent" hx-push-url="true">Get Started</a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body text-center">
                <div class="text-4xl mb-4">📊</div>
                <h2 class="card-title justify-center">Simple Analytics</h2>
                <p>Track page views and unique visitors with a lightweight JavaScript snippet.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body text-center">
                <div class="text-4xl mb-4">🔒</div>
                <h2 class="card-title justify-center">Privacy First</h2>
                <p>No personal data collection. Only unique daily visits are counted and stored.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body text-center">
                <div class="text-4xl mb-4">⚡</div>
                <h2 class="card-title justify-center">Fast & Lightweight</h2>
                <p>Minimal impact on your website performance with efficient tracking.</p>
            </div>
        </div>
    </div>

    @if(!isset($user) || !$user)
    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h2 class="card-title">Ready to get started?</h2>
            <p>Create an account to access your analytics dashboard and get your tracking code.</p>
            <div class="card-actions justify-end">
                <a href="/register" class="btn btn-primary" hx-push-url="true">Sign Up Free</a>
            </div>
        </div>
    </div>
    @endif

    {{-- <!-- Demo tracking -->
    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h2 class="card-title">🧪 This page is being tracked!</h2>
            <p>This homepage includes the tracking script, so your visit is being recorded in the demo dashboard.</p>
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Refresh this page a few times, then check the dashboard to see the tracking in action!</span>
            </div>
        </div>
    </div> --}}
</div>

@endsection
