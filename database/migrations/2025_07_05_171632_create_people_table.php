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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 10)->nullable();
            $table->string('document_number', 20)->nullable();
            $table->unique(['document_type', 'document_number']);
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('full_name', 100)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable();
            $table->string('nationality', 30)->nullable();
            $table->string('family_phone_number', 15)->nullable();
            $table->string('linkedin', 70)->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('formation_id')->nullable()->constrained('academic_formations');
            $table->foreignId('experience_id')->nullable()->constrained('experiences');
            $table->string('area')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
