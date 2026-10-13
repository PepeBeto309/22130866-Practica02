<?php
declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

$metodoPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = validarId($metodoPost ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null));
$datos = obtenerDatosEstudiante($_POST);
$errores = [];
$errorGeneral = null;
$estudiante = null;

if ($id === null) {
    http_response_code(400);
    $errorGeneral = 'El identificador del estudiante no es válido.';
} else {
    try {
        require __DIR__ . '/conexion.php';

        $sentencia = $pdo->prepare(
            'SELECT id, matricula, nombre, apellido, correo, fecha_nacimiento
             FROM estudiantes WHERE id = :id'
        );
        $sentencia->execute(['id' => $id]);
        $estudiante = $sentencia->fetch();

        if ($estudiante === false) {
            http_response_code(404);
            $errorGeneral = 'No se encontró el estudiante solicitado.';
        } elseif (!$metodoPost) {
            $datos = $estudiante;
        } elseif ($metodoPost) {
            if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
                http_response_code(403);
                $errorGeneral = 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.';
            } else {
                $errores = validarDatosEstudiante($datos);
                if ($errores === []) {
                    $sentencia = $pdo->prepare(
                        'UPDATE estudiantes
                         SET matricula = :matricula, nombre = :nombre, apellido = :apellido,
                             correo = :correo, fecha_nacimiento = :fecha_nacimiento
                         WHERE id = :id'
                    );
                    $sentencia->execute([...$datos, 'id' => $id]);
                    establecerAlerta('success', 'Los datos del estudiante se actualizaron correctamente.');
                    header('Location: index.php', true, 303);
                    exit;
                }
            }
        }
    } catch (PDOException $exception) {
        error_log('Error al editar estudiante: ' . $exception->getMessage());
        $errorGeneral = ($exception->errorInfo[0] ?? '') === '23505'
            ? 'Ya existe un estudiante con esa matrícula.'
            : 'No fue posible cargar o actualizar el estudiante.';
    } catch (RuntimeException $exception) {
        $errorGeneral = $exception->getMessage();
    }
}

$titulo = 'Editar estudiante';
require __DIR__ . '/encabezado.php';
?>
<div class="mb-4">
    <a href="index.php" class="link-secondary text-decoration-none">&larr; Volver a estudiantes</a>
    <h1 class="h2 mt-3 mb-1">Editar estudiante</h1>
    <p class="text-secondary mb-0">Actualiza la información del registro.</p>
</div>
<?php if ($errorGeneral !== null): ?>
    <div class="alert alert-danger" role="alert"><?= escapar($errorGeneral) ?></div>
<?php endif; ?>
<?php if ($estudiante !== null && $estudiante !== false): ?>
    <form method="post" action="editar.php?id=<?= escapar($id) ?>" class="bg-white border rounded shadow-sm p-3 p-md-4">
        <input type="hidden" name="id" value="<?= escapar($id) ?>">
        <input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()) ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="matricula" class="form-label">Matrícula</label>
                <input id="matricula" name="matricula" class="form-control <?= isset($errores['matricula']) ? 'is-invalid' : '' ?>" maxlength="30" required value="<?= escapar($datos['matricula']) ?>">
                <?php if (isset($errores['matricula'])): ?><div class="invalid-feedback"><?= escapar($errores['matricula']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre</label>
                <input id="nombre" name="nombre" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>" maxlength="100" required value="<?= escapar($datos['nombre']) ?>">
                <?php if (isset($errores['nombre'])): ?><div class="invalid-feedback"><?= escapar($errores['nombre']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="apellido" class="form-label">Apellido</label>
                <input id="apellido" name="apellido" class="form-control <?= isset($errores['apellido']) ? 'is-invalid' : '' ?>" maxlength="100" required value="<?= escapar($datos['apellido']) ?>">
                <?php if (isset($errores['apellido'])): ?><div class="invalid-feedback"><?= escapar($errores['apellido']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input id="correo" name="correo" type="email" class="form-control <?= isset($errores['correo']) ? 'is-invalid' : '' ?>" maxlength="150" required value="<?= escapar($datos['correo']) ?>">
                <?php if (isset($errores['correo'])): ?><div class="invalid-feedback"><?= escapar($errores['correo']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="form-control <?= isset($errores['fecha_nacimiento']) ? 'is-invalid' : '' ?>" required value="<?= escapar($datos['fecha_nacimiento']) ?>">
                <?php if (isset($errores['fecha_nacimiento'])): ?><div class="invalid-feedback"><?= escapar($errores['fecha_nacimiento']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/pie.php'; ?>