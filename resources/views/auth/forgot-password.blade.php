@extends('layouts.auth')

@section('title', 'Forgot Password - Hospital HMS')

@section('content')
<div class="auth-grid">
    <div>
        <div class="auth-card">
            <div class="auth-brand auth-brand-center">
                <div class="auth-logo">
                    <img src="{{ asset('images/HMS logo.png') }}" alt="Hospital HMS Logo" style="width: 55px; height: 55px;">
                </div>
                <div class="auth-brand-text">
                    <span class="auth-brand-title">Hospital HMS</span>
                </div>
            </div>

            <div class="alert alert-danger js-form-error" style="display: none;"></div>
            <div class="alert alert-success js-form-success" style="display: none;"></div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ __('There were some problems with your input.') }}
                </div>
            @endif

            <div class="auth-card-form">
                <div class="auth-card-title auth-card-title-center">Forgot Password</div>
                <div class="auth-card-caption" style="text-align:center;">
                    Enter your email and we’ll send you a reset link.
                </div>

                <form method="POST" action="{{ route('password.email') }}" data-ajax="true">
                    @csrf

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" class="form-input" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Send Reset Link</button>
                    </div>

                    <div style="text-align:center; margin-top: 14px; font-size: 13px; color: #6b7280;">
                        Back to
                        <a href="{{ route('login') }}" class="link-muted" style="font-weight: 500;">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
