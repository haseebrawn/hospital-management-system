<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $needsNotifiableIndex = ! Schema::hasColumn('notifications', 'notifiable_type')
            || ! Schema::hasColumn('notifications', 'notifiable_id');

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->after('id');
            }

            if (! Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->after('type');
            }

            if (! Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->after('notifiable_type');
            }
        });

        if ($needsNotifiableIndex) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        }
    }

    public function down(): void
    {
        //
    }
};
