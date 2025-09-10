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
            // Note: area and group columns are already added by 2025_09_04_191657_add_area_and_group_to_people_table.php
            // Only add if they don't exist
            if (!Schema::hasColumn('people', 'area')) {
                $table->string('area')->nullable();
            }
            if (!Schema::hasColumn('people', 'group')) {
                $table->string('group')->nullable();
            }
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
            // Only drop if columns exist
            if (Schema::hasColumn('people', 'area')) {
                $table->dropColumn('area');
            }
            if (Schema::hasColumn('people', 'group')) {
                $table->dropColumn('group');
            }
        });
    }
};
