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
         Schema::create('participants_temp', function (Blueprint $table) {
            $table->id('participants_id');
            $table->string('karyawan_nik');
            $table->string('participants_name');
            $table->string('position');
            $table->string('working_unit');
            $table->string('class_name');
            $table->unsignedBigInteger('class_id');
            $table->integer('pre_test')->nullable();
            $table->integer('post_test')->nullable();
            $table->string('attendance_remarks')->nullable();
            $table->enum('status', ['Present', 'Absent', 'Absent - Sick', 'Absent - Maternity',
                                                      'Absent - Business', 'Invited', 'Uninvited'
                                                    ])->default('Invited');
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants_temp');
    }
};
