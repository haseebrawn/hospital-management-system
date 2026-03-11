@extends('layouts.auth')

@section('title', 'Register User - Hospital HMS')

@section('content')
    <div class="auth-grid">
        <div>
            <div class="auth-card auth-card-register">
                <div class="auth-brand auth-brand-center">
                    <div class="auth-logo">+</div>
                    <div class="auth-brand-text">
                        <span class="auth-brand-title">Hospital HMS</span>
                    </div>
                </div>

                <div class="auth-card-title auth-card-title-center">Register New User</div>

                <div class="alert alert-danger js-form-error" style="display: none;"></div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ __('There were some problems with your input.') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" data-ajax="true">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-input"
                            placeholder="Enter full name"
                            required
                        >
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-input"
                            placeholder="name@example.com"
                            required
                        >
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="department">Department</label>
                            <select
                                id="department"
                                name="department"
                                class="form-select"
                                required
                            >
                                <option value="" disabled {{ old('department') ? '' : 'selected' }}>Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>
                                        {{ $department }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="role">Role</label>
                            <input
                                id="role"
                                type="text"
                                name="role"
                                value="{{ old('role') }}"
                                class="form-input"
                                placeholder="e.g. Nurse, Doctor (optional)"
                            >
                            @error('role')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-input"
                                placeholder="Create password"
                                required
                            >
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="form-input"
                                placeholder="Confirm password"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 18px;">
                        <a href="{{ route('login') }}" class="link-muted">Back to Login</a>
                        <button type="submit" class="btn-primary">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

