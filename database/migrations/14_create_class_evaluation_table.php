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
        Schema::create('class_evaluation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->references('id')->on('classes')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('evaluasi_materi');
            $table->integer('evaluasi_pengajar');
            $table->integer('evaluasi_panitia');
            $table->foreignId('participant_id')->references('id')->on('participants')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('eval_id')->references('id')->on('evaluation')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_class');
    }
};
