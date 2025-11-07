<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$dotenv = new Dotenv();

// ✅ Cargar siempre primero .env.test si existe
$envTestFile = dirname(__DIR__).'/.env.test';
if (file_exists($envTestFile)) {
    $dotenv->loadEnv($envTestFile);
    echo "✅ Cargando entorno desde .env.test" . PHP_EOL;
} else {
    $dotenv->bootEnv(dirname(__DIR__).'/.env');
    echo "⚠️  Archivo .env.test no encontrado, usando .env" . PHP_EOL;
}

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}

echo "✅ DEFAULT_URI detectada: " . ($_SERVER['DEFAULT_URI'] ?? 'NO DEFINIDA') . PHP_EOL;
