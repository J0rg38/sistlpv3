<?php
// setup_db.php
// Script temporal para inicializar la base de datos
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear Base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sistlpv3_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE sistlpv3_db");

    // Crear Tabla usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'user') DEFAULT 'user',
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insertar usuario por defecto si no existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = 'admin@admin.com'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $insert = $pdo->prepare("INSERT INTO usuarios (nombre, apellidos, email, password, rol) VALUES ('Administrador', 'Principal', 'admin@admin.com', ?, 'admin')");
        $insert->execute([$hash]);
        echo "Base de datos y usuario admin (admin@admin.com / admin123) creados correctamente.<br>";
    } else {
        echo "La base de datos ya estaba inicializada.<br>";
    }
    
    echo "<br><a href='index.php'>&rarr; Ir al Login</a>";

} catch (PDOException $e) {
    die("Error configurando la BD: " . $e->getMessage());
}
?>
