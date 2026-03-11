<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backup;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup {--notes=}';
    protected $description = 'Create a DB dump and save to storage/app/backups';

    public function handle()
    {
        $this->info('Starting DB backup...');

        $filename = 'backup_' . now()->format('Ymd_His') . '.sql';
        $path = storage_path('app/backups/'.$filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);

        // MySQL example — ensure mysqldump is available and env vars are correct
        $dbHost = config('database.connections.mysql.host');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbName = config('database.connections.mysql.database');

        // Escape passwords/spaces
        $cmd = "mysqldump -h {$dbHost} -u {$dbUser} -p'{$dbPass}' {$dbName} > " . escapeshellarg($path);

        $returnVar = null;
        system($cmd, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Backup command failed. Ensure mysqldump available and credentials correct.');
            return 1;
        }

        $size = filesize($path);

        // Save meta in DB
        Backup::create([
            'filename' => $filename,
            'created_by' => auth()->id() ?? null,
            'storage_path' => 'backups/'.$filename,
            'filesize' => $size,
            'notes' => $this->option('notes'),
        ]);

        $this->info("Backup saved to storage/app/backups/{$filename} ({$size} bytes)");

        return 0;
    }
}
