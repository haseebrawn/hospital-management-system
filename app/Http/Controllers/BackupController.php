<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Models\Backup;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index() {
        return Backup::with('creator')->orderByDesc('created_at')->paginate(15);
    }

    public function download($id) {
        $b = Backup::findOrFail($id);
        $path = storage_path('app/'.$b->storage_path);
        if (!file_exists($path)) return response()->json(['message'=>'File not found'],404);
        return response()->download($path, $b->filename);
    }

    public function create(Request $request) {
        // optionally limit to manage backups permission
        // call the same underlying system command (or queue)
        Artisan::call('db:backup', ['--notes' => $request->input('notes')]);
        return response()->json(['message'=>'Backup queued/created']);
    }
}
