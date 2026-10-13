<?php
declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

$datos = obtenerDatosEstudiante($_POST);
$errores = [];
$errorGeneral = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        http_response_code(403);
        $errorGeneral = 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.';
    } else {
        $errores = validarDatosEstudiante($datos);
        if ($errores === []) {
            try {
                require __DIR__ . '/conexion.php';
                $sentencia = $pdo->prepare(
                    'INSERT INTO estudiantes (matricula, nombre, apellido, correo, fecha_nacimiento)
                     VALUES (:matricula, :nombre, :apellido, :correo, :fecha_nacimiento)'
                );
                $sentencia->execute($datos);
                establecerAlerta('success', 'El estudiante se registró correctamente.');
                header('Location: index.php', true, 303);
                exit;
            } catch (PDOException $exception) {
                error_log('Error al crear estudiante: ' . $exception->getMessage());
                $errorGeneral = ($exception->errorInfo[0] ?? '') === '23505'
                    ? 'Ya existe un estudiante con esa matrícula.'
                    : 'No fue posible guardar el estudiante.';
            } catch (RuntimeException $exception) {
                $errorGeneral = $exception->getMessage();
            }
        }
    }
}

$titulo = 'Registrar estudiante';
require __DIR__ . '/encabezado.php';
?>
<div class="mb-4">
    <a href="index.php" class="link-secondary text-decoration-none">&larr; Volver a estudiantes</a>
    <h1 class="h2 mt-3 mb-1">Registrar estudiante</h1>
    <p class="text-secondary mb-0">Completa los datos del nuevo estudiante.</p>
</div>
<?php if ($errorGeneral !== null): ?>
    <div class="alert alert-danger" role="alert"><?= escapar($errorGeneral) ?></div>
<?php endif; ?>
<?php if (isset($errores['general'])): ?>
    <div class="alert alert-danger" role="alert"><?= escapar($errores['general']) ?></div>
<?php endif; ?>
<form method="post" action="crear.php" class="bg-white border rounded shadow-sm p-3 p-md-4">
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
        <button type="submit" class="btn btn-primary">Guardar estudiante</button>
        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?php require __DIR__ . '/pie.php'; ?>