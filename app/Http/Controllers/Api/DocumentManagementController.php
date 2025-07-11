<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentManagementController extends Controller
{
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240', // Max 10MB
        ]);

        $file = $request->file('pdf_file');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('templates', $fileName, 'public'); // Store in storage/app/public/templates

        return response()->json([
            'message' => 'Archivo PDF subido exitosamente.',
            'file_path' => $filePath,
            'url' => Storage::disk('public')->url($filePath),
        ], 201);
    }

    public function createDocumentTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file_path' => 'required|string', // Just validate it's a string
            'content' => 'nullable|string', // Optional content for text-based templates
        ]);

        $documentTemplate = DocumentTemplate::create([
            'name' => $request->name,
            'content' => $request->content,
            'file_path' => $request->file_path,
        ]);

        return response()->json([
            'message' => 'Plantilla de documento creada exitosamente.',
            'document_template' => $documentTemplate,
        ], 201);
    }
}
