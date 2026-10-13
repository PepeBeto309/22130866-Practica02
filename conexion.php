<?php
declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'localhost';
$puerto = getenv('DB_PORT') ?: '5432';
$baseDatos = getenv('DB_NAME') ?: 'sistema_estudiantes';
$usuario = getenv('DB_USER') ?: 'programador_crud';
$contrasena = getenv('DB_PASSWORD') ?: 'ClaveSegura2026*';

try {
    $pdo = new PDO(
        "pgsql:host={$host};port={$puerto};dbname={$baseDatos}",
        $usuario,
        $contrasena,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log('Error de conexión a PostgreSQL: ' . $exception->getMessage());
    throw new RuntimeException('No fue posible conectar con la base de datos.', 0, $exception);
}