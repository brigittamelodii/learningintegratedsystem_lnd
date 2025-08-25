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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('program_name');
            $table->string('program_loc');
            $table->integer('program_realization')->nullable();
            $table->enum('program_type', ['Training Activity', 'Others']);
            $table->enum('program_act_type', ['Internal', 'External']);
            $table->string('program_unit_int');
            $table->time('program_duration');
            $table->string('program_document')->nullable();  // Jika dokumen diperlukan
            $table->string('program_remarks')->nullable();
            $table->foreignId('bi_code')->constrained('category_bi')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('tp_id')->constrained('training_programs')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
