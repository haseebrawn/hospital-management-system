<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BackupsController extends Controller
{
    public function index()
    {
        $backups = Backup::query()
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('system.backups.index', compact('backups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Artisan::call('db:backup', ['--notes' => $request->input('notes')]);

        return back()->with('status', 'Backup command executed.');
    }

    public function download(Backup $backup)
    {
        $basePath = realpath(storage_path('app'));
        $path = realpath(storage_path('app/' . ltrim($backup->storage_path, '/')));

        if (! $path || ! $basePath || ! str_starts_with($path, $basePath) || ! file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path, $backup->filename);
    }
}
