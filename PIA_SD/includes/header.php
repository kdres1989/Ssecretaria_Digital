<?php
// Cabecera común. Antes de incluirla, cada página define:
//   $titulo => título de la pestaña
//   $css    => lista de hojas de estilo (sin ".css") dentro de assets/css/
$titulo = $titulo ?? 'Inicio';
$css    = $css ?? [];

$notifCitas = 0;
if (esta_logueado() && !es_admin() && isset($pdo)) {
    $stmtNotif = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE usuario_id = ? AND notificado_usuario = 0 AND estado IN ('atendida', 'cancelada')");
    $stmtNotif->execute([(int)$_SESSION['usuario_id']]);
    $notifCitas = (int)$stmtNotif->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> - Secretaría Digital</title>
    <link rel="stylesheet" href="assets/css/base.css">
<?php foreach ($css as $hoja): ?>
    <link rel="stylesheet" href="assets/css/<?= e($hoja) ?>.css">
<?php endforeach; ?>
    <style>
        .badge-notif {
            background: #d93025;
            color: #fff;
            border-radius: 999px;
            padding: 2px 7px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 4px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<header>
    <div class="logo-title">
        <a href="index.php">
            <img src="assets/img/logo.jpg" alt="Logo Secretaría">
        </a>
        <h1>Secretaría Digital</h1>
    </div>

    <nav>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="nosotros.php">Sobre Nosotros</a></li>
            <li>
                <a href="notificaciones.php">
                    Notificaciones<?php if ($notifCitas > 0): ?><span class="badge-notif"><?= $notifCitas ?></span><?php endif; ?>
                </a>
            </li>
            <li><a href="formulario.php">Contacto</a></li>
            <?php if (es_admin()): ?>
                <li><a href="admin.php">Panel</a></li>
            <?php endif; ?>
            <?php if (esta_logueado()): ?>
                <li><a href="logout.php" class="btn-login">Salir (<?= e($_SESSION['nombre'] ?? '') ?>)</a></li>
            <?php else: ?>
                <li><a href="registro.php">Registrarse</a></li>
                <li><a href="login.php" class="btn-login">Ingresar</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>