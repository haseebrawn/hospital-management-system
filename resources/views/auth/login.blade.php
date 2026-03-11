@extends('layouts.auth')

@section('title', 'Admin Login - Hospital HMS')

@section('content')
    <div class="auth-grid">
        <div>
            <div class="auth-card">
                <div class="auth-brand">
                    <div class="auth-logo">+</div>
                    <div class="auth-brand-text">
                        <span class="auth-brand-title">Hospital HMS</span>
                        <span class="auth-brand-sub">Admin Login</span>
                    </div>
                </div>

                <!-- <div class="auth-card-title">Admin Login</div>
                <div class="auth-card-caption">Sign in to manage hospital operations.</div> -->

                <div class="alert alert-danger js-form-error" style="display: none;"></div>

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ __('There were some problems with your input.') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" data-ajax="true">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-input"
                            placeholder="admin@example.com"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-input"
                            placeholder="••••••••"
                            required
                        >
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>

                        <button type="submit" class="btn-primary">
                            Login
                        </button>
                    </div>

                    <div style="margin-top: 10px; font-size: 12px; text-align: right;">
                        <a href="#" class="link-muted">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

