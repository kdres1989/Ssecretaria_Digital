<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

$grados      = ['Preescolar', 'Primaria (1° - 5°)', 'Secundaria (6° - 9°)', 'Media (10° - 11°)'];
$modalidades = ['presencial' => 'Presencial en Secretaría', 'virtual' => 'Virtual (Videollamada)'];
$horas       = ['08:00 AM' => '08:00:00', '10:00 AM' => '10:00:00', '02:00 PM' => '14:00:00', '04:00 PM' => '16:00:00'];

$errores = [];
$d = [
    'estudiante' => esta_logueado() ? ($_SESSION['nombre'] ?? '') : '',
    'documento'  => '',
    'grado'      => '',
    'modalidad'  => 'presencial',
    'fecha'      => '',
    'hora'       => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($d as $campo => $_) {
        $d[$campo] = trim($_POST[$campo] ?? '');
    }

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $errores[] = 'El formulario expiró. Recarga la página e inténtalo de nuevo.';
    }
    if (mb_strlen($d['estudiante']) < 3 || mb_strlen($d['estudiante']) > 120) {
        $errores[] = 'Escribe el nombre completo del estudiante.';
    }
    if (!preg_match('/^[0-9A-Za-z.\-]{5,20}$/', $d['documento'])) {
        $errores[] = 'El documento de identidad no es válido (entre 5 y 20 caracteres).';
    }
    if (!in_array($d['grado'], $grados, true)) {
        $errores[] = 'Selecciona el grado a matricular.';
    }
    if (!isset($modalidades[$d['modalidad']])) {
        $errores[] = 'Selecciona la modalidad de atención.';
    }
    if (!isset($horas[$d['hora']])) {
        $errores[] = 'Selecciona la hora de la cita.';
    }

    $fecha = DateTime::createFromFormat('Y-m-d', $d['fecha']);
    if (!$fecha || $fecha->format('Y-m-d') !== $d['fecha']) {
        $errores[] = 'Selecciona una fecha válida.';
    } elseif ($d['fecha'] < date('Y-m-d')) {
        $errores[] = 'La fecha no puede ser anterior a hoy.';
    } elseif ((int)$fecha->format('N') > 5) {
        $errores[] = 'La secretaría atiende de lunes a viernes. Elige un día entre semana.';
    }

    if (!$errores) {
        $hora = $horas[$d['hora']];

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE fecha = ? AND hora = ? AND estado <> 'cancelada'");
        $stmt->execute([$d['fecha'], $hora]);
        if ((int)$stmt->fetchColumn() > 0) {
            $errores[] = 'Ese horario ya está reservado. Elige otra hora u otro día.';
        }
    }

    if (!$errores) {
        $usuarioId = esta_logueado() ? (int)$_SESSION['usuario_id'] : null;
        $stmt = $pdo->prepare(
            'INSERT INTO citas (usuario_id, estudiante, documento, grado, modalidad, fecha, hora) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$usuarioId, $d['estudiante'], $d['documento'], $d['grado'], $d['modalidad'], $d['fecha'], $hora]);

        flash('ok', 'Cita agendada para el ' . fmt_fecha($d['fecha']) . ' a las ' . $d['hora'] . '. ¡Te esperamos!');
        header('Location: cita.php');
        exit;
    }
}

$titulo = 'Cita de Matrícula';
$css    = ['formularios'];
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h2>Agendar Cita de Matrícula</h2>
    <p class="subtitle">Reserva un espacio de atención presencial o virtual con la secretaría.</p>

    <?php mostrar_flash(); ?>
    <?php if ($errores): ?>
        <div class="aviso error">
            Revisa lo siguiente:
            <ul>
                <?php foreach ($errores as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="cita.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="estudiante">Nombre Completo del Estudiante</label>
            <input type="text" id="estudiante" name="estudiante" required placeholder="Ej. Juan Pérez"
                   value="<?= e($d['estudiante']) ?>">
        </div>

        <div class="form-group">
            <label for="documento">Documento de Identidad</label>
            <input type="text" id="documento" name="documento" required placeholder="Número de documento"
                   value="<?= e($d['documento']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="grado">Grado a Matricular</label>
                <select id="grado" name="grado" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($grados as $g): ?>
                        <option value="<?= e($g) ?>" <?= $d['grado'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="modalidad">Modalidad de Atención</label>
                <select id="modalidad" name="modalidad" required>
                    <?php foreach ($modalidades as $valor => $texto): ?>
                        <option value="<?= e($valor) ?>" <?= $d['modalidad'] === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="fecha">Fecha preferida</label>
                <input type="date" id="fecha" name="fecha" required min="<?= e(date('Y-m-d')) ?>"
                       value="<?= e($d['fecha']) ?>">
            </div>
            <div class="form-group">
                <label for="hora">Hora preferida</label>
                <select id="hora" name="hora" required>
                    <option value="">Seleccione hora...</option>
                    <?php foreach ($horas as $texto => $_): ?>
                        <option value="<?= e($texto) ?>" <?= $d['hora'] === $texto ? 'selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit">Confirmar y Agendar Cita</button>
    </form>

    <div class="back"><a href="index.php">⬅ Volver al Inicio</a></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
