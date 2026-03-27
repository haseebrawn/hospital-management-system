@extends('layouts.auth')

@section('title', 'Register User - Hospital HMS')

@section('content')
<div class="auth-grid">
    <div>
        <div class="auth-card auth-card-register">
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
                <div class="auth-card-title auth-card-title-center">Register New User</div>
                <form method="POST" action="{{ route('register.post') }}" data-ajax="true">
                    @csrf

                    <div class="form-group">
                        <input type="text" name="name" placeholder="Name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <select name="department" class="form-select" required>
                            <option value="" disabled selected>Select Department</option>
                            @foreach ($departments as $department)
                            <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <input type="text" name="role" placeholder="Role" class="form-input">
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password_confirmation" placeholder="Confirm Password" class="form-input" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Register</button>
                    </div>
                    <!-- Added Login Link -->
                    <div style="text-align: center; margin-top: 15px; font-size: 13px; color: #6b7280;">
                        Already have an account?
                        <a href="{{ route('login') }}" style="color: #1e88e5; text-decoration: none; font-weight: 500;">
                            Login
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection