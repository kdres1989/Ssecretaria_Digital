<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

$misCitas = [];
if (esta_logueado() && !es_admin()) {
    $uid = (int)$_SESSION['usuario_id'];

    // Obtener historial de citas del estudiante
    $stmt = $pdo->prepare('SELECT id, estudiante, grado, modalidad, fecha, hora, estado, notificado_usuario FROM citas WHERE usuario_id = ? ORDER BY fecha DESC, hora DESC');
    $stmt->execute([$uid]);
    $misCitas = $stmt->fetchAll();

    // Marcar notificaciones de citas como leídas al abrir esta página
    $pdo->prepare('UPDATE citas SET notificado_usuario = 1 WHERE usuario_id = ? AND notificado_usuario = 0')->execute([$uid]);
}

$avisos = $pdo->query('SELECT titulo, contenido, publicado_en FROM notificaciones ORDER BY publicado_en DESC, id DESC')->fetchAll();

$titulo = 'Notificaciones';
$css    = ['avisos'];
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h2>Notificaciones</h2>
    <p class="subtitle">Estado de tus citas, circulares y avisos institucionales.</p>

    <?php if ($misCitas): ?>
        <h3 style="color:#002147; margin: 20px 0 12px 0;">Estado de mis Citas de Matrícula</h3>
        <?php foreach ($misCitas as $c): ?>
            <?php
                $esNueva = ((int)$c['notificado_usuario'] === 0 && in_array($c['estado'], ['atendida', 'cancelada'], true));
                $borde   = $c['estado'] === 'atendida' ? '#1e6b34' : ($c['estado'] === 'cancelada' ? '#b3261e' : '#d49b00');
                $textoEstado = $c['estado'] === 'atendida'
                    ? 'Tu cita fue marcada como ATENDIDA por la secretaría.'
                    : ($c['estado'] === 'cancelada'
                        ? 'Tu cita fue CANCELADA por la secretaría. Puedes agendar una nueva si lo necesitas.'
                        : 'Tu cita está PENDIENTE de atención.');
            ?>
            <article class="aviso-card" style="border-left: 5px solid <?= $borde ?>;">
                <h3>
                    Cita de Matrícula - <?= e(ucfirst($c['estado'])) ?>
                    <?php if ($esNueva): ?>
                        <span style="background:#d93025; color:#fff; font-size:11px; padding:2px 8px; border-radius:10px; margin-left:6px;">Nuevo</span>
                    <?php endif; ?>
                </h3>
                <div class="fecha">
                    Programada para el <?= e(fmt_fecha($c['fecha'])) ?> a las <?= e(fmt_hora($c['hora'])) ?> (Modalidad: <?= e(ucfirst($c['modalidad'])) ?>)
                </div>
                <p><?= e($textoEstado) ?></p>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <h3 style="color:#002147; margin: 25px 0 12px 0;">Avisos Institucionales</h3>
    <?php foreach ($avisos as $a): ?>
        <article class="aviso-card">
            <h3><?= e($a['titulo']) ?></h3>
            <div class="fecha"><?= e(fmt_fecha($a['publicado_en'])) ?></div>
            <p><?= nl2br(e($a['contenido'])) ?></p>
        </article>
    <?php endforeach; ?>

    <?php if (!$avisos): ?>
        <div class="vacio">Por ahora no hay avisos publicados.</div>
    <?php endif; ?>

    <div class="back"><a href="index.php">⬅ Volver al Inicio</a></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>