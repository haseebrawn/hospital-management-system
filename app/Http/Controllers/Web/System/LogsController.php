<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LoginLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function activity(Request $request)
    {
        $userId = trim((string) $request->query('user_id', ''));
        $path = trim((string) $request->query('path', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $logs = ActivityLog::query()
            ->with('user')
            ->when($userId !== '', fn ($q) => $q->where('user_id', $userId))
            ->when($path !== '', fn ($q) => $q->where('path', 'like', "%{$path}%"))
            ->when($from !== '', fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('system.logs.activity', compact('logs', 'userId', 'path', 'from', 'to'));
    }

    public function logins(Request $request)
    {
        $email = trim((string) $request->query('email', ''));
        $success = $request->query('success', '');
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $logs = LoginLog::query()
            ->with('user')
            ->when($email !== '', fn ($q) => $q->where('email', $email))
            ->when($success !== '', fn ($q) => $q->where('success', (bool) $success))
            ->when($from !== '', fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('system.logs.logins', compact('logs', 'email', 'success', 'from', 'to'));
    }
}

