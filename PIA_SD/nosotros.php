<?php
require_once __DIR__ . '/includes/funciones.php';
$titulo = 'Sobre Nosotros';
$css    = ['nosotros'];
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <h2>Visión</h2>
        <p>Convertir la secretaría escolar en un espacio 100% digital, accesible y sostenible.</p>
    </div>

    <div class="card alt">
        <h2>Misión</h2>
        <p>Brindar un sistema digital confiable para gestionar trámites académicos.</p>
    </div>

    <div class="card">
        <h2>Objetivos</h2>
        <p>✔ Interfaces fáciles de usar</p>
        <p>✔ Digitalización de procesos</p>
        <p>✔ Plataforma eficiente</p>
    </div>

    <div class="card alt">
        <h2>Resultados Esperados</h2>
        <p>✔ Reducción de tiempos</p>
        <p>✔ Mejor atención</p>
        <p>✔ Uso de tecnología</p>
    </div>

    <div class="card">
        <h2>Integrantes del Proyecto</h2>
        <div class="integrantes">
            <div>Sofía Alvarez Muñoz</div>
            <div>Isabela Gil Castaño</div>
            <div>Juan Manuel Rodríguez Baena</div>
            <div>Ximena Suárez Tavera</div>
        </div>
    </div>

    <div class="back">
        <a href="index.php">⬅ Volver al Inicio</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
