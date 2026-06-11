@extends('layouts.app')

@section('title', 'Admin — Users')

@section('content')
    <div class="card">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div class="card-title">Users</div>
                <div class="card-subtitle">Create users, assign roles, and manage department access.</div>
            </div>
            <a href="{{ route('admin.users.create') }}"
                style="padding:8px 12px; border-radius:10px; background:var(--primary); color:#fff; text-decoration:none; font-size:13px;">
                + Create User
            </a>
        </div>

        <div style="overflow:auto; margin-top:16px;">
            <table class="dash-table" style="min-width: 820px;">
                <thead>
                    <tr>
                        <th class="u-nowrap">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Roles</th>
                        <th class="u-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="u-nowrap">{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td class="u-nowrap">{{ $user->email }}</td>
                            <td>{{ optional($user->department)->name ?? '-' }}</td>
                            <td>{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                            <td class="u-right u-nowrap">
                                <form method="POST" action="{{ route('admin.users.role.assign', $user) }}"
                                    style="display:inline-flex; gap:8px; align-items:center;">
                                    @csrf
                                    @method('PUT')
                                    <select name="role"
                                        style="padding:6px 8px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                        style="padding:6px 10px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                                        Assign
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.role.remove', $user) }}"
                                    style="display:inline-flex; gap:8px; align-items:center; margin-left:10px;">
                                    @csrf
                                    @method('DELETE')
                                    <input name="role" placeholder="role (optional)"
                                        style="padding:6px 8px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; width:150px;">
                                    <button type="submit"
                                        style="padding:6px 10px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.06); color:#dc2626; cursor:pointer;">
                                        Remove
                                    </button>
                                </form>

                                @if (auth()->user()->hasRole('super_admin'))
                                    <form method="POST" action="{{ route('admin.users.department.update', $user) }}"
                                        style="display:inline-flex; gap:8px; align-items:center; margin-left:10px;">
                                        @csrf
                                        @method('PUT')
                                        <select name="department_id"
                                            style="padding:6px 8px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                                            <option value="">— None —</option>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->id }}"
                                                    {{ (string) $user->department_id === (string) $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                            style="padding:6px 10px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                                            Update Dept
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 16px;">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $users->links() }}
        </div>
    </div>
@endsection
