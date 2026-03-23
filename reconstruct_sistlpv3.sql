SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================================
-- SCRIPT DE RESURRECCIÓN DE BASE DE DATOS SISTLPV3
-- GENERADO AUTOMÁTICAMENTE TRAS CAÍDA DEL MOTOR MYSQL
-- ==========================================================

CREATE DATABASE IF NOT EXISTS sistlpv3_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistlpv3_db;

-- 1. ESTRUCTURAS DE SEGURIDAD Y ROLES
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    UNIQUE KEY mod_acc (modulo, accion)
);

CREATE TABLE IF NOT EXISTS rol_permisos (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    tipo_documento VARCHAR(20) DEFAULT 'DNI',
    numero_documento VARCHAR(50) DEFAULT '',
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    rol_id INT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL
);

-- Insert Roles and Base User (Contraseña generada: admin123)
INSERT INTO roles (id, nombre, descripcion) VALUES (1, 'Administrador', 'Acceso total al sistema');
INSERT INTO usuarios (nombre, apellidos, email, password, rol_id) VALUES ('Administrador', 'Principal', 'admin@admin.com', '$2y$10$.8c9dXNGWO5YGNtVrdpXJeN6uuQoRQ4/xHfYEBR2mlMffCNu8kNgq', 1);

-- Insert Base Permisos
INSERT IGNORE INTO permisos (modulo, accion, descripcion) VALUES 
('usuarios', 'ver', 'Ver listado de usuarios'), ('usuarios', 'crear', 'Crear nuevos usuarios'),
('usuarios', 'editar', 'Editar usuarios existentes'), ('usuarios', 'eliminar', 'Eliminar usuarios'),
('roles', 'ver', 'Ver listado de roles'), ('roles', 'crear', 'Crear nuevos roles'),
('roles', 'editar', 'Editar roles existentes'), ('roles', 'eliminar', 'Eliminar roles'),
('dashboard', 'ver', 'Ver panel principal'),
('clientes', 'ver', 'Ver listado de clientes'), ('clientes', 'crear', 'Crear nuevos clientes'),
('clientes', 'editar', 'Editar clientes existentes'), ('clientes', 'eliminar', 'Eliminar clientes'),
('facturacion_series', 'ver', 'Ver listado de series de facturacion'), ('facturacion_series', 'crear', 'Crear nuevas series de facturacion'),
('facturacion_series', 'editar', 'Editar series de facturacion'), ('facturacion_series', 'eliminar', 'Eliminar series de facturacion'),
('facturacion_emision', 'ver', 'Ver listado de comprobantes emitidos'), ('facturacion_emision', 'crear', 'Emitir nuevos comprobantes'),
('facturacion_emision', 'anular', 'Anular comprobantes'), ('facturacion_emision', 'editar', 'Editar comprobantes diarios'), ('facturacion_emision', 'eliminar', 'Eliminar registros'),
('facturacion_tipo_cambio', 'ver', 'Ver Tipos de Cambio'), ('facturacion_tipo_cambio', 'crear', 'Crear Tipos de Cambio'),
('facturacion_tipo_cambio', 'editar', 'Editar Tipos de Cambio'), ('facturacion_tipo_cambio', 'eliminar', 'Borrar Tipos de Cambio');

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) SELECT 1, id FROM permisos;

-- 2. CONFIGURACIONES EMPRESARIALES DE FIRMA
CREATE TABLE IF NOT EXISTS empresa_config (
    id INT PRIMARY KEY AUTO_INCREMENT, 
    ruc VARCHAR(20), 
    razon_social VARCHAR(255), 
    nombre_comercial VARCHAR(255), 
    direccion TEXT, 
    ubigeo VARCHAR(6), 
    departamento VARCHAR(50), 
    provincia VARCHAR(50), 
    distrito VARCHAR(50), 
    sol_usuario VARCHAR(50), 
    sol_clave VARCHAR(50), 
    certificado_path VARCHAR(255), 
    logo_path VARCHAR(255)
);
INSERT IGNORE INTO empresa_config (id, ruc, razon_social, nombre_comercial, direccion, ubigeo, departamento, provincia, distrito, sol_usuario, sol_clave, certificado_path, logo_path) 
VALUES (1, '20000000001', 'EMPRESA BASE S.A.C.', 'SISTLPV3 ERP', 'AV. CUALQUIERA 123', '150101', 'LIMA', 'LIMA', 'LIMA', 'MODDATOS', 'moddatos', 'data/certs/certificate.pem', '');

-- 3. MÓDULO CLIENTES
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_cliente ENUM('NATURAL', 'EMPRESA') NOT NULL DEFAULT 'NATURAL',
    tipo_documento VARCHAR(20) NOT NULL DEFAULT 'DNI',
    numero_documento VARCHAR(50) NOT NULL UNIQUE,
    razon_social VARCHAR(255) NULL,
    nombres VARCHAR(100) NULL,
    apellidos VARCHAR(100) NULL,
    direccion VARCHAR(255) NULL,
    departamento VARCHAR(100) NULL,
    provincia VARCHAR(100) NULL,
    distrito VARCHAR(100) NULL,
    telefono VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. FACTURACIÓN Y COMPROBANTES V3 + UBL + NC/ND + MÚLTIPLES DIVISAS
CREATE TABLE IF NOT EXISTS series_facturacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_comprobante VARCHAR(50) NOT NULL COMMENT 'BOLETA, FACTURA, NOTA_CREDITO, NOTA_DEBITO',
    serie VARCHAR(4) NOT NULL COMMENT 'B001, F001',
    descripcion VARCHAR(255) NULL COMMENT 'Para qué se destina esta serie',
    correlativo_actual INT NOT NULL DEFAULT 1,
    estado BOOLEAN NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tipo_serie (tipo_comprobante, serie)
);

CREATE TABLE IF NOT EXISTS comprobantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_comprobante VARCHAR(50) NOT NULL COMMENT 'BOLETA, FACTURA, NOTA_CREDITO, NOTA_DEBITO',
    codigo_tipo_documento VARCHAR(2) NULL,
    serie VARCHAR(4) NOT NULL,
    correlativo INT NOT NULL,
    cliente_id INT NOT NULL,
    cliente_numero_documento VARCHAR(20) NULL,
    cliente_razon_social VARCHAR(255) NULL,
    cliente_direccion_completa TEXT NULL,
    comprobante_relacionado_id INT NULL,
    codigo_motivo VARCHAR(2) NULL,
    descripcion_motivo VARCHAR(255) NULL,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NULL,
    moneda VARCHAR(3) NOT NULL DEFAULT 'PEN',
    tipo_cambio DECIMAL(10,4) NULL,
    condicion_pago VARCHAR(50) NOT NULL DEFAULT 'CONTADO',
    dias_credito INT NULL DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    igv DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(20) NOT NULL DEFAULT 'EMITIDO' COMMENT 'EMITIDO, ANULADO',
    estado_sunat ENUM('PENDIENTE', 'ACEPTADO', 'RECHAZADO', 'EXCEPCION') NOT NULL DEFAULT 'PENDIENTE',
    archivo_xml VARCHAR(255) NULL,
    archivo_cdr VARCHAR(255) NULL,
    hash_cpe VARCHAR(255) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_comprobante (tipo_comprobante, serie, correlativo),
    FOREIGN KEY (comprobante_relacionado_id) REFERENCES comprobantes(id)
);

CREATE TABLE IF NOT EXISTS comprobantes_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comprobante_id INT NOT NULL,
    codigo VARCHAR(50) NULL,
    descripcion TEXT NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL DEFAULT 'NIU',
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    importe_total DECIMAL(10,2) NOT NULL,
    igv DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
    FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tipo_cambio (
    fecha DATE PRIMARY KEY,
    compra DECIMAL(10,3) NOT NULL,
    venta DECIMAL(10,3) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SET FOREIGN_KEY_CHECKS = 1;
