const http = require('http');
const url = require('url');

console.log('🚀 Iniciando servidor de documentos en puerto 8000...');
console.log('URL: http://localhost:8000');
console.log('Presiona Ctrl+C para detener\n');

const server = http.createServer((req, res) => {
    // CORS headers
    res.setHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    
    console.log(`📨 ${req.method} ${req.url}`);

    // Handle OPTIONS request
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    // Handle GET to /api/documents/commitment-letter/{id}
    if (req.method === 'GET' && req.url.includes('/api/documents/commitment-letter/')) {
        const parsedUrl = url.parse(req.url, true);
        const params = parsedUrl.query;
        
        console.log('📄 Parámetros recibidos:', params);
        
        // Generar HTML del documento
        const html = generateCommitmentLetter(params);
        
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        res.writeHead(200);
        res.end(html);
    } else {
        res.writeHead(404);
        res.end(JSON.stringify({ message: 'Endpoint no encontrado', url: req.url }));
    }
});

function generateCommitmentLetter(params) {
    const {
        first_name = 'Usuario',
        last_name = 'Ejemplo', 
        document_type = 'DNI',
        document_number = '00000000',
        area = 'A1. Coordinación Nacional',
        group = 'Gestión del Talento'
    } = params;

    const fullName = `${first_name} ${last_name}`.trim();
    const currentDate = new Date().getDate();
    const currentMonth = new Date().toLocaleDateString('es-ES', { month: 'long' });

    // Mapeo de áreas a textos específicos
    const areaTexts = {
        'A1. Coordinación Nacional': {
            period: '2025-II, de agosto a diciembre',
            role: `bajo el rol de la Coordinación Nacional del área de ${group} dentro de la organización Acciones Empáticas.`
        },
        'A2. SkillUp 360': {
            period: '2025-II, de agosto a diciembre',
            role: 'para el programa SkillUp 360°, dentro de la organización Acciones Empáticas.'
        },
        'A5. Coordinación Regional': {
            period: '2025-II, de septiembre a diciembre',
            role: `bajo el rol de la Coordinación Regional de la región ${group} dentro de la organización Acciones Empáticas.`
        }
    };

    const areaConfig = areaTexts[area] || areaTexts['A1. Coordinación Nacional'];

    return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            color: #000;
            background: white;
        }
        .header {
            background-color: #40B5A8;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
        }
        .title {
            text-align: center;
            font-size: 16px;
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
        .strong { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Acciones Empáticas</h1>
    </div>
    
    <div class="title">CARTA DE COMPROMISO DEL VOLUNTARIO</div>
    
    <div class="content">
        <p>Yo, <span class="strong">${fullName}</span>, identificado(a) con ${document_type} N.° <span class="strong">${document_number}</span>, por la presente, manifiesto mi compromiso formal de asumir las responsabilidades como voluntario durante el ${areaConfig.period}, ${areaConfig.role}</p>
        
        <p>En mi rol como voluntario manifiesto que mi participación es libre, voluntaria y sin la expectativa de remuneración económica alguna, de acuerdo con la Ley N.º 28238, Ley General del Voluntariado.</p>
        
        <p><span class="strong">Me comprometo con lo siguiente:</span></p>
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
        
        <p><span class="strong">Asimismo, al firmar autorizo a Acciones Empáticas, así como a sus patrocinadores y aliados:</span></p>
        <ul>
            <li>Recopilar, almacenar, procesar y usar mis datos personales, conforme a la Ley N.° 29733 y su reglamento, a Acciones Empáticas e instituciones aliadas para fines organizacionales y comunicacionales, y reconozco mis derechos ARCO.</li>
            <li>El uso, reproducción y difusión de mi imagen, voz y/o nombre en fotografías, videos y/o cualquier material audiovisual, cediendo los derechos patrimoniales de mi imagen de manera no exclusiva, sin límite territorial ni temporal, y de manera gratuita.</li>
            <li>Cedo todos los derechos patrimoniales de cualquier creación que desarrolle durante mis actividades (documentos, materiales, gráficos, etc.), conforme a la Ley N.° 822.</li>
        </ul>
        
        <p>Confirmo que he leído, comprendido y me comprometo a cumplir con lo establecido.</p>
    </div>
    
    <div class="signature-section">
        <p><span class="date-line">${currentDate}</span> de ${currentMonth} del 2025, Perú</p>
        <br><br>
        <p>FIRMA: <span class="signature-line"></span></p>
        <br><br>
        <p>Nombre: <span class="strong">${fullName}</span></p>
        <p>${document_type}: <span class="strong">${document_number}</span></p>
    </div>
</body>
</html>`;
}

server.listen(8001, () => {
    console.log('✅ Servidor iniciado exitosamente en http://localhost:8001');
    console.log('🔍 Escuchando requests para documentos...\\n');
});

process.on('SIGINT', () => {
    console.log('\\n🛑 Deteniendo servidor...');
    server.close(() => {
        console.log('✅ Servidor detenido');
        process.exit(0);
    });
});