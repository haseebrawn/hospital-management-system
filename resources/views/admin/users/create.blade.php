@extends('layouts.app')

@section('title', 'Create User')

@section('content')
    <div class="card">
        <div class="card-title">Create User</div>
        <div class="card-subtitle">Create doctors, department staff, and admin-controlled login accounts.</div>

        <form method="POST" action="{{ route('admin.users.store') }}" style="margin-top:16px;">
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Name</label>
                    <input name="name" value="{{ old('name') }}" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Password</label>
                    <input type="password" name="password" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Department</label>
                    <select name="department_id" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>Select department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ (string) old('department_id') === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Role</label>
                    <select name="role" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', ucfirst($role->name)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="grid-column:1 / -1; padding:12px; border:1px solid var(--border-color); border-radius:14px; background:rgba(37,99,235,0.04);">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                        <input type="checkbox" name="create_staff_profile" value="1" {{ old('create_staff_profile') ? 'checked' : '' }}>
                        Create staff profile also
                    </label>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                        Use this for doctors and hospital staff who need HR/staff records.
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Designation</label>
                    <input name="designation" value="{{ old('designation') }}" placeholder="e.g. Consultant Doctor"
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Salary</label>
                    <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', 0) }}"
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date') }}"
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Employment Status</label>
                    <select name="employment_status"
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" {{ old('employment_status', 'active') === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                <a href="{{ route('admin.users.index') }}"
                    style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Cancel
                </a>
                <button type="submit"
                    style="padding:10px 14px; border-radius:12px; border:none; background:var(--primary); color:#fff; cursor:pointer; font-size:13px;">
                    Create User
                </button>
            </div>
        </form>
    </div>
@endsection
