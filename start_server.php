<?php
// Script para iniciar el servidor Laravel
echo "Iniciando servidor Laravel en puerto 8000...\n";
echo "URL: http://localhost:8000\n";
echo "Presiona Ctrl+C para detener el servidor\n\n";

// Cambiar al directorio correcto
chdir(__DIR__);

// Ejecutar el comando artisan serve
$command = 'php artisan serve --port=8000';
echo "Ejecutando: $command\n\n";

// Ejecutar el comando
passthru($command);
?>