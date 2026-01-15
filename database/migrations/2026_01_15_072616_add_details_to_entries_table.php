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
        Schema::table('entries', function (Blueprint $table) {
            // Menambahkan kolom baru jika belum ada
            if (!Schema::hasColumn('entries', 'weather')) {
                $table->string('weather')->nullable()->after('mood');
            }
            if (!Schema::hasColumn('entries', 'gratitude')) {
                $table->text('gratitude')->nullable()->after('negative_reflection');
            }
            if (!Schema::hasColumn('entries', 'goals')) {
                $table->text('goals')->nullable()->after('gratitude');
            }
            if (!Schema::hasColumn('entries', 'affirmations')) {
                $table->text('affirmations')->nullable()->after('goals');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn(['weather', 'gratitude', 'goals', 'affirmations']);
        });
    }
};