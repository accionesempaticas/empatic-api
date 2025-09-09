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
        Schema::table('people', function (Blueprint $table) {
            $table->string('dni_scan_path')->nullable();
            $table->string('lab_cert_path')->nullable();
            $table->string('commitment_letter_path')->nullable();
            $table->string('photo_informal_path')->nullable();
            $table->string('photo_formal_path')->nullable();
            $table->string('cv_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn([
                'dni_scan_path',
                'lab_cert_path',
                'commitment_letter_path',
                'photo_informal_path',
                'photo_formal_path',
                'cv_path'
            ]);
        });
    }
};
