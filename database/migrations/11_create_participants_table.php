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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('class_id')->references('id')->on('classes')->onDelete('cascade')->onUpdate('cascade');
            $table->string('participant_name');
            $table->string('karyawan_nik')->unique();
            $table->integer('pre_test')->nullable();
            $table->integer('post_test')->nullable();
            $table->enum('status', ['Present','Absent','Absent-Busy','Absent-Sick','Absent-Maternity','Absent-Business'])->default('Invited');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
