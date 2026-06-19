<?php
namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup {--notes=} {--retain=14}';
    protected $description = 'Create a DB dump and save to storage/app/backups';

    public function handle()
    {
        $this->info('Starting DB backup...');

        $driver = config('database.default');
        $filename = 'backup_' . now()->format('Ymd_His') . '.sql';
        $path = storage_path('app/backups/'.$filename);
        File::ensureDirectoryExists(dirname($path));

        if ($driver === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database');

            if ($databasePath === ':memory:' || ! is_file($databasePath)) {
                file_put_contents($path, $this->dumpSqliteDatabase());
            } else {
                if (! copy($databasePath, $path)) {
                    $this->error('Failed to copy SQLite database file.');
                    return self::FAILURE;
                }
            }
        } else {
            $dbHost = config('database.connections.mysql.host');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbName = config('database.connections.mysql.database');

            $cmd = "mysqldump -h {$dbHost} -u {$dbUser} -p'{$dbPass}' {$dbName} > " . escapeshellarg($path);

            $returnVar = null;
            system($cmd, $returnVar);

            if ($returnVar !== 0) {
                $this->error('Backup command failed. Ensure mysqldump available and credentials correct.');
                return self::FAILURE;
            }
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

        $retainDays = max(1, (int) $this->option('retain'));
        Backup::query()
            ->where('created_at', '<', now()->subDays($retainDays))
            ->each(function (Backup $backup) {
                $oldPath = storage_path('app/' . $backup->storage_path);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }

                $backup->delete();
            });

        $this->info("Backup saved to storage/app/backups/{$filename} ({$size} bytes)");

        return self::SUCCESS;
    }

    private function dumpSqliteDatabase(): string
    {
        $pdo = DB::connection()->getPdo();
        $lines = [
            '-- HMS SQLite backup',
            '-- Generated: ' . now()->toDateTimeString(),
            '',
        ];

        $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

        foreach ($tables as $table) {
            $tableName = $table->name;
            $createStatement = $table->sql;

            if ($createStatement) {
                $lines[] = $createStatement . ';';
                $lines[] = '';
            }

            $rows = DB::table($tableName)->get();

            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $columns = array_keys($rowArray);
                $values = array_map(function ($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    if (is_bool($value)) {
                        return $value ? '1' : '0';
                    }

                    return $pdo->quote((string) $value);
                }, array_values($rowArray));

                $lines[] = sprintf(
                    'INSERT INTO "%s" ("%s") VALUES (%s);',
                    $tableName,
                    implode('", "', $columns),
                    implode(', ', $values)
                );
            }

            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }
}
