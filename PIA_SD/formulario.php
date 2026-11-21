<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

$asuntos = ['Consulta General', 'Dudas sobre Matrícula', 'Solicitud de registro', 'Sugerencias o Quejas'];

$errores = [];
$d = ['nombre' => '', 'email' => '', 'asunto' => '', 'mensaje' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($d as $campo => $_) {
        $d[$campo] = trim($_POST[$campo] ?? '');
    }

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $errores[] = 'El formulario expiró. Recarga la página e inténtalo de nuevo.';
    }
    if (mb_strlen($d['nombre']) < 3 || mb_strlen($d['nombre']) > 120) {
        $errores[] = 'Escribe tu nombre completo.';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 150) {
        $errores[] = 'Escribe un correo electrónico válido.';
    }
    if (!in_array($d['asunto'], $asuntos, true)) {
        $errores[] = 'Selecciona el asunto de tu consulta.';
    }
    if (mb_strlen($d['mensaje']) < 10 || mb_strlen($d['mensaje']) > 1000) {
        $errores[] = 'El mensaje debe tener entre 10 y 1000 caracteres.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare('INSERT INTO mensajes_contacto (nombre, correo, asunto, mensaje) VALUES (?, ?, ?, ?)');
        $stmt->execute([$d['nombre'], $d['email'], $d['asunto'], $d['mensaje']]);

        flash('ok', 'Tu mensaje fue enviado. La secretaría te responderá al correo indicado.');
        header('Location: formulario.php');
        exit;
    }
}

$titulo = 'Contacto';
$css    = ['formularios', 'contacto'];
include __DIR__ . '/includes/header.php';
?>

<main class="main-container">
    <div class="contact-grid">

        <div class="info-card">
            <div>
                <h3>Canales de Atención</h3>

                <div class="info-item">
                    <h4>Institución Educativa Santa Teresa</h4>
                    <p>Secretaría Escolar<br>Carrera 52 #109A-18</p>
                </div>

                <div class="info-item">
                    <h4>Horario de Atención</h4>
                    <p>Lunes a Viernes<br>8:00 AM – 3:00 PM</p>
                </div>

                <div class="info-item">
                    <h4>Teléfono</h4>
                    <p>+57 300-419-2620</p>
                </div>

                <div class="info-item">
                    <h4>Correo Institucional</h4>
                    <p>Santateresa@santateresaie.edu.co</p>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3>Enviar un Mensaje</h3>

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

            <form method="post" action="formulario.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Ana María Pérez"
                           value="<?= e($d['nombre']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com"
                           value="<?= e($d['email']) ?>">
                </div>

                <div class="form-group">
                    <label for="asunto">Asunto o Tipo de Consulta</label>
                    <select id="asunto" name="asunto" required>
                        <option value="">Seleccione una opción...</option>
                        <?php foreach ($asuntos as $a): ?>
                            <option value="<?= e($a) ?>" <?= $d['asunto'] === $a ? 'selected' : '' ?>><?= e($a) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" required maxlength="1000"
                              placeholder="Escribe aquí los detalles de tu consulta..."><?= e($d['mensaje']) ?></textarea>
                </div>

                <button type="submit">Enviar Consulta</button>
            </form>
        </div>

    </div>

    <div class="back">
        <a href="index.php">⬅ Volver al Inicio</a>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
