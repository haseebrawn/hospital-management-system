<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->unsignedInteger('reorder_level')->default(10)->after('stock');
            $table->date('expiry_alert_date')->nullable()->after('expiry_date');
            $table->boolean('expiry_alert_sent')->default(false)->after('expiry_alert_date');
            $table->boolean('reorder_alert_sent')->default(false)->after('expiry_alert_sent');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn([
                'reorder_level',
                'expiry_alert_date',
                'expiry_alert_sent',
                'reorder_alert_sent',
            ]);
        });
    }
};
