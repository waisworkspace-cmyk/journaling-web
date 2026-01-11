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
    Schema::create('entries', function (Blueprint $table) {
        $table->id();
        $table->date('entry_date');
        $table->string('mood')->nullable(); // happy, sad, neutral, etc
        $table->string('weather')->nullable(); // sunny, rainy, etc
        $table->integer('rating')->default(5); // 1-10
        $table->text('positive_highlight')->nullable();
        $table->text('negative_reflection')->nullable();
        $table->json('photo_paths')->nullable(); // Untuk menyimpan path foto
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
