<?php
// Conexión a MySQL/MariaDB (XAMPP) usando PDO
$host    = 'localhost';
$db      = 'secretaria_digital';
$usuario = 'root';   // usuario por defecto de XAMPP
$clave   = '';       // por defecto en XAMPP la contraseña está vacía
$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=$charset",
        $usuario,
        $clave,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('No se pudo conectar a la base de datos. Revisa que MySQL esté iniciado en XAMPP y que exista la base "secretaria_digital". Detalle: ' . htmlspecialchars($e->getMessage()));
}
