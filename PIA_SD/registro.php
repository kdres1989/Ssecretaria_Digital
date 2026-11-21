<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

if (esta_logueado()) {
    header('Location: ' . (es_admin() ? 'admin.php' : 'index.php'));
    exit;
}

$errores = [];
$nombre  = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre          = trim($_POST['nombre'] ?? '');
    $usuario         = trim($_POST['usuario'] ?? '');
    $clave           = $_POST['clave'] ?? '';
    $confirmar_clave = $_POST['confirmar_clave'] ?? '';

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $errores[] = 'El formulario expiró. Recarga la página e inténtalo de nuevo.';
    } else {
        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errores[] = 'Ingresa tu nombre completo (mínimo 3 caracteres).';
        }
        if ($usuario === '' || mb_strlen($usuario) < 4) {
            $errores[] = 'Ingresa un usuario o correo institucional válido (mínimo 4 caracteres).';
        }
        if (mb_strlen($clave) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if ($clave !== $confirmar_clave) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if (empty($errores)) {
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
            $stmt->execute([$usuario]);
            if ($stmt->fetch()) {
                $errores[] = 'Ese usuario o correo institucional ya está registrado.';
            }
        }

        if (empty($errores)) {
            $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
            $rol        = 'estudiante';

            $stmt = $pdo->prepare('INSERT INTO usuarios (usuario, clave_hash, nombre, rol) VALUES (?, ?, ?, ?)');
            if ($stmt->execute([$usuario, $clave_hash, $nombre, $rol])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int)$pdo->lastInsertId();
                $_SESSION['nombre']     = $nombre;
                $_SESSION['rol']        = $rol;

                header('Location: index.php');
                exit;
            } else {
                $errores[] = 'Ocurrió un error al crear la cuenta. Inténtalo más tarde.';
            }
        }
    }
}

$titulo = 'Crear Cuenta';
$css    = ['formularios', 'login'];
include __DIR__ . '/includes/header.php';
?>

<main>
    <div class="login-wrapper">
        <h2>Registro de Estudiante</h2>
        <p class="desc">Crea tu cuenta para agendar citas y gestionar tus trámites.</p>

        <?php if (!empty($errores)): ?>
            <div class="aviso error">
                <?php foreach ($errores as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="registro.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej. Ana María Gómez"
                       maxlength="120" value="<?= e($nombre) ?>" required>
            </div>

            <div class="form-group">
                <label for="usuario">Correo Institucional o Usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="usuario@colegio.edu.co"
                       maxlength="100" value="<?= e($usuario) ?>" required>
            </div>

            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="clave" placeholder="Mínimo 6 caracteres"
                       minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirmar_clave">Confirmar Contraseña</label>
                <input type="password" id="confirmar_clave" name="confirmar_clave" placeholder="Repite tu contraseña"
                       minlength="6" required>
            </div>

            <button type="submit">Registrarme</button>
        </form>

        <div class="register">
            ¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>