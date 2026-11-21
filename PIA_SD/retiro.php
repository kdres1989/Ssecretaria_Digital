<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

$tipos = [
    'Certificado de Estudios (Año Actual)',
    'Certificado de Notas (Años Anteriores)',
    'Paz y Salvo Académico / Financiero',
];

$errores = [];
$d = ['solicitante' => '', 'tipo_documento' => '', 'correo' => '', 'observaciones' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($d as $campo => $_) {
        $d[$campo] = trim($_POST[$campo] ?? '');
    }

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $errores[] = 'El formulario expiró. Recarga la página e inténtalo de nuevo.';
    }
    if (mb_strlen($d['solicitante']) < 3 || mb_strlen($d['solicitante']) > 120) {
        $errores[] = 'Escribe el nombre completo de quien solicita.';
    }
    if (!in_array($d['tipo_documento'], $tipos, true)) {
        $errores[] = 'Selecciona el tipo de documento requerido.';
    }
    if (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['correo']) > 150) {
        $errores[] = 'Escribe un correo electrónico válido.';
    }
    if (mb_strlen($d['observaciones']) > 500) {
        $errores[] = 'Las observaciones no pueden superar 500 caracteres.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'INSERT INTO solicitudes_papeleria (solicitante, tipo_documento, correo, observaciones) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['solicitante'],
            $d['tipo_documento'],
            $d['correo'],
            $d['observaciones'] !== '' ? $d['observaciones'] : null,
        ]);

        flash('ok', 'Tu solicitud fue enviada. Te avisaremos al correo ' . $d['correo'] . '.');
        header('Location: retiro.php');
        exit;
    }
}

$titulo = 'Retiro de Papelería';
$css    = ['formularios'];
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h2>Solicitud de Papelería Académica</h2>
    <p class="subtitle">Solicita certificados, calificaciones y paz y salvos.</p>

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

    <form method="post" action="retiro.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="solicitante">Nombre de quien solicita</label>
            <input type="text" id="solicitante" name="solicitante" required placeholder="Nombre completo"
                   value="<?= e($d['solicitante']) ?>">
        </div>

        <div class="form-group">
            <label for="tipo_documento">Tipo de Documento Requerido</label>
            <select id="tipo_documento" name="tipo_documento" required>
                <option value="">Seleccione el tipo...</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= e($t) ?>" <?= $d['tipo_documento'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="correo">Correo donde recibirá el documento</label>
            <input type="email" id="correo" name="correo" required placeholder="correo@ejemplo.com"
                   value="<?= e($d['correo']) ?>">
        </div>

        <div class="form-group">
            <label for="observaciones">Detalles adicionales u observaciones (Opcional)</label>
            <textarea id="observaciones" name="observaciones" style="min-height:80px;" maxlength="500"
                      placeholder="Indica el año o periodo específico si aplica..."><?= e($d['observaciones']) ?></textarea>
        </div>

        <button type="submit">Enviar Solicitud</button>
    </form>

    <div class="back"><a href="index.php">⬅ Volver al Inicio</a></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
