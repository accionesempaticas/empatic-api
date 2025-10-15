<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    private function getAreaTemplateMapping()
    {
        return [
            'A1. Coordinación Nacional' => '1. CARTA DE COMPROMISO DEL VOLUNTARIO - Coordinación Nacional',
            'A2. SkillUp 360' => '2. CARTA DE COMPROMISO DEL VOLUNTARIO - SkillUp 360',
            'A3. Coordinación de Proyectos' => '5. CARTA DE COMPROMISO DEL VOLUNTARIO - Coordinación de Proyectos',
            'A4. Mentores Empáticos' => '6. CARTA DE COMPROMISO DEL VOLUNTARIO - Mentores Empáticos',
            'A5. Coordinación Regional' => '3. CARTA DE COMPROMISO DEL VOLUNTARIO - Coordinación Regional',
            'A6. Líderes Que Impactan' => '4. CARTA DE COMPROMISO DEL VOLUNTARIO - Líderes Que Impactan',
            'A7. Aliados Empáticos' => '8. CARTA DE COMPROMISO DEL VOLUNTARIO - Aliados Empáticos',
        ];
    }

    private function getTemplateContent($area)
    {
        $templates = [
            'Coordinación Nacional' => [
                'period' => '2025-II, de agosto a diciembre',
                'role_text' => 'bajo el rol de la Coordinación Nacional del área de ______________________________ dentro de la organización Acciones Empáticas.',
                'placeholder_field' => 'area'
            ],
            'SkillUp 360' => [
                'period' => '2025-II, de agosto a diciembre',
                'role_text' => 'para el programa SkillUp 360°, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
            'Coordinación Programas' => [ 
                'period' => '2025-II, de septiembre a diciembre',
                'role_text' => 'bajo el rol de la Coordinación de Proyectos para el proyecto _______________________ dentro de la organización Acciones Empáticas.',
                'placeholder_field' => 'project'
            ],
            'Mentores Empáticos' => [
                'period' => '2025, de septiembre a diciembre',
                'role_text' => 'para el programa Mentores Empáticos, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
            'Coordinación Regional' => [
                'period' => '2025-II, de septiembre a diciembre',
                'role_text' => 'bajo el rol de la Coordinación Regional de la región __________________________ dentro de la organización Acciones Empáticas.',
                'placeholder_field' => 'region'
            ],
            'Líderes Que Impactan' => [
                'period' => '2025-II, de septiembre a diciembre',
                'role_text' => 'para el programa Líderes Que Impactan, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
            'Aliados Empáticos' => [
                'period' => '2025, de septiembre a noviembre',
                'role_text' => 'para el programa de Aliados Empáticos, dentro de la organización Acciones Empáticas.',
                'placeholder_field' => null
            ],
        ];

        return $templates[$area] ?? $templates['Coordinación Nacional'];
    }

    public function generateCommitmentLetter(Request $request, $personId)
    {
        // Log para debugging
        \Log::info('DocumentController - Parámetros recibidos:', $request->all());
        \Log::info('DocumentController - Person ID:', ['person_id' => $personId]);
        
        // Obtener datos de la persona desde la base de datos
        $person = \App\Models\Person::with(['location', 'formation', 'experience'])->find($personId);
        
        if (!$person) {
            return response('Usuario no encontrado', 404);
        }
        
        \Log::info('DocumentController - Datos de la persona:', $person->toArray());
        
        // Si el usuario no tiene los campos necesarios, buscar el usuario más reciente con rol 'user'
        if (!$person->first_name || !$person->last_name || !$person->area) {
            \Log::info('DocumentController - Usuario incompleto, buscando usuario más reciente...');
            $recentPerson = \App\Models\Person::with(['location', 'formation', 'experience'])
                ->where('role', 'user')
                ->whereNotNull('first_name')
                ->whereNotNull('area')
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($recentPerson) {
                \Log::info('DocumentController - Usando usuario más reciente:', $recentPerson->toArray());
                $person = $recentPerson;
            }
        }
        
        // Usar datos reales de la base de datos en lugar de parámetros de URL
        $area = $person->area ?? 'A1. Coordinación Nacional';
        $template = $this->getTemplateContent($area);
        
        // Crear el contenido HTML basado en los PDFs originales
        $html = $this->generateHtmlFromPdfTemplate($person, $template);
        
        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Access-Control-Allow-Origin', 'http://localhost:3001')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    private function generateHtmlFromPdfTemplate($person, $template)
    {
        $firstName = $person->first_name ?? 'Usuario';
        $lastName = $person->last_name ?? 'Ejemplo';
        $fullName = trim($firstName . ' ' . $lastName);
        $documentType = $person->document_type ?? 'DNI';
        $documentNumber = $person->document_number ?? '00000000';
        $province = $person->location?->province ?? 'Lima'; // Obtener provincia de la relación
        
        // Forzar localización a español para la fecha
        app()->setLocale('es');
        
        $currentDate = now()->format('d');
        
        // Array de meses en español
        $monthsSpanish = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        $currentMonth = $monthsSpanish[now()->format('n')];
        
        // Obtener el campo específico según el área
        $specificField = '';
        if ($template['placeholder_field']) {
            switch ($template['placeholder_field']) {
                case 'area':
                    $specificField = $person->group ?? '______________________________';
                    break;
                case 'region':
                    $specificField = $person->group ?? '__________________________';
                    break;
                case 'project':
                    $specificField = $person->group ?? '_______________________';
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
            font-size: 13px;
            line-height: 1.4;
            margin: 20px;
            color: #000;
        }
        .header {
            background-color: #40B5A8;
            color: white;
            padding: 17px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
            font-weight: bold;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }
        .content {
            text-align: justify;
            margin-bottom: 15px;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
        .signature-section {
            margin-top: 40px;
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
}