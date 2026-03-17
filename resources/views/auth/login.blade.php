@extends('layouts.auth')

@section('title', 'Admin Login - Hospital HMS')

@section('content')
<div class="auth-grid">
    <div>
        <div class="auth-card">
            <div class="auth-brand">
                <div class="auth-logo">
                    <img src="{{ asset('images/HMS logo.png') }}" alt="Hospital HMS Logo" style="width: 55px; height: 55px;">
                </div>
                <div class="auth-brand-text">
                    <span class="auth-brand-title">Hospital HMS</span>
                </div>
            </div>

            <div class="alert alert-danger js-form-error" style="display: none;"></div>

            @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger">
                {{ __('There were some problems with your input.') }}
            </div>
            @endif

            <div class="auth-card-form">
                <div class="auth-card-title auth-card-title-center">Login to Your Account</div>
                <form method="POST" action="{{ route('login.post') }}" data-ajax="true">
                    @csrf

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" class="form-input" required>
                    </div>

                    <!-- ✅ Remember + Forgot Password -->
                    <div class="form-group remember-me" style="display: flex; justify-content: space-between; align-items: center;">
                        <label>
                            <input type="checkbox" name="remember"> Remember me
                        </label>

                        <a href="#" style="font-size: 12px; color: #1e88e5; text-decoration: none;">
                            Forgot Password?
                        </a>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Login</button>
                    </div>

                    <!-- ✅ Sign Up Link -->
                    <div style="text-align: center; margin-top: 15px; font-size: 13px; color: #6b7280;">
                        Create a new account?
                        <a href="{{ route('register') }}" style="color: #1e88e5; text-decoration: none; font-weight: 500;">
                            Sign Up
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection