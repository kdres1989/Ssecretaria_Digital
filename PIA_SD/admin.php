<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
requerir_admin();

$tabs    = [
    'citas'     => 'Citas',
    'papeleria' => 'Papelería',
    'mensajes'  => 'Mensajes',
    'avisos'    => 'Avisos',
    'admins'    => 'Administradores'
];
$estados = ['pendiente', 'atendida', 'cancelada'];

$ver = $_GET['ver'] ?? ($_POST['ver'] ?? 'citas');
if (!isset($tabs[$ver])) {
    $ver = 'citas';
}

// ---------- Acciones (formularios del panel) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        flash('error', 'El formulario expiró. Inténtalo de nuevo.');
    } else {
        switch ($accion) {
            case 'estado_cita':
                $estado = $_POST['estado'] ?? '';
                if ($id > 0 && in_array($estado, $estados, true)) {
                    $notif = in_array($estado, ['atendida', 'cancelada'], true) ? 0 : 1;
                    $pdo->prepare('UPDATE citas SET estado = ?, notificado_usuario = ? WHERE id = ?')
                        ->execute([$estado, $notif, $id]);
                    flash('ok', 'Estado de la cita actualizado.');
                }
                break;

            case 'estado_papeleria':
                $estado = $_POST['estado'] ?? '';
                if ($id > 0 && in_array($estado, $estados, true)) {
                    $pdo->prepare('UPDATE solicitudes_papeleria SET estado = ? WHERE id = ?')->execute([$estado, $id]);
                    flash('ok', 'Estado de la solicitud actualizado.');
                }
                break;

            case 'leido_mensaje':
                if ($id > 0) {
                    $leido = !empty($_POST['leido']) ? 1 : 0;
                    $pdo->prepare('UPDATE mensajes_contacto SET leido = ? WHERE id = ?')->execute([$leido, $id]);
                    flash('ok', 'Mensaje actualizado.');
                }
                break;

            case 'nuevo_aviso':
                $tituloAviso    = trim($_POST['titulo'] ?? '');
                $contenidoAviso = trim($_POST['contenido'] ?? '');
                if (mb_strlen($tituloAviso) < 3 || mb_strlen($tituloAviso) > 150) {
                    flash('error', 'El título debe tener entre 3 y 150 caracteres.');
                } elseif (mb_strlen($contenidoAviso) < 5 || mb_strlen($contenidoAviso) > 2000) {
                    flash('error', 'El contenido debe tener entre 5 y 2000 caracteres.');
                } else {
                    $pdo->prepare('INSERT INTO notificaciones (titulo, contenido) VALUES (?, ?)')
                        ->execute([$tituloAviso, $contenidoAviso]);
                    flash('ok', 'Aviso publicado.');
                }
                break;

            case 'borrar_aviso':
                if ($id > 0) {
                    $pdo->prepare('DELETE FROM notificaciones WHERE id = ?')->execute([$id]);
                    flash('ok', 'Aviso eliminado.');
                }
                break;

            case 'nuevo_admin':
                $nombreAdmin  = trim($_POST['nombre'] ?? '');
                $usuarioAdmin = trim($_POST['usuario'] ?? '');
                $claveAdmin   = $_POST['clave'] ?? '';
                $confirmar    = $_POST['confirmar_clave'] ?? '';

                if (mb_strlen($nombreAdmin) < 3 || mb_strlen($nombreAdmin) > 120) {
                    flash('error', 'El nombre debe tener entre 3 y 120 caracteres.');
                } elseif (mb_strlen($usuarioAdmin) < 4 || mb_strlen($usuarioAdmin) > 100) {
                    flash('error', 'El usuario o correo debe tener entre 4 y 100 caracteres.');
                } elseif (mb_strlen($claveAdmin) < 6) {
                    flash('error', 'La contraseña debe tener al menos 6 caracteres.');
                } elseif ($claveAdmin !== $confirmar) {
                    flash('error', 'Las contraseñas no coinciden.');
                } else {
                    $existe = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
                    $existe->execute([$usuarioAdmin]);
                    if ($existe->fetch()) {
                        flash('error', 'Ese usuario o correo institucional ya está registrado.');
                    } else {
                        $hash = password_hash($claveAdmin, PASSWORD_DEFAULT);
                        $pdo->prepare('INSERT INTO usuarios (usuario, clave_hash, nombre, rol) VALUES (?, ?, ?, ?)')
                            ->execute([$usuarioAdmin, $hash, $nombreAdmin, 'administrativo']);
                        flash('ok', 'Nuevo administrador registrado correctamente.');
                    }
                }
                break;

            case 'borrar_admin':
                $miId = (int)($_SESSION['usuario_id'] ?? 0);
                if ($id <= 0) {
                    flash('error', 'Administrador no válido.');
                } elseif ($id === $miId) {
                    flash('error', 'No puedes eliminar tu propia cuenta mientras tienes la sesión iniciada.');
                } else {
                    $totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'administrativo'")->fetchColumn();
                    if ($totalAdmins <= 1) {
                        flash('error', 'Debe existir al menos un administrador en la plataforma.');
                    } else {
                        $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'administrativo'")->execute([$id]);
                        flash('ok', 'Cuenta administrativa eliminada.');
                    }
                }
                break;
        }
    }

    header('Location: admin.php?ver=' . $ver);
    exit;
}

// ---------- Contadores y estadísticas generales ----------
$conteos = [
    'citas'     => (int)$pdo->query("SELECT COUNT(*) FROM citas WHERE estado = 'pendiente'")->fetchColumn(),
    'papeleria' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes_papeleria WHERE estado = 'pendiente'")->fetchColumn(),
    'mensajes'  => (int)$pdo->query('SELECT COUNT(*) FROM mensajes_contacto WHERE leido = 0')->fetchColumn(),
    'avisos'    => (int)$pdo->query('SELECT COUNT(*) FROM notificaciones')->fetchColumn(),
    'admins'    => (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'administrativo'")->fetchColumn(),
];
$totalEstudiantes = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'estudiante'")->fetchColumn();

// ---------- Datos de la pestaña activa ----------
$filas = [];
if ($ver === 'citas') {
    $filas = $pdo->query('SELECT * FROM citas ORDER BY fecha DESC, hora DESC LIMIT 200')->fetchAll();
} elseif ($ver === 'papeleria') {
    $filas = $pdo->query('SELECT * FROM solicitudes_papeleria ORDER BY creado_en DESC, id DESC LIMIT 200')->fetchAll();
} elseif ($ver === 'mensajes') {
    $filas = $pdo->query('SELECT * FROM mensajes_contacto ORDER BY creado_en DESC, id DESC LIMIT 200')->fetchAll();
} elseif ($ver === 'avisos') {
    $filas = $pdo->query('SELECT * FROM notificaciones ORDER BY publicado_en DESC, id DESC')->fetchAll();
} else {
    $filas = $pdo->query("SELECT id, usuario, nombre, creado_en FROM usuarios WHERE rol = 'administrativo' ORDER BY creado_en DESC, id DESC")->fetchAll();
}

$titulo = 'Panel administrativo';
$css    = ['admin'];
include __DIR__ . '/includes/header.php';
?>

<main class="panel">
    <div class="panel-header">
        <div>
            <h2>Panel Administrativo</h2>
            <p class="subtitulo">Bienvenido, <strong><?= e($_SESSION['nombre'] ?? 'Administrador') ?></strong>. Gestiona solicitudes, avisos y personal.</p>
        </div>
    </div>

    <!-- Tarjetas de resumen rápido -->
    <div class="resumen-grid">
        <a href="admin.php?ver=citas" class="stat-card">
            <span class="stat-label">Citas pendientes</span>
            <span class="stat-valor"><?= $conteos['citas'] ?></span>
        </a>
        <a href="admin.php?ver=papeleria" class="stat-card">
            <span class="stat-label">Papelería pendiente</span>
            <span class="stat-valor"><?= $conteos['papeleria'] ?></span>
        </a>
        <a href="admin.php?ver=mensajes" class="stat-card">
            <span class="stat-label">Mensajes sin leer</span>
            <span class="stat-valor"><?= $conteos['mensajes'] ?></span>
        </a>
        <div class="stat-card">
            <span class="stat-label">Estudiantes registrados</span>
            <span class="stat-valor"><?= $totalEstudiantes ?></span>
        </div>
        <a href="admin.php?ver=admins" class="stat-card">
            <span class="stat-label">Administradores</span>
            <span class="stat-valor"><?= $conteos['admins'] ?></span>
        </a>
    </div>

    <?php mostrar_flash(); ?>

    <!-- Navegación por pestañas -->
    <div class="tabs">
        <?php foreach ($tabs as $clave => $nombre): ?>
            <a href="admin.php?ver=<?= e($clave) ?>" class="<?= $ver === $clave ? 'activa' : '' ?>">
                <?= e($nombre) ?>
                <?php if (in_array($clave, ['citas', 'papeleria', 'mensajes'], true) && $conteos[$clave] > 0): ?>
                    <span class="num"><?= (int)$conteos[$clave] ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

<?php if ($ver === 'citas'): ?>

    <div class="tabla-caja">
        <table>
            <tr>
                <th>Fecha</th><th>Hora</th><th>Estudiante</th><th>Documento</th>
                <th>Grado</th><th>Modalidad</th><th>Estado</th><th>Cambiar estado</th>
            </tr>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <td><?= e(fmt_fecha($f['fecha'])) ?></td>
                    <td><?= e(fmt_hora($f['hora'])) ?></td>
                    <td><strong><?= e($f['estudiante']) ?></strong></td>
                    <td><?= e($f['documento']) ?></td>
                    <td><?= e($f['grado']) ?></td>
                    <td><?= e(ucfirst($f['modalidad'])) ?></td>
                    <td><span class="estado <?= e($f['estado']) ?>"><?= e(ucfirst($f['estado'])) ?></span></td>
                    <td>
                        <form method="post" class="en-linea">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="estado_cita">
                            <input type="hidden" name="ver" value="citas">
                            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                            <select name="estado">
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?= e($est) ?>" <?= $f['estado'] === $est ? 'selected' : '' ?>><?= e(ucfirst($est)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-mini">Guardar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (!$filas): ?><div class="vacio">Todavía no hay citas agendadas.</div><?php endif; ?>
    </div>

<?php elseif ($ver === 'papeleria'): ?>

    <div class="tabla-caja">
        <table>
            <tr>
                <th>Recibida</th><th>Solicitante</th><th>Documento</th><th>Correo</th>
                <th>Observaciones</th><th>Estado</th><th>Cambiar estado</th>
            </tr>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <td><?= e(fmt_fecha_hora($f['creado_en'])) ?></td>
                    <td><strong><?= e($f['solicitante']) ?></strong></td>
                    <td><?= e($f['tipo_documento']) ?></td>
                    <td><?= e($f['correo']) ?></td>
                    <td><?= nl2br(e($f['observaciones'] ?? '')) ?></td>
                    <td><span class="estado <?= e($f['estado']) ?>"><?= e(ucfirst($f['estado'])) ?></span></td>
                    <td>
                        <form method="post" class="en-linea">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="estado_papeleria">
                            <input type="hidden" name="ver" value="papeleria">
                            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                            <select name="estado">
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?= e($est) ?>" <?= $f['estado'] === $est ? 'selected' : '' ?>><?= e(ucfirst($est)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-mini">Guardar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (!$filas): ?><div class="vacio">Todavía no hay solicitudes de papelería.</div><?php endif; ?>
    </div>

<?php elseif ($ver === 'mensajes'): ?>

    <div class="tabla-caja">
        <table>
            <tr>
                <th>Recibido</th><th>Nombre</th><th>Correo</th><th>Asunto</th><th>Mensaje</th><th>Acción</th>
            </tr>
            <?php foreach ($filas as $f): ?>
                <tr class="<?= $f['leido'] ? '' : 'no-leido' ?>">
                    <td><?= e(fmt_fecha_hora($f['creado_en'])) ?></td>
                    <td><?= e($f['nombre']) ?></td>
                    <td><?= e($f['correo']) ?></td>
                    <td><?= e($f['asunto']) ?></td>
                    <td><?= nl2br(e($f['mensaje'])) ?></td>
                    <td>
                        <form method="post" class="en-linea">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="leido_mensaje">
                            <input type="hidden" name="ver" value="mensajes">
                            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                            <input type="hidden" name="leido" value="<?= $f['leido'] ? '0' : '1' ?>">
                            <button type="submit" class="btn-mini <?= $f['leido'] ? 'secundario' : '' ?>">
                                <?= $f['leido'] ? 'Marcar no leído' : 'Marcar leído' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (!$filas): ?><div class="vacio">Todavía no hay mensajes de contacto.</div><?php endif; ?>
    </div>

<?php elseif ($ver === 'avisos'): ?>

    <div class="caja-form">
        <h3>Publicar un nuevo aviso</h3>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="accion" value="nuevo_aviso">
            <input type="hidden" name="ver" value="avisos">
            <div class="campo">
                <label for="titulo_aviso">Título del aviso</label>
                <input type="text" id="titulo_aviso" name="titulo" placeholder="Ej. Fechas de entrega de boletines" maxlength="150" required>
            </div>
            <div class="campo">
                <label for="contenido_aviso">Contenido</label>
                <textarea id="contenido_aviso" name="contenido" placeholder="Escribe los detalles del comunicado..." maxlength="2000" required></textarea>
            </div>
            <button type="submit" class="btn-mini">Publicar Aviso</button>
        </form>
    </div>

    <div class="tabla-caja">
        <table>
            <tr><th>Publicado</th><th>Título</th><th>Contenido</th><th>Acción</th></tr>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <td><?= e(fmt_fecha($f['publicado_en'])) ?></td>
                    <td><strong><?= e($f['titulo']) ?></strong></td>
                    <td><?= nl2br(e($f['contenido'])) ?></td>
                    <td>
                        <form method="post" class="en-linea" onsubmit="return confirm('¿Eliminar este aviso?');">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="borrar_aviso">
                            <input type="hidden" name="ver" value="avisos">
                            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                            <button type="submit" class="btn-mini rojo">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (!$filas): ?><div class="vacio">No hay avisos publicados.</div><?php endif; ?>
    </div>

<?php else: ?>

    <div class="caja-form">
        <h3>Registrar nuevo personal administrativo</h3>
        <p class="ayuda-form">La cuenta creada tendrá acceso completo a este panel administrativo.</p>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="accion" value="nuevo_admin">
            <input type="hidden" name="ver" value="admins">

            <div class="form-grid">
                <div class="campo">
                    <label for="nombre_admin">Nombre Completo</label>
                    <input type="text" id="nombre_admin" name="nombre" placeholder="Ej. Carlos Pérez" maxlength="120" required>
                </div>

                <div class="campo">
                    <label for="usuario_admin">Usuario o Correo Institucional</label>
                    <input type="text" id="usuario_admin" name="usuario" placeholder="secretaria2@colegio.edu.co" maxlength="100" required>
                </div>

                <div class="campo">
                    <label for="clave_admin">Contraseña</label>
                    <input type="password" id="clave_admin" name="clave" placeholder="Mínimo 6 caracteres" minlength="6" required>
                </div>

                <div class="campo">
                    <label for="confirmar_admin">Confirmar Contraseña</label>
                    <input type="password" id="confirmar_admin" name="confirmar_clave" placeholder="Repite la contraseña" minlength="6" required>
                </div>
            </div>

            <button type="submit" class="btn-mini">Crear Cuenta Administrativa</button>
        </form>
    </div>

    <div class="tabla-caja">
        <table>
            <tr>
                <th>Nombre</th>
                <th>Usuario / Correo</th>
                <th>Fecha de registro</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($filas as $f): ?>
                <?php $esMiCuenta = ((int)$f['id'] === (int)($_SESSION['usuario_id'] ?? 0)); ?>
                <tr>
                    <td>
                        <strong><?= e($f['nombre']) ?></strong>
                        <?php if ($esMiCuenta): ?>
                            <span class="badge-tu">Tú</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($f['usuario']) ?></td>
                    <td><?= e(fmt_fecha_hora($f['creado_en'])) ?></td>
                    <td>
                        <?php if (!$esMiCuenta): ?>
                            <form method="post" class="en-linea" onsubmit="return confirm('¿Seguro que deseas eliminar esta cuenta administrativa?');">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="accion" value="borrar_admin">
                                <input type="hidden" name="ver" value="admins">
                                <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                                <button type="submit" class="btn-mini rojo">Eliminar</button>
                            </form>
                        <?php else: ?>
                            <span class="texto-tenue">Sesión activa</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

<?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>