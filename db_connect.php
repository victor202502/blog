<?php
$host = '127.0.0.1';
$db_name = 'blog';
$user = 'blog';
$pass = 'blog_local_password';
$port = '5432';

$dsn = "pgsql:host={$host};port={$port};dbname={$db_name}";

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
    http_response_code(503);
    die("No se pudo conectar con la base de datos. Comprueba que el servicio esté iniciado.");
}
?>