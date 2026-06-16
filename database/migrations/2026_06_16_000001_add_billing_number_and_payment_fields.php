<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('id');
            $table->string('payment_method')->nullable()->after('status');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('payment_method');
            $table->decimal('balance_due', 10, 2)->default(0)->after('paid_amount');
        });

        Schema::table('billing_items', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('type');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->string('source_name')->nullable()->after('source_id');
        });

        DB::table('billings')
            ->orderBy('id')
            ->get()
            ->each(function ($billing) {
                DB::table('billings')
                    ->where('id', $billing->id)
                    ->update([
                        'invoice_number' => sprintf('INV-%s-%06d', now()->format('Ymd'), $billing->id),
                        'paid_amount' => $billing->paid_amount ?? 0,
                        'balance_due' => $billing->balance_due ?? $billing->total_amount,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_id', 'source_name']);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'payment_method', 'paid_amount', 'balance_due']);
        });
    }
};
