<?php
// db_connect.php

// Estos valores vendrán de las Variables de Entorno en Render
$host = getenv('DB_HOST') ?: 'ep-young-night-a2yla1jq-pooler.eu-central-1.aws.neon.tech'; // Fallback para desarrollo local si no están seteadas
$db_name = getenv('DB_NAME') ?: 'neondb';
$user = getenv('DB_USER') ?: 'neondb_owner';
$pass = getenv('DB_PASS') ?: 'npg_s2oOghk6QdTp';  // ¡CAMBIA ESTO EN LOCAL Y NO LO SUBAS A GIT! Usa variables de entorno.
$port = getenv('DB_PORT') ?: '5432';
$sslmode = getenv('DB_SSLMODE') ?: 'require'; // Neon requiere sslmode

// Data Source Name (DSN) para PostgreSQL
$dsn = "pgsql:host={$host};port={$port};dbname={$db_name};sslmode={$sslmode}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa PREPARE reales
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En un entorno de producción, no mostrarías el mensaje de error detallado al usuario.
    // Lo registrarías y mostrarías un mensaje genérico.
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error de conexión. Por favor, inténtalo más tarde.");
    // O si estás depurando:
    // throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>