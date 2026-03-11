<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('patients', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('contact_number');
        $table->enum('gender', ['male', 'female', 'other']);
        $table->string('address')->nullable();
        $table->unsignedBigInteger('department_id')->nullable();
        $table->timestamps();

        $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
