<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
    */

    // database/migrations/xxxx_xx_xx_create_agenda_table.php
public function up()
{
    Schema::create('agenda', function (Blueprint $table) {
        $table->id();
        $table->foreignId('class_id')->constrained()->onDelete('cascade');
        $table->string('materi_name');
        $table->integer('materi_duration');
        $table->string('file_path')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda');
    }
};
