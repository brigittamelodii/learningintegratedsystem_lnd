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
    Schema::create('training_programs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade') ->onUpdate('cascade');
        $table->string('tp_name');
        $table->time('tp_duration');
        $table->integer('tp_invest');
        $table->integer('tp_realization');
        $table->string('bi_code');
        $table->foreign('bi_code')->references('bi_code')->on('category_bi')->onDelete('cascade')->onUpdate('cascade');
        $table->foreignId('pic_id')->references('id')->on('pics')->onDelete('cascade')->onUpdate('cascade');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};
