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

        // Note: area and group columns already exist in the people table
        // from the original create_people_table migration
        // No additional columns needed for people table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['country', 'district']);
        });

        // No columns to drop from people table as they were not added here
    }
};
