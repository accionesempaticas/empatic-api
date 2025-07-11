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
        Schema::table('signed_documents', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signed_documents', function (Blueprint $table) {
            $table->dropColumn('digital_signature_id');
            $table->dropColumn('signed_pdf_path');
            $table->dropColumn('signature_metadata');
        });
    }
};
