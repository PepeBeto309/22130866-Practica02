<?php
declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

$id = validarId($_GET['id'] ?? null);
if ($id === null || !validarTokenCsrf($_GET['token'] ?? null)) {
    establecerAlerta('danger', 'La solicitud de eliminación no es válida.');
    redirigirInicio();
}

try {
    require __DIR__ . '/conexion.php';
    $sentencia = $pdo->prepare('DELETE FROM estudiantes WHERE id = :id');
    $sentencia->execute(['id' => $id]);
    establecerAlerta(
        $sentencia->rowCount() === 1 ? 'success' : 'warning',
        $sentencia->rowCount() === 1
            ? 'El estudiante se eliminó correctamente.'
            : 'El estudiante solicitado ya no existe.'
    );
} catch (Throwable $exception) {
    error_log('Error al eliminar estudiante: ' . $exception->getMessage());
    establecerAlerta('danger', 'No fue posible eliminar el estudiante.');
}

redirigirInicio();