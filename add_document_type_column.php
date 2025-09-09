<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Adding document_type column to signed_documents table...\n";

try {
    // Check if column exists
    $hasColumn = Schema::hasColumn('signed_documents', 'document_type');
    
    if (!$hasColumn) {
        Schema::table('signed_documents', function (Blueprint $table) {
            $table->string('document_type')->after('person_id');
        });
        echo "✅ Successfully added document_type column.\n";
    } else {
        echo "✅ document_type column already exists.\n";
    }
    
    // Show current table structure
    echo "\nCurrent signed_documents table structure:\n";
    $columns = DB::select("PRAGMA table_info(signed_documents)");
    foreach ($columns as $column) {
        echo "- {$column->name} ({$column->type})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}