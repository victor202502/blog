<?php
// db_connect.php

// NUEVOS VALORES DE FALLBACK PARA DESARROLLO LOCAL (obtenidos de tu nuevo proyecto Neon)
$fallback_host = 'ep-noisy-poetry-a96i6gx3-pooler.gwc.azure.neon.tech';
$fallback_db_name = 'neondb';
$fallback_user = 'neondb_owner';
$fallback_pass = 'npg_tRCUsMp6f1Ze';  // ¡RECUERDA: ESTO ES PARA LOCAL! NO LO SUBAS A GIT CON LA CONTRASEÑA REAL SI ES UN REPO PÚBLICO.
$fallback_port = '5432';
$fallback_sslmode = 'require';

// Estos valores vendrán de las Variables de Entorno en Render (o donde despliegues)
$host = getenv('DB_HOST') ?: $fallback_host;
$db_name = getenv('DB_NAME') ?: $fallback_db_name;
$user = getenv('DB_USER') ?: $fallback_user;
$pass = getenv('DB_PASS') ?: $fallback_pass;
$port = getenv('DB_PORT') ?: $fallback_port;
$sslmode = getenv('DB_SSLMODE') ?: $fallback_sslmode;

// Data Source Name (DSN) para PostgreSQL
$dsn = "pgsql:host={$host};port={$port};dbname={$db_name};sslmode={$sslmode}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa PREPARE reales
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Para probar si funciona, puedes descomentar la siguiente línea temporalmente
    // echo "Conexión a la base de datos '{$db_name}' en host '{$host}' establecida exitosamente!";
} catch (\PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error de conexión. Por favor, inténtalo más tarde. Detalles del error: " . $e->getMessage()); // Muestra más detalles para depuración
    // O si estás depurando y quieres que el script se detenga con una excepción completa:
    // throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>