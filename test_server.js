const http = require('http');
const url = require('url');

console.log('🚀 Iniciando servidor de prueba en puerto 8000...');
console.log('URL: http://localhost:8000');
console.log('Presiona Ctrl+C para detener\n');

const server = http.createServer((req, res) => {
    // CORS headers
    res.setHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    res.setHeader('Content-Type', 'application/json');

    console.log(`📨 ${req.method} ${req.url}`);

    // Handle OPTIONS request
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    // Handle POST to /api/postulant
    if (req.method === 'POST' && req.url === '/api/postulant') {
        let body = '';
        
        req.on('data', chunk => {
            body += chunk.toString();
        });
        
        req.on('end', () => {
            console.log('📄 Datos recibidos:', body.length, 'bytes');
            
            const response = {
                message: 'Servidor de prueba funcionando',
                timestamp: new Date().toISOString(),
                received_content_type: req.headers['content-type'] || 'unknown',
                received_size: body.length
            };
            
            res.writeHead(422, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({
                message: 'Error simulado para debugging',
                errors: {
                    'area': ['Campo area requerido para debugging'],
                    'group': ['Campo group requerido para debugging']
                }
            }));
        });
    } else {
        res.writeHead(404);
        res.end(JSON.stringify({ message: 'Endpoint no encontrado', url: req.url }));
    }
});

server.listen(8000, () => {
    console.log('✅ Servidor iniciado exitosamente en http://localhost:8000');
    console.log('🔍 Monitorea las requests aquí...\n');
});

process.on('SIGINT', () => {
    console.log('\n🛑 Deteniendo servidor...');
    server.close(() => {
        console.log('✅ Servidor detenido');
        process.exit(0);
    });
});