<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operational_disturbances', function (Blueprint $table) {
            $table->id();
            $table->date('event_date');
            $table->string('event_time', 10);
            $table->string('disturbance_type');
            $table->string('status')->default('Dalam Penanganan');
            $table->text('description')->nullable();
            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_disturbances');
    }
};
