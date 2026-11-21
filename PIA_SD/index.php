<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
$titulo = 'Inicio';
$css    = ['inicio'];
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h2>¿Qué trámite deseas realizar hoy?</h2>
    <p>Accede de forma rápida y digital a todos los servicios académicos.</p>
    <?php if (esta_logueado()): ?>
        <p style="margin-top:10px;">Hola, <?= e($_SESSION['nombre'] ?? '') ?></p>
    <?php endif; ?>
</section>

<main class="container">
    <div class="services-grid">

        <div class="card-service">
            <div>
                <h3>Cita de Matrícula</h3>
                <p>Agenda tu cita presencial o virtual para completar el proceso de matrícula.</p>
            </div>
            <a href="cita.php" class="btn-action">Agendar Cita</a>
        </div>

        <div class="card-service">
            <div>
                <h3>Notificaciones</h3>
                <p>Revisa circulares, avisos institucionales y comunicaciones importantes.</p>
            </div>
            <a href="notificaciones.php" class="btn-action">Ver Avisos</a>
        </div>

        <div class="card-service">
            <div>
                <h3>Retiro de Papelería</h3>
                <p>Solicita certificados, constancias de estudio y carpetas académicas.</p>
            </div>
            <a href="retiro.php" class="btn-action">Solicitar Papelería</a>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
