<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

// Si ya inició sesión, lo mandamos a su sitio
if (esta_logueado()) {
    header('Location: ' . (es_admin() ? 'admin.php' : 'index.php'));
    exit;
}

$errores = [];
$exito   = '';
$usuario = '';
$nombre  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario         = trim($_POST['usuario'] ?? '');
    $nombre          = trim($_POST['nombre'] ?? '');
    $nueva_clave     = $_POST['nueva_clave'] ?? '';
    $confirmar_clave = $_POST['confirmar_clave'] ?? '';

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $errores[] = 'El formulario expiró. Recarga la página e inténtalo de nuevo.';
    } else {
        if ($usuario === '' || $nombre === '') {
            $errores[] = 'Completa tu usuario/correo y tu nombre registrado para verificar tu identidad.';
        }
        if (mb_strlen($nueva_clave) < 6) {
            $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        }
        if ($nueva_clave !== $confirmar_clave) {
            $errores[] = 'Las contraseñas nuevas no coinciden.';
        }

        // Buscar que coincidan usuario y nombre en la base de datos
        if (empty($errores)) {
            $stmt = $pdo->prepare('SELECT id, nombre FROM usuarios WHERE usuario = ? LIMIT 1');
            $stmt->execute([$usuario]);
            $fila = $stmt->fetch();

            // Comparación insensible a mayúsculas/minúsculas para el nombre
            if (!$fila || mb_strtolower(trim($fila['nombre'])) !== mb_strtolower($nombre)) {
                $errores[] = 'Los datos no coinciden con ninguna cuenta registrada.';
            } else {
                $clave_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
                $update = $pdo->prepare('UPDATE usuarios SET clave_hash = ? WHERE id = ?');

                if ($update->execute([$clave_hash, (int)$fila['id']])) {
                    $exito   = '¡Contraseña actualizada correctamente! Ya puedes iniciar sesión con tu nueva clave.';
                    $usuario = '';
                    $nombre  = '';
                } else {
                    $errores[] = 'Ocurrió un error al actualizar la contraseña. Inténtalo de nuevo.';
                }
            }
        }
    }
}

$titulo = 'Recuperar Contraseña';
$css    = ['formularios', 'login'];
include __DIR__ . '/includes/header.php';
?>

<main>
    <div class="login-wrapper">
        <h2>Recuperar Contraseña</h2>
        <p class="desc">Verifica tus datos registrados para asignar una nueva contraseña.</p>

        <?php if ($exito): ?>
            <div class="aviso exito"><?= e($exito) ?></div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="aviso error">
                <?php foreach ($errores as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="recuperar.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="usuario">Correo Institucional o Usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="usuario@colegio.edu.co"
                       maxlength="100" value="<?= e($usuario) ?>" required>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre Completo Registrado</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej. Ana María Gómez"
                       maxlength="120" value="<?= e($nombre) ?>" required>
            </div>

            <div class="form-group">
                <label for="nueva_clave">Nueva Contraseña</label>
                <input type="password" id="nueva_clave" name="nueva_clave" placeholder="Mínimo 6 caracteres"
                       minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirmar_clave">Confirmar Nueva Contraseña</label>
                <input type="password" id="confirmar_clave" name="confirmar_clave" placeholder="Repite tu nueva contraseña"
                       minlength="6" required>
            </div>

            <button type="submit">Actualizar Contraseña</button>
        </form>

        <div class="register">
            ¿Recordaste tu contraseña? <a href="login.php">Volver a Iniciar Sesión</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>