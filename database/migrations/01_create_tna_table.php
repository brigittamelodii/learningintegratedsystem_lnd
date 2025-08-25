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
        Schema::create('tna', function (Blueprint $table) {
            $table->id();
            $table->binary('tna_document');
            $table->year('tna_year');
            $table->decimal('tna_min_budget', 12, 2)->nullable();
            $table->string('tna_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tna');
    }
};
