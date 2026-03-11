<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\LoginLog;

class AuditController extends Controller
{
    public function activity(Request $request)
    {
        $q = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->query('user_id')) $q->where('user_id', $request->query('user_id'));
        if ($request->query('path')) $q->where('path','like','%'.$request->query('path').'%');
        if ($request->query('from')) $q->whereDate('created_at','>=',$request->query('from'));
        if ($request->query('to'))   $q->whereDate('created_at','<=',$request->query('to'));

        return $q->paginate(25);
    }

    public function logins(Request $request)
    {
        $q = LoginLog::with('user')->orderByDesc('created_at');

        if ($request->query('email')) $q->where('email',$request->query('email'));
        if ($request->query('success') !== null) $q->where('success', (bool)$request->query('success'));
        if ($request->query('from')) $q->whereDate('created_at','>=',$request->query('from'));
        if ($request->query('to'))   $q->whereDate('created_at','<=',$request->query('to'));

        return $q->paginate(25);
    }
}
