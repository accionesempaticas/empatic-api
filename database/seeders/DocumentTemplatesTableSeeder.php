<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentContent = <<<'EOD'

CARTA DE COMPROMISO DEL VOLUNTARIO
Yo, __________________________________,	identificado(a)	con	DNI	N.° __________________________________, por la presente, manifiesto mi compromiso formal de asumir las responsabilidades como voluntario durante el 2025 para el programa __________________________________ en el área de _____________________________________ dentro de la organización Acciones Empáticas.
En mi rol como voluntario manifiesto que mi participación es libre, voluntaria y sin la expectativa de remuneración económica alguna, de acuerdo con la Ley N.º 28238, Ley General del Voluntariado.
Me comprometo con lo siguiente: 
-Desarrollar las actividades encomendadas en el área y rol que se me sea asignado. 
-Asistir de las capacitaciones y actividades relacionadas al programa asignado. 
-Asistir a las reuniones semanales del área asignada y reuniones mensuales organizacionales.  
-Mantener una actitud respetuosa y confidencial en cada interacción con el equipo, beneficiarios y aliados de la organización. 
-Cumplir la política de prevención de acoso sexual, política de prevención de discriminación, normativas, Reglamento Interno del Voluntario y el Código de Conducta de Acciones Empáticas. 
-Mantener confidencialidad respecto a toda información, actividades, datos o documentos relacionados a los programas, proyectos y organización. 
-He de reconocer que la organización Acciones Empáticas puede darme de baja como voluntario tras 3 inasistencias injustificadas a las reuniones del área y organizacionales.
-En caso decida dar por finalizado mi compromiso antes de la fecha estipulada del programa, comunicar al correo acciones.empaticas@grupoempatic.com mi retiro con 15 días de antelación.
-En caso me entreguen materiales, me comprometo a mantenerlos en buen estado, evitar su deterioro, pérdida,  uso indebido, y devolverlos una vez finalizadas las actividades.
-He de reconocer que de incumplir las normativas podría ser retirado del programa y de ser necesario se evaluará la posibilidad de aperturar un proceso legal.

Asimismo, al firmar autorizo a Acciones Empáticas, así como a sus patrocinadores y aliados:
-Recopilar, almacenar, procesar y usar mis datos personales, conforme a la Ley N.º 29733 y su reglamento, a Acciones Empáticas e instituciones aliadas para fines organizacionales y comunicacionales, y reconozco mis derechos ARCO.
-El uso, reproducción y difusión de mi imagen, voz y/o nombre en fotografías, videos y/o cualquier material audiovisual, cediendo los derechos patrimoniales de mi imagen de manera no exclusiva, sin límite territorial ni temporal, y de manera gratuita.
-Cedos los derechos patrimoniales de cualquier creación que desarrolle durante mis actividades (documentos, materiales, gráficos, etc.), conforme a la Ley N.º 822.

Confirmo que he leído, comprendido y me comprometo a cumplir con lo establecido.
                                                                                                                                                                                                                                                                                                                                                __________________ del 2025, Perú



FIRMA:		_______________________________
EOD;

        DocumentTemplate::firstOrCreate([
            'name' => 'Carta de Compromiso del Voluntario 2025',
        ], [
            'content' => $documentContent,
        ]);
    }
}

