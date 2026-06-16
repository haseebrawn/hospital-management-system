<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('ALTER TABLE billing_payments RENAME TO billing_payments_old');
            DB::statement('ALTER TABLE billing_items RENAME TO billing_items_old');
            DB::statement('ALTER TABLE billings RENAME TO billings_old');
            DB::statement('DROP INDEX IF EXISTS billings_invoice_number_unique');

            Schema::create('billings', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->nullable()->unique();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
                $table->string('payment_method')->nullable();
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('balance_due', 10, 2)->default(0);
                $table->timestamps();

                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });

            DB::statement('
                INSERT INTO billings (
                    id, invoice_number, patient_id, created_by, approved_by,
                    total_amount, status, payment_method, paid_amount, balance_due,
                    created_at, updated_at
                )
                SELECT
                    id, invoice_number, patient_id, created_by, approved_by,
                    total_amount, status, payment_method, paid_amount, balance_due,
                    created_at, updated_at
                FROM billings_old
            ');

            Schema::create('billing_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('billing_id');
                $table->string('service_name');
                $table->integer('quantity')->default(1);
                $table->decimal('price', 10, 2);
                $table->enum('type', ['lab', 'medicine', 'appointment', 'other'])->default('other');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('source_name')->nullable();
                $table->timestamps();

                $table->foreign('billing_id')->references('id')->on('billings')->onDelete('cascade');
            });

            DB::statement('
                INSERT INTO billing_items (
                    id, billing_id, service_name, quantity, price, type,
                    source_type, source_id, source_name, created_at, updated_at
                )
                SELECT
                    id, billing_id, service_name, quantity, price, type,
                    source_type, source_id, source_name, created_at, updated_at
                FROM billing_items_old
            ');

            Schema::create('billing_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('billing_id');
                $table->unsignedBigInteger('received_by')->nullable();
                $table->decimal('amount', 10, 2);
                $table->string('payment_method');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('billing_id')->references('id')->on('billings')->onDelete('cascade');
                $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            });

            DB::statement('
                INSERT INTO billing_payments (
                    id, billing_id, received_by, amount, payment_method,
                    reference, notes, created_at, updated_at
                )
                SELECT
                    id, billing_id, received_by, amount, payment_method,
                    reference, notes, created_at, updated_at
                FROM billing_payments_old
            ');

            DB::statement('DROP TABLE billing_payments_old');
            DB::statement('DROP TABLE billing_items_old');
            DB::statement('DROP TABLE billings_old');
            DB::statement('PRAGMA foreign_keys=ON');

            return;
        }

        DB::statement("ALTER TABLE billings MODIFY status ENUM('pending', 'partial', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('ALTER TABLE billing_payments RENAME TO billing_payments_new');
            DB::statement('ALTER TABLE billing_items RENAME TO billing_items_new');
            DB::statement('ALTER TABLE billings RENAME TO billings_new');
            DB::statement('DROP INDEX IF EXISTS billings_invoice_number_unique');

            Schema::create('billings', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->nullable()->unique();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
                $table->string('payment_method')->nullable();
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('balance_due', 10, 2)->default(0);
                $table->timestamps();

                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });

            DB::statement('
                INSERT INTO billings (
                    id, invoice_number, patient_id, created_by, approved_by,
                    total_amount, status, payment_method, paid_amount, balance_due,
                    created_at, updated_at
                )
                SELECT
                    id, invoice_number, patient_id, created_by, approved_by,
                    total_amount,
                    CASE
                        WHEN status = "partial" THEN "pending"
                        ELSE status
                    END,
                    payment_method, paid_amount, balance_due,
                    created_at, updated_at
                FROM billings_new
            ');

            Schema::create('billing_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('billing_id');
                $table->string('service_name');
                $table->integer('quantity')->default(1);
                $table->decimal('price', 10, 2);
                $table->enum('type', ['lab', 'medicine', 'appointment', 'other'])->default('other');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('source_name')->nullable();
                $table->timestamps();

                $table->foreign('billing_id')->references('id')->on('billings')->onDelete('cascade');
            });

            DB::statement('
                INSERT INTO billing_items (
                    id, billing_id, service_name, quantity, price, type,
                    source_type, source_id, source_name, created_at, updated_at
                )
                SELECT
                    id, billing_id, service_name, quantity, price, type,
                    source_type, source_id, source_name, created_at, updated_at
                FROM billing_items_new
            ');

            Schema::create('billing_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('billing_id');
                $table->unsignedBigInteger('received_by')->nullable();
                $table->decimal('amount', 10, 2);
                $table->string('payment_method');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('billing_id')->references('id')->on('billings')->onDelete('cascade');
                $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            });

            DB::statement('
                INSERT INTO billing_payments (
                    id, billing_id, received_by, amount, payment_method,
                    reference, notes, created_at, updated_at
                )
                SELECT
                    id, billing_id, received_by, amount, payment_method,
                    reference, notes, created_at, updated_at
                FROM billing_payments_new
            ');

            DB::statement('DROP TABLE billing_payments_new');
            DB::statement('DROP TABLE billing_items_new');
            DB::statement('DROP TABLE billings_new');
            DB::statement('PRAGMA foreign_keys=ON');

            return;
        }

        DB::statement("ALTER TABLE billings MODIFY status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
