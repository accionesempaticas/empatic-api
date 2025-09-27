<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\SignedDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;

class DocumentSignController extends Controller
{
    public function signDocument(Request $request)
    {
        \Log::info('=== INICIO DocumentSignController ===');
        \Log::info('DocumentSignController - Datos recibidos:', $request->all());
        \Log::info('DocumentSignController - Archivos recibidos:', $request->allFiles());
        
        try {
            \Log::info('=== PASO 1: Validación ===');
            $request->validate([
                'signature' => 'required|string',
                'user_id' => 'nullable|exists:people,id'
            ]);
            \Log::info('✅ Validación exitosa');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        try {
            \Log::info('=== PASO 2: Búsqueda de usuario ===');
            $userId = $request->user_id;
            \Log::info('User ID recibido:', ['user_id' => $userId]);
            
            $person = \App\Models\Person::with(['location', 'formation', 'experience'])->find($userId);
            
            if (!$person) {
                \Log::error('❌ Usuario no encontrado');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                        ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            }
            
            \Log::info('DocumentSignController - Datos de la persona:', $person->toArray());
            
            
            
            \Log::info('✅ Usuario encontrado:', ['id' => $person->id, 'name' => $person->first_name]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Error en búsqueda de usuario:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar usuario: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        try {
            \Log::info('=== PASO 3: Crear directorio ===');
            $userDir = 'privates/' . $person->id;
            \Log::info('Creando directorio:', ['dir' => $userDir]);
            \Storage::makeDirectory($userDir);
            \Log::info('✅ Directorio creado');
            
        } catch (\Exception $e) {
            \Log::error('❌ Error creando directorio:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear directorio: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        try {
            \Log::info('=== PASO 4: Generar contenido ===');
            $filename = 'commitment_letter_signed_' . $person->id . '_' . time() . '.pdf'; // Change extension to .pdf
            $filePath = $userDir . '/' . $filename;
            \Log::info('Archivo a crear:', ['path' => $filePath]);
            
            \Log::info('Generando contenido HTML...');
            $documentContent = $this->generateSignedDocumentHTML($person, $request->signature);
            \Log::info('✅ Contenido HTML generado, longitud:', ['length' => strlen($documentContent)]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Error generando contenido:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al generar contenido: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        try {
            \Log::info('=== PASO 5: Guardar archivo PDF ===');
            \Log::info('Guardando archivo PDF en storage...');
            $pdf = \PDF::loadHTML($documentContent);
            \Storage::put($filePath, $pdf->output());
            \Log::info('✅ Archivo PDF guardado exitosamente');
            
        } catch (\Exception $e) {
            \Log::error('❌ Error guardando archivo PDF:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar archivo PDF: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        try {
            \Log::info('=== PASO 6: Crear registro en BD ===');
            $signedDocument = SignedDocument::create([
                'person_id' => $person->id,
                'document_type' => $request->document_type ?? 'commitment_letter',
                'file_path' => $filePath,
                'signed_at' => now(),
                'signature_data' => $request->signature
            ]);
            \Log::info('✅ Registro creado en BD:', ['id' => $signedDocument->id]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Error creando registro en BD:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear registro: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        try {
            \Log::info('=== PASO 7: Actualizar registro de persona ===');
            $person->commitment_letter_path = $filePath;
            $person->save();
            \Log::info('✅ Campo commitment_letter_path actualizado en people table');
            
        } catch (\Exception $e) {
            \Log::error('❌ Error actualizando people table:', ['error' => $e->getMessage()]);
        }

        try {
            \Log::info('=== PASO 8: Preparar respuesta ===');
            $downloadUrl = url('storage/' . $filePath);
            \Log::info('URL de descarga:', ['url' => $downloadUrl]);
            
            \Log::info('✅ PROCESO COMPLETADO EXITOSAMENTE');
            $response = [
                'success' => true,
                'message' => 'Documento firmado exitosamente',
                'signed_document_id' => $signedDocument->id,
                'person_name' => $person->first_name . ' ' . $person->last_name,
                'signed_document_url' => $downloadUrl
            ];
            
            \Log::info('Respuesta a enviar:', $response);
            
            return response()->json($response)
                ->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            
        } catch (\Exception $e) {
            \Log::error('❌ Error en respuesta final:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error en respuesta: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
    }
    
    private function createSignedPdf($person, $signatureImagePath)
    {
        // Crear el contenido del PDF con la firma
        $html = $this->generateCommitmentLetterHTML($person, $signatureImagePath);
        
        // Guardar el HTML como documento firmado (simulando PDF)
        $filename = 'commitment_letter_signed_' . $person->id . '_' . time() . '.html';
        $filePath = 'privates/' . $person->id . '/' . $filename;
        
        // Asegurar que el directorio existe
        Storage::makeDirectory('privates/' . $person->id);
        
        Storage::put($filePath, $html);
        
        return $filePath;
    }
    
    private function generateSignedDocumentHTML($person, $signatureBase64)
    {
        // Usar datos reales de la base de datos igual que DocumentController
        $area = $person->area ?? 'A1. Coordinación Nacional';
        $template = $this->getTemplateContent($area);
        
        // Generar el HTML base igual que el DocumentController
        $htmlContent = $this->generateHtmlFromPdfTemplate($person, $template);
        
        // Agregar la firma al HTML
        $signatureData = $signatureBase64;
        
        // Insertar la firma en el HTML en lugar del espacio de firma vacío
        $htmlContent = str_replace(
            '<span class="signature-line"></span>',
            '<img src="' . $signatureData . '" style="max-width: 200px; max-height: 80px;" alt="Firma" />',
            $htmlContent
        );
        
        return $htmlContent;
    }
    
    private function getTemplateContent($area)
    {
        $templates = [
            'A1. Coordinación Nacional' => [
                'period' => '2025-II, de agosto a diciembre',
                'role_text' => 'bajo el rol de la Coordinación Nacional del área de ______________________________ dentro de la organización Acciones Empáticas.',
                'placeholder_field' => 'area'
            ],
            'A2. SkillUp 360' => [
                'period' => '2025-II, de agosto a diciembre',
                'role_text' => 'para el programa SkillUp 360°, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
            'A3. Coordinación de Proyectos' => [
                'period' => '2025-II, de septiembre a noviembre',
                'role_text' => 'bajo el rol de la Coordinación de Proyectos del proyecto _______________________ dentro de la organización Acciones Empáticas.',
                'placeholder_field' => 'project'
            ],
            'A4. Mentores Empáticos' => [
                'period' => '2025, de septiembre a noviembre',
                'role_text' => 'para el programa Mentores Empáticos, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
            'A5. Coordinación Regional' => [
                'period' => '2025-II, de septiembre a diciembre',
                'role_text' => 'bajo el rol de la Coordinación Regional de la región __________________________ dentro de la organización Acciones Empáticas.',
                'placeholder_field' => 'region'
            ],
            'A6. Líderes Que Impactan' => [
                'period' => '2025-II, de septiembre a diciembre',
                'role_text' => 'para el programa Líderes Que Impactan, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
            'A7. Aliados Empáticos' => [
                'period' => '2025, de septiembre a noviembre',
                'role_text' => 'para el programa de Aliados Empáticos, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
        ];

        return $templates[$area] ?? $templates['A1. Coordinación Nacional'];
    }

    private function generateHtmlFromPdfTemplate($person, $template)
    {
        $firstName = $person->first_name ?? 'Usuario';
        $lastName = $person->last_name ?? 'Ejemplo';
        $fullName = trim($firstName . ' ' . $lastName);
        $documentType = $person->document_type ?? 'DNI';
        $documentNumber = $person->document_number ?? '00000000';
        $province = $person->location?->province ?? 'Lima'; // Obtener provincia de la relación
        $currentDate = now()->format('d');
        $currentMonth = now()->locale('es')->format('F');
        
        // Obtener el campo específico según el área
        $specificField = '';
        if ($template['placeholder_field']) {
            switch ($template['placeholder_field']) {
                case 'area':
                    $specificField = $person->group ?? '______________________________';
                    break;
                case 'region':
                    $specificField = $person->location?->region ?? '__________________________';
                    break;
                case 'project':
                    $specificField = $person->project ?? '_______________________';
                    break;
            }
        }

        // Reemplazar placeholders en role_text
        $roleText = $template['role_text'];
        if (strpos($roleText, '______') !== false && $specificField) {
            $roleText = str_replace(
                ['______________________________', '__________________________', '_______________________'],
                $specificField,
                $roleText
            );
        }

        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.7;
            margin: 10px;
            color: #000;
        }
        .header {
            background-color: #40B5A8;
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
            text-decoration: underline;
        }
        .content {
            text-align: justify;
            margin-bottom: 10px;
        }
        ul {
            margin: 5px 0;
            padding-left: 15px;
        }
        li {
            margin-bottom: 5px;
        }
        .signature-section {
            margin-top: 20px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 250px;
            display: inline-block;
            margin: 0 10px;
        }
        .date-line {
            border-bottom: 1px solid #000;
            width: 150px;
            display: inline-block;
        }
        .footer {
            margin-top: 30px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Acciones Empáticas</h1>
    </div>
    
    <div class="title">CARTA DE COMPROMISO DEL VOLUNTARIO</div>
    
    <div class="content">
        <p>Yo, <strong>' . $fullName . '</strong>, identificado(a) con ' . $documentType . ' N.° <strong>' . $documentNumber . '</strong>, por la presente, manifiesto mi compromiso formal de asumir las responsabilidades como voluntario durante el ' . $template['period'] . ', ' . $roleText . '</p>
        
        <p>En mi rol como voluntario manifiesto que mi participación es libre, voluntaria y sin la expectativa de remuneración económica alguna, de acuerdo con la Ley N.º 28238, Ley General del Voluntariado.</p>
        
        <p><strong>Me comprometo con lo siguiente:</strong></p>
        <ul>
            <li>Desarrollar las actividades encomendadas en el área y rol que se me sea asignado.</li>
            <li>Asistir de las capacitaciones y actividades relacionadas al programa asignado.</li>
            <li>Asistir a las reuniones semanales del área asignada y reuniones mensuales organizacionales.</li>
            <li>Mantener una actitud respetuosa y confidencial en cada interacción con el equipo, beneficiarios y aliados de la organización.</li>
            <li>Cumplir la política de prevención de acoso sexual, política de prevención de discriminación, normativas, Reglamento Interno del Voluntario y el Código de Conducta de Acciones Empáticas.</li>
            <li>Mantener confidencialidad respecto a toda información, actividades, datos o documentos relacionados a los programas, proyectos y organización.</li>
            <li>He de reconocer que la organización Acciones Empáticas puede darme de baja como voluntario tras 3 inasistencias injustificadas a las reuniones del área y organizacionales.</li>
            <li>En caso decida dar por finalizado mi compromiso antes de la fecha estipulada del programa, comunicar al correo acciones.empaticas@grupoempatic.com mi retiro con 15 días de antelación.</li>
            <li>En caso me entreguen materiales, me comprometo a mantenerlos en buen estado, evitar su deterioro, pérdida, uso indebido, y devolverlos una vez finalizadas las actividades.</li>
            <li>He de reconocer que de incumplir las normativas podría ser retirado del programa y de ser necesario se evaluará la posibilidad de aperturar un proceso legal.</li>
        </ul>
        
        <p><strong>Asimismo, al firmar autorizo a Acciones Empáticas, así como a sus patrocinadores y aliados:</strong></p>
        <ul>
            <li>Recopilar, almacenar, procesar y usar mis datos personales, conforme a la Ley N.° 29733 y su reglamento, a Acciones Empáticas e instituciones aliadas para fines organizacionales y comunicacionales, y reconozco mis derechos ARCO.</li>
            <li>El uso, reproducción y difusión de mi imagen, voz y/o nombre en fotografías, videos y/o cualquier material audiovisual, cediendo los derechos patrimoniales de mi imagen de manera no exclusiva, sin límite territorial ni temporal, y de manera gratuita.</li>
            <li>Cedo todos los derechos patrimoniales de cualquier creación que desarrolle durante mis actividades (documentos, materiales, gráficos, etc.), conforme a la Ley N.° 822.</li>
        </ul>
        
        <p>Confirmo que he leído, comprendido y me comprometo a cumplir con lo establecido.</p>
    </div>
    
    <div class="signature-section">
        <p>' . $province . ', ' . $currentDate . ' de ' . $currentMonth . ' del 2025</p>
        <br><br>
        <p>FIRMA: <span class="signature-line"></span></p>
        <br><br>
        <p>Nombre: <strong>' . $fullName . '</strong></p>
        <p>' . $documentType . ': <strong>' . $documentNumber . '</strong></p>
    </div>
</body>
</html>';
    }
    
    public function checkDocumentStatus($userId)
    {
        try {
            // Buscar si el usuario ya tiene un documento firmado
            $signedDocument = SignedDocument::where('person_id', $userId)
                ->where('document_type', 'commitment_letter')
                ->first();
            
            $response = [
                'user_id' => $userId,
                'has_signed_document' => $signedDocument ? true : false,
                'signed_at' => $signedDocument ? $signedDocument->signed_at : null
            ];
            
            return response()->json($response)
                ->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
                
        } catch (\Exception $e) {
            \Log::error('Error checking document status:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estado del documento: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://empathic-actions-portal.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
    }
    
}