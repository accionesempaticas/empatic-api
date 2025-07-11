<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\SignedDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DocumentSigningController extends Controller
{
    public function getPendingDocuments(Request $request)
    {
        $user = $request->user();
        $signedTemplateIds = $user->signedDocuments()->pluck('document_template_id');

        $pendingDocuments = DocumentTemplate::whereNotIn('id', $signedTemplateIds)->get();

        return response()->json($pendingDocuments);
    }

    public function getSignedDocuments(Request $request)
    {
        $signedDocuments = $request->user()->signedDocuments()->with('documentTemplate')->get();

        // Map to add the full URL for signed_pdf_path
        $signedDocuments->map(function ($doc) {
            if ($doc->signed_pdf_path) {
                $doc->signed_pdf_url = Storage::disk('public')->url($doc->signed_pdf_path);
            }
            return $doc;
        });

        return response()->json($signedDocuments);
    }

    public function signDocument(Request $request, DocumentTemplate $template)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify user's password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'La contraseña es incorrecta.'], 401);
        }

        // Check if the user has already signed this document
        if ($user->signedDocuments()->where('document_template_id', $template->id)->exists()) {
            return response()->json(['message' => 'Ya has firmado este documento.'], 409);
        }

        // Create the signed document record
        $signedDocument = SignedDocument::create([
            'user_id' => $user->id,
            'document_template_id' => $template->id,
            'signed_at' => now(),
            'signature_data' => $user->name, // Storing the user's name as the signature
            'ip_address' => $request->ip(),
        ]);

        // --- Simulated Digital Signature Logic ---
        try {
            // 1. Load the original PDF
            if (!$template->file_path || !Storage::disk('public')->exists($template->file_path)) {
                return response()->json(['message' => 'El archivo PDF del documento no existe.'], 404);
            }
            $originalPdfPath = Storage::disk('public')->path($template->file_path);

            // 2. Calculate hash of the original PDF
            $originalPdfContent = file_get_contents($originalPdfPath);
            $documentHash = hash('sha256', $originalPdfContent);

            // 3. Prepare FPDI/FPDF for visual stamping
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($originalPdfPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);

                // Add signature text (example position, adjust as needed)
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetTextColor(0, 0, 0); // Black color
                $pdf->SetXY(10, 280); // Example position (x, y) - adjust based on your PDF layout
                $pdf->Write(0, 'Firmado por: ' . $user->name);
                $pdf->SetXY(10, 285); // Example position
                $pdf->Write(0, 'Fecha: ' . now()->format('Y-m-d H:i:s'));
                $pdf->SetXY(10, 290); // Example position
                $pdf->Write(0, 'Firmado conforme');
            }

            // 4. Save the signed PDF
            $signedPdfFileName = 'signed_' . $user->id . '_' . $template->id . '_' . time() . '.pdf';
            $signedPdfPath = 'signed_documents/' . $signedPdfFileName;
            Storage::disk('public')->put($signedPdfPath, $pdf->Output('S')); // 'S' returns the PDF as a string

            // 5. Update SignedDocument record
            $signedDocument->update([
                'signed_pdf_path' => $signedPdfPath,
                'signature_metadata' => json_encode([
                    'document_hash' => $documentHash,
                    'signed_by_name' => $user->name,
                    'signed_at_timestamp' => now()->timestamp,
                ]),
            ]);

        } catch (PdfParserException | PdfReaderException $e) {
            return response()->json(['message' => 'Error al procesar el PDF: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error inesperado al firmar el documento: ' . $e->getMessage()], 500);
        }
        // --- End Simulated Digital Signature Logic ---

        return response()->json([
            'message' => 'Documento firmado exitosamente.',
            'document' => $signedDocument,
            'signed_pdf_url' => Storage::disk('public')->url($signedPdfPath) // Return URL for frontend
        ], 201);
    }

    // New method to serve PDF files
    public function serveDocument(DocumentTemplate $template)
    {
        if (!$template->file_path || !Storage::disk('public')->exists($template->file_path)) {
            return response()->json(['message' => 'Documento no encontrado.'], 404);
        }

        $path = Storage::disk('public')->path($template->file_path);
        return response()->file($path);
    }
}
