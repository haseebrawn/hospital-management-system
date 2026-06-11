<?php

use App\Models\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('mrn')->nullable()->unique()->after('id');
        });

        Patient::query()
            ->whereNull('mrn')
            ->orderBy('id')
            ->get()
            ->each(function (Patient $patient) {
                $patient->forceFill([
                    'mrn' => Patient::generateMrn($patient->id),
                ])->saveQuietly();
            });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['mrn']);
            $table->dropColumn('mrn');
        });
    }
};
