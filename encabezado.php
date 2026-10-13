<?php
declare(strict_types=1);

$titulo = $titulo ?? 'Gestión de estudiantes';
$alerta = obtenerAlerta();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="index.php">Sistema de estudiantes</a>
    </div>
</nav>
<main class="container py-4 py-md-5">
    <?php if ($alerta !== null): ?>
        <div class="alert alert-<?= escapar($alerta['tipo'] ?? 'info') ?> alert-dismissible fade show" role="alert">
            <?= escapar($alerta['mensaje'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>