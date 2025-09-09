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
        // Add missing fields to locations table
        Schema::table('locations', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->string('district')->nullable();
        });

        // Add missing fields to people table
        Schema::table('people', function (Blueprint $table) {
            $table->string('area')->nullable();
            $table->string('group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['country', 'district']);
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn(['area', 'group']);
        });
    }
};
