<?php
// Funciones comunes. Se incluye al inicio de TODAS las páginas (antes de escribir HTML).
date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escapa texto para mostrarlo de forma segura en HTML. */
function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/** Token que protege los formularios (CSRF). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valido($token): bool
{
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

function esta_logueado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

function es_admin(): bool
{
    return esta_logueado() && ($_SESSION['rol'] ?? '') === 'administrativo';
}

/** Solo deja pasar al personal administrativo. */
function requerir_admin(): void
{
    if (!es_admin()) {
        header('Location: login.php');
        exit;
    }
}

/** Mensaje que se muestra una sola vez después de redirigir. */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $mensaje];
}

function mostrar_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="aviso ' . e($f['tipo']) . '">' . e($f['msg']) . '</div>';
    }
}

function fmt_fecha($valor): string
{
    $d = date_create((string)$valor);
    return $d ? $d->format('d/m/Y') : '';
}

function fmt_hora($valor): string
{
    $d = date_create((string)$valor);
    return $d ? $d->format('h:i A') : '';
}

function fmt_fecha_hora($valor): string
{
    $d = date_create((string)$valor);
    return $d ? $d->format('d/m/Y h:i A') : '';
}
