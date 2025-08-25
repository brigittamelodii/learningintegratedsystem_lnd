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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
        
            // Relasi ke category_budget
            $table->foreignId('category_budget_id')
                  ->constrained('category_budget')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        
            // Salinan data biaya
            $table->decimal('meals_fee', 12, 2)->nullable();
            $table->decimal('hotel_fee', 12, 2)->nullable();
            $table->decimal('meeting_pkg_fee', 12, 2)->nullable();
            $table->decimal('transport_fee', 12, 2)->nullable();
            $table->decimal('bus_trip_allowance', 12, 2)->nullable();
            $table->decimal('reward_fee', 12, 2)->nullable();
            $table->decimal('material_fee', 12, 2)->nullable();
            $table->decimal('internet_fee', 12, 2)->nullable();
            $table->decimal('misc_fee', 12, 2)->nullable();
            $table->decimal('total_amount', 14, 2)->nullable();
            $table->string('account_no');
            $table->string('account_name');
            $table->decimal('ppn_fee', 12, 2)->nullable();
            $table->decimal('pph_fee', 12, 2)->nullable();

            // Relasi ke user yang menyetujui
            $table->foreignId('approved_by_pics_id')
                  ->nullable()
                  ->constrained('pics')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            $table->foreignId('participant_id')->references('id')->on('participants')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('program_id')->references('id')->on('programs')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
    
        });
        
    }

    /**
     * $table->foreignId('participant_id')->references('id')->on('participants')->onDelete('cascade')->onUpdate('cascade');
    * $table->foreignId('budget_id')->references('id')->on('category_budget')->onDelete('cascade')->onUpdate('cascade');
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
