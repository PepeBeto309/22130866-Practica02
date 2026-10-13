<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function escapar(string|int|null $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tokenCsrf(): string
{
    if (!isset($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['token_csrf'];
}

function validarTokenCsrf(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['token_csrf'])
        && hash_equals($_SESSION['token_csrf'], $token);
}

function establecerAlerta(string $tipo, string $mensaje): void
{
    $_SESSION['alerta'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtenerAlerta(): ?array
{
    $alerta = $_SESSION['alerta'] ?? null;
    unset($_SESSION['alerta']);

    return is_array($alerta) ? $alerta : null;
}

function validarId(mixed $valor): ?int
{
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return $id === false ? null : $id;
}

function obtenerDatosEstudiante(array $entrada): array
{
    $datos = [];

    foreach (['matricula', 'nombre', 'apellido', 'correo', 'fecha_nacimiento'] as $campo) {
        $valor = $entrada[$campo] ?? '';
        $datos[$campo] = is_string($valor) ? trim($valor) : '';
    }

    return $datos;
}

function validarDatosEstudiante(array $datos): array
{
    $errores = [];
    $limites = [
        'matricula' => 30,
        'nombre' => 100,
        'apellido' => 100,
        'correo' => 150,
    ];

    foreach ($limites as $campo => $maximo) {
        if ($datos[$campo] === '' || strlen($datos[$campo]) > $maximo) {
            $errores[$campo] = 'Este campo es obligatorio y debe respetar su longitud máxima.';
        }
    }

    if ($datos['correo'] !== '' && filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) === false) {
        $errores['correo'] = 'Ingresa un correo electrónico válido.';
    }

    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $datos['fecha_nacimiento']);
    $erroresFecha = DateTimeImmutable::getLastErrors();
    if (
        $fecha === false
        || $fecha->format('Y-m-d') !== $datos['fecha_nacimiento']
        || ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))
    ) {
        $errores['fecha_nacimiento'] = 'Ingresa una fecha de nacimiento válida.';
    }

    return $errores;
}

function redirigirInicio(): never
{
    header('Location: index.php', true, 303);
    exit;
}