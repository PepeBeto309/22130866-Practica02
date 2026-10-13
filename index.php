<?php
declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

$estudiantes = [];
$error = null;

try {
    require __DIR__ . '/conexion.php';
    $consulta = $pdo->query(
        'SELECT id, matricula, nombre, apellido, correo, fecha_nacimiento, creado_en
         FROM estudiantes ORDER BY id DESC'
    );
    $estudiantes = $consulta->fetchAll();
} catch (Throwable $exception) {
    error_log('Error al consultar estudiantes: ' . $exception->getMessage());
    $error = 'No fue posible cargar los estudiantes. Verifica la conexión y que exista la tabla.';
}

$titulo = 'Estudiantes registrados';
require __DIR__ . '/encabezado.php';
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Estudiantes</h1>
        <p class="text-secondary mb-0">Registra y administra la información académica.</p>
    </div>
    <a class="btn btn-primary" href="crear.php">Registrar nuevo estudiante</a>
</div>

<?php if ($error !== null): ?>
    <div class="alert alert-danger" role="alert"><?= escapar($error) ?></div>
<?php elseif ($estudiantes === []): ?>
    <div class="alert alert-info" role="status">Aún no hay estudiantes registrados.</div>
<?php else: ?>
    <div class="table-responsive bg-white border rounded shadow-sm">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th scope="col">Matrícula</th>
                <th scope="col">Nombre</th>
                <th scope="col">Correo</th>
                <th scope="col">Fecha de nacimiento</th>
                <th scope="col" class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($estudiantes as $estudiante): ?>
                <tr>
                    <td><?= escapar($estudiante['matricula']) ?></td>
                    <td><?= escapar($estudiante['nombre'] . ' ' . $estudiante['apellido']) ?></td>
                    <td><?= escapar($estudiante['correo']) ?></td>
                    <td><?= escapar($estudiante['fecha_nacimiento']) ?></td>
                    <td>
                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?= escapar($estudiante['id']) ?>">Editar</a>
                            <form action="eliminar.php" method="get" onsubmit="return confirm('¿Eliminar este estudiante?');">
                                <input type="hidden" name="id" value="<?= escapar($estudiante['id']) ?>">
                                <input type="hidden" name="token" value="<?= escapar(tokenCsrf()) ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/pie.php'; ?>