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
        Schema::create('category_budget', function (Blueprint $table) {
            $table->id();
            $table->decimal('meals_fee', 12, 2)->nullable();
            $table->decimal('hotel_fee', 12, 2)->nullable();
            $table->decimal('meeting_pkg_fee', 12, 2)->nullable();
            $table->decimal('transport_fee', 12, 2)->nullable();
            $table->decimal('bus_trip_allowance', 12, 2)->nullable();
            $table->decimal('reward_fee', 12, 2)->nullable();
            $table->decimal('material_fee', 12, 2)->nullable();
            $table->decimal('internet_fee', 12, 2)->nullable();
            $table->decimal('misc_fee', 12, 2)->nullable();
            $table->timestamps();
            $table->foreignId('program_id')->references('id')->on('programs')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_budget');
    }
};
