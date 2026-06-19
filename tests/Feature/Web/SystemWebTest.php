<?php

namespace Tests\Feature\Web;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Department;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemWebTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);

        $dept = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $dept->id]);
        $user->assignRole('super_admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_backups_page_loads(): void
    {
        $user = $this->actingAsAdmin();

        Backup::create([
            'filename' => 'backup_test.sql',
            'created_by' => $user->id,
            'storage_path' => 'backups/backup_test.sql',
            'filesize' => 123,
            'notes' => 'test',
        ]);

        $this->get('/system/backups')->assertOk()->assertSee('Backups');
    }

    public function test_backup_download_works_when_file_exists(): void
    {
        $user = $this->actingAsAdmin();

        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $filePath = $dir . '/backup_test_download.sql';
        file_put_contents($filePath, 'test');

        $backup = Backup::create([
            'filename' => 'backup_test_download.sql',
            'created_by' => $user->id,
            'storage_path' => 'backups/backup_test_download.sql',
            'filesize' => 4,
            'notes' => 'test',
        ]);

        $this->get("/system/backups/{$backup->id}/download")->assertOk();
    }

    public function test_backup_command_creates_backup_file_and_record(): void
    {
        $user = $this->actingAsAdmin();

        $code = Artisan::call('db:backup', [
            '--notes' => 'system test backup',
            '--retain' => 14,
        ]);

        $this->assertSame(0, $code);

        $backup = Backup::query()->latest('id')->first();
        $this->assertNotNull($backup);
        $this->assertStringStartsWith('backup_', $backup->filename);

        $path = storage_path('app/' . $backup->storage_path);
        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));
    }

    public function test_activity_logs_page_loads(): void
    {
        $user = $this->actingAsAdmin();

        ActivityLog::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'method' => 'GET',
            'path' => '/test',
            'request_payload' => '{}',
            'response_summary' => '{}',
            'user_agent' => 'phpunit',
        ]);

        $this->get('/system/logs/activity')->assertOk()->assertSee('Activity Logs');
    }

    public function test_login_logs_page_loads(): void
    {
        $user = $this->actingAsAdmin();

        LoginLog::create([
            'email' => $user->email,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'success' => true,
            'notes' => 'ok',
            'user_agent' => 'phpunit',
        ]);

        $this->get('/system/logs/logins')->assertOk()->assertSee('Login Logs');
    }
}
