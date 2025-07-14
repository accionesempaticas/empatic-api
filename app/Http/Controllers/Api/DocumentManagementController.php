<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentManagementController extends Controller
{
    public function downloadFile(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        $filePath = $request->input('file_path');

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['message' => 'Archivo no encontrado.'], 404);
        }

        return Storage::disk('local')->download($filePath);
    }
}
