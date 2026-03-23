<?php
require_once 'config/db.php';

try {
    // 1. Create 'roles' table
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create 'permisos' table
    $pdo->exec("CREATE TABLE IF NOT EXISTS permisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        modulo VARCHAR(50) NOT NULL,
        accion VARCHAR(50) NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        UNIQUE KEY mod_acc (modulo, accion)
    )");

    // 3. Create 'rol_permisos' table
    $pdo->exec("CREATE TABLE IF NOT EXISTS rol_permisos (
        rol_id INT NOT NULL,
        permiso_id INT NOT NULL,
        PRIMARY KEY (rol_id, permiso_id),
        FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
    )");

    // 4. Alter 'usuarios' table
    $columns = $pdo->query("SHOW COLUMNS FROM usuarios")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('tipo_documento', $columns)) {
        $pdo->exec("ALTER TABLE usuarios ADD tipo_documento VARCHAR(20) DEFAULT 'DNI' AFTER apellidos");
    }
    if (!in_array('numero_documento', $columns)) {
        $pdo->exec("ALTER TABLE usuarios ADD numero_documento VARCHAR(50) DEFAULT '' AFTER tipo_documento");
    }
    if (!in_array('activo', $columns)) {
        $pdo->exec("ALTER TABLE usuarios ADD activo TINYINT(1) DEFAULT 1 AFTER password");
    }
    if (!in_array('rol_id', $columns)) {
        $pdo->exec("ALTER TABLE usuarios ADD rol_id INT NULL AFTER activo");
        // We'll safely add the foreign key using a try-catch to avoid errors if constraint exists
        try {
            $pdo->exec("ALTER TABLE usuarios ADD CONSTRAINT fk_user_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL");
        } catch(PDOException $e) {}
    }

    // 5. Insert default role and link admin user
    $adminRoleQuery = $pdo->query("SELECT id FROM roles WHERE nombre = 'Administrador'");
    $adminRoleId = $adminRoleQuery->fetchColumn();
    if (!$adminRoleId) {
        $pdo->exec("INSERT INTO roles (nombre, descripcion) VALUES ('Administrador', 'Acceso total al sistema')");
        $adminRoleId = $pdo->lastInsertId();
    }

    // Assign old admin to new role
    if (in_array('rol', $columns)) {
        $pdo->exec("UPDATE usuarios SET rol_id = $adminRoleId WHERE rol = 'admin' AND rol_id IS NULL");
        $pdo->exec("UPDATE usuarios SET rol_id = $adminRoleId WHERE rol = 'user' AND rol_id IS NULL"); // default assignment
    } else {
        $pdo->exec("UPDATE usuarios SET rol_id = $adminRoleId WHERE rol_id IS NULL");
    }

    // Create base permissions
    $perms = [
        ['usuarios', 'ver', 'Ver listado de usuarios'],
        ['usuarios', 'crear', 'Crear nuevos usuarios'],
        ['usuarios', 'editar', 'Editar usuarios existentes'],
        ['usuarios', 'eliminar', 'Eliminar usuarios'],
        ['roles', 'ver', 'Ver listado de roles'],
        ['roles', 'crear', 'Crear nuevos roles'],
        ['roles', 'editar', 'Editar roles existentes'],
        ['roles', 'eliminar', 'Eliminar roles'],
        ['dashboard', 'ver', 'Ver panel principal']
    ];

    foreach ($perms as $p) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO permisos (modulo, accion, descripcion) VALUES (?, ?, ?)");
        $stmt->execute($p);
    }

    // Assign all permissions to Admin role
    $pdo->exec("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) SELECT $adminRoleId, id FROM permisos");

    // Optional: Drop old 'rol' enum column if you want to cleanly migrate
    if (in_array('rol', $columns)) {
        $pdo->exec("ALTER TABLE usuarios DROP COLUMN rol");
    }

    echo "Base de datos migrada correctamente para la Fase 3.\n";
} catch (PDOException $e) {
    die("Error en migración: " . $e->getMessage());
}
?>
