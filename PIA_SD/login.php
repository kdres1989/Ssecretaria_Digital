<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

if (esta_logueado()) {
    header('Location: ' . (es_admin() ? 'admin.php' : 'index.php'));
    exit;
}

$error           = '';
$rol             = 'estudiante';
$usuarioGuardado = $_COOKIE['usuario_guardado'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rol = $_POST['rol'] ?? 'estudiante';
    if (!in_array($rol, ['estudiante', 'administrativo'], true)) {
        $rol = 'estudiante';
    }
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = $_POST['clave'] ?? '';
    $usuarioGuardado = $usuario;

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $error = 'El formulario expiró. Recarga la página e inténtalo de nuevo.';
    } else {
        $stmt = $pdo->prepare('SELECT id, clave_hash, nombre, rol FROM usuarios WHERE usuario = ? AND rol = ?');
        $stmt->execute([$usuario, $rol]);
        $fila = $stmt->fetch();

        if ($fila && password_verify($clave, $fila['clave_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$fila['id'];
            $_SESSION['nombre']     = $fila['nombre'];
            $_SESSION['rol']        = $fila['rol'];

            if (!empty($_POST['recordar'])) {
                setcookie('usuario_guardado', $usuario, time() + 60 * 60 * 24 * 30, '/');
            } else {
                setcookie('usuario_guardado', '', time() - 3600, '/');
            }

            header('Location: ' . ($fila['rol'] === 'administrativo' ? 'admin.php' : 'index.php'));
            exit;
        }
        $error = 'Usuario, contraseña o tipo de usuario incorrectos.';
    }
}

$titulo = 'Iniciar Sesión';
$css    = ['formularios', 'login'];
include __DIR__ . '/includes/header.php';
?>

<main>
    <div class="login-wrapper">
        <h2>Ingreso a la Plataforma</h2>
        <p class="desc">Selecciona tu perfil e ingresa tus credenciales.</p>

        <?php if ($error): ?>
            <div class="aviso error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="rol">Tipo de Usuario</label>
                <select id="rol" name="rol" required>
                    <option value="estudiante" <?= $rol === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                    <option value="administrativo" <?= $rol === 'administrativo' ? 'selected' : '' ?>>Personal Administrativo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="usuario">Correo Institucional o Usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="usuario@colegio.edu.co"
                       value="<?= e($usuarioGuardado) ?>" required>
            </div>

            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="clave" placeholder="••••••••" required>
            </div>

            <div class="actions">
                <label><input type="checkbox" name="recordar" <?= $usuarioGuardado !== '' ? 'checked' : '' ?>> Recordar mi usuario</label>
                <a href="recuperar.php">¿Olvidaste tu clave?</a>
            </div>

            <button type="submit">Ingresar</button>
        </form>

        <div class="register">
            ¿Aún no tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>