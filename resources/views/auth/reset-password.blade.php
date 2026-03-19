@extends('layouts.auth')

@section('title', 'Reset Password - Hospital HMS')

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

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ __('There were some problems with your input.') }}
                </div>
            @endif

            <div class="auth-card-form">
                <div class="auth-card-title auth-card-title-center">Reset Password</div>
                <div class="auth-card-caption" style="text-align:center;">
                    Set your new password below.
                </div>

                <form method="POST" action="{{ route('password.update') }}" data-ajax="true">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" class="form-input"
                            value="{{ old('email', $email) }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="New Password" class="form-input" required>
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input type="password" name="password_confirmation" placeholder="Confirm New Password" class="form-input" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Update Password</button>
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
