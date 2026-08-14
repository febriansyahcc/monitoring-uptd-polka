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
        Schema::create('fuel_stocks', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date')->unique();
            $table->decimal('daily_consumption', 12, 2)->default(0);
            $table->decimal('main_tank', 12, 2)->default(0);
            $table->decimal('total_gross', 12, 2)->default(0);
            $table->decimal('death_stock', 12, 2)->default(0);
            $table->decimal('unloading', 12, 2)->default(0);
            $table->decimal('netto_stock', 12, 2)->default(0);
            $table->decimal('estimated_daily_consumption', 12, 2)->default(0);
            $table->decimal('days_of_supply', 8, 2)->default(0);
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
        Schema::dropIfExists('fuel_stocks');
    }
};
