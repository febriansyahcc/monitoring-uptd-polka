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
        Schema::create('current_log_records', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date');
            $table->enum('shift', ['pagi', 'sore', 'malam']);
            $table->string('time_interval', 10);
            $table->foreignId('feeder_id')->constrained('feeders')->onDelete('cascade');
            $table->decimal('current_value', 8, 2)->nullable();
            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();

            $table->unique(['recorded_date', 'shift', 'time_interval', 'feeder_id'], 'unique_current_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_log_records');
    }
};
