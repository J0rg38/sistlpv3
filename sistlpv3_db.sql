/*
 Navicat Premium Dump SQL

 Source Server         : Local
 Source Server Type    : MySQL
 Source Server Version : 100427 (10.4.27-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : sistlpv3_db

 Target Server Type    : MySQL
 Target Server Version : 100427 (10.4.27-MariaDB)
 File Encoding         : 65001

 Date: 23/03/2026 16:56:32
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for clientes
-- ----------------------------
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_cliente` enum('NATURAL','EMPRESA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NATURAL',
  `tipo_documento` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DNI',
  `numero_documento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nombres` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `apellidos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `direccion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `departamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `provincia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `distrito` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero_documento`(`numero_documento` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of clientes
-- ----------------------------
INSERT INTO `clientes` VALUES (1, 'NATURAL', 'DNI', '73965086', NULL, 'JORGE', 'CHECYA', 'AV SAN MIGUEL DE PIURA 211', 'AREQUIPA', 'AREQUIPA', 'JACOBO HUNTER', '950584817', 'jorge@limabikes.com', '2026-03-23 11:15:10');
INSERT INTO `clientes` VALUES (2, 'EMPRESA', 'RUC', '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', NULL, NULL, 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL', 'AREQUIPA', 'AREQUIPA', 'AREQUIPA', '', '', '2026-03-23 12:45:55');
INSERT INTO `clientes` VALUES (3, 'NATURAL', 'DNI', '29555701', NULL, 'MARIA TERESA', 'CHECYA CONDORI', 'AV SAN MIGUEL DE PIURA 211', 'AREQUIPA', 'AREQUIPA', 'JACOBO HUNTER', '958684910', '', '2026-03-23 16:54:28');

-- ----------------------------
-- Table structure for comprobantes
-- ----------------------------
DROP TABLE IF EXISTS `comprobantes`;
CREATE TABLE `comprobantes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_comprobante` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'BOLETA, FACTURA, NOTA_CREDITO, NOTA_DEBITO',
  `codigo_tipo_documento` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `serie` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `correlativo` int NOT NULL,
  `cliente_id` int NOT NULL,
  `cliente_numero_documento` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cliente_razon_social` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cliente_direccion_completa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `comprobante_relacionado_id` int NULL DEFAULT NULL,
  `codigo_motivo` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descripcion_motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NULL DEFAULT NULL,
  `moneda` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PEN',
  `tipo_cambio` decimal(10, 4) NULL DEFAULT NULL,
  `condicion_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CONTADO',
  `dias_credito` int NULL DEFAULT 0,
  `subtotal` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `igv` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `total` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EMITIDO' COMMENT 'EMITIDO, ANULADO',
  `estado_sunat` enum('PENDIENTE','ACEPTADO','RECHAZADO','EXCEPCION') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `archivo_xml` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `archivo_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hash_cpe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tiene_detraccion` tinyint(1) NULL DEFAULT 0,
  `codigo_detraccion` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `porcentaje_detraccion` decimal(5, 2) NULL DEFAULT NULL,
  `monto_detraccion` decimal(10, 2) NULL DEFAULT NULL,
  `tiene_retencion` tinyint(1) NULL DEFAULT 0,
  `porcentaje_retencion` decimal(5, 2) NULL DEFAULT NULL,
  `monto_retencion` decimal(10, 2) NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_comprobante`(`tipo_comprobante` ASC, `serie` ASC, `correlativo` ASC) USING BTREE,
  INDEX `comprobante_relacionado_id`(`comprobante_relacionado_id` ASC) USING BTREE,
  CONSTRAINT `comprobantes_ibfk_1` FOREIGN KEY (`comprobante_relacionado_id`) REFERENCES `comprobantes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comprobantes
-- ----------------------------
INSERT INTO `comprobantes` VALUES (1, 'BOLETA', '03', 'B001', 1, 1, '73965086', 'JORGE CHECYA', 'AV SAN MIGUEL DE PIURA 211 - AREQUIPA - AREQUIPA - JACOBO HUNTER', NULL, '', '', '2026-03-23', '2026-03-30', 'USD', 3.4210, 'CREDITO', 7, 254.24, 45.76, 300.00, 'EMITIDO', 'ACEPTADO', '20000000001-03-B001-1.xml', 'R-20000000001-03-B001-1.zip', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-23 11:18:07');
INSERT INTO `comprobantes` VALUES (2, 'BOLETA', '03', 'B001', 2, 1, '73965086', 'JORGE CHECYA', 'AV SAN MIGUEL DE PIURA 211 - AREQUIPA - AREQUIPA - JACOBO HUNTER', NULL, '', '', '2026-03-23', '2026-03-30', 'PEN', NULL, 'CREDITO', 7, 381.36, 68.64, 450.00, 'EMITIDO', 'ACEPTADO', '20000000001-03-B001-2.xml', 'R-20000000001-03-B001-2.zip', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-23 11:18:51');
INSERT INTO `comprobantes` VALUES (3, 'NOTA_CREDITO', '07', 'B001', 1, 1, '73965086', 'JORGE CHECYA', 'AV SAN MIGUEL DE PIURA 211 - AREQUIPA - AREQUIPA - JACOBO HUNTER', 2, '04', 'Descuento global', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 169.49, 30.51, 200.00, 'EMITIDO', 'ACEPTADO', '20000000001-07-B001-1.xml', 'R-20000000001-07-B001-1.zip', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-23 11:22:23');
INSERT INTO `comprobantes` VALUES (4, 'NOTA_CREDITO', '07', 'B001', 2, 1, '73965086', 'JORGE CHECYA', 'AV SAN MIGUEL DE PIURA 211 - AREQUIPA - AREQUIPA - JACOBO HUNTER', 2, '01', 'Anulación de la operación', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 211.86, 38.14, 250.00, 'EMITIDO', 'ACEPTADO', '20000000001-07-B001-2.xml', 'R-20000000001-07-B001-2.zip', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-23 11:23:25');
INSERT INTO `comprobantes` VALUES (5, 'NOTA_DEBITO', '08', 'B001', 1, 1, '73965086', 'JORGE CHECYA', 'AV SAN MIGUEL DE PIURA 211 - AREQUIPA - AREQUIPA - JACOBO HUNTER', 2, '02', 'Aumento en el valor', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 211.86, 38.14, 250.00, 'EMITIDO', 'ACEPTADO', '20000000001-08-B001-1.xml', 'R-20000000001-08-B001-1.zip', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-23 11:24:11');
INSERT INTO `comprobantes` VALUES (6, 'FACTURA', '01', 'F001', 1, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 847.46, 152.54, 1000.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-1.xml', 'R-20000000001-01-F001-1.zip', NULL, 1, '020', 12.00, 120.00, 0, 3.00, 0.00, '2026-03-23 13:01:44');
INSERT INTO `comprobantes` VALUES (7, 'FACTURA', '01', 'F001', 2, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 677.97, 122.03, 800.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-2.xml', 'R-20000000001-01-F001-2.zip', NULL, 0, '037', 12.00, 0.00, 1, 3.00, 24.00, '2026-03-23 13:09:27');
INSERT INTO `comprobantes` VALUES (8, 'FACTURA', '01', 'F001', 3, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 677.97, 122.03, 800.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-3.xml', 'R-20000000001-01-F001-3.zip', NULL, 0, '037', 12.00, 0.00, 1, 3.00, 24.00, '2026-03-23 13:23:31');
INSERT INTO `comprobantes` VALUES (9, 'FACTURA', '01', 'F001', 4, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 677.97, 122.03, 800.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-4.xml', 'R-20000000001-01-F001-4.zip', NULL, 0, '037', 12.00, 0.00, 1, 3.00, 24.00, '2026-03-23 13:29:50');
INSERT INTO `comprobantes` VALUES (10, 'FACTURA', '01', 'F001', 5, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-30', 'USD', 3.4210, 'CREDITO', 7, 550.85, 99.15, 650.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-5.xml', 'R-20000000001-01-F001-5.zip', NULL, 1, '037', 12.00, 78.00, 0, 3.00, 0.00, '2026-03-23 14:48:09');
INSERT INTO `comprobantes` VALUES (11, 'FACTURA', '01', 'F001', 6, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-04-07', 'USD', 3.4210, 'CREDITO', 15, 677.97, 122.03, 800.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-6.xml', 'R-20000000001-01-F001-6.zip', NULL, 1, '019', 12.00, 96.00, 0, 3.00, 0.00, '2026-03-23 15:00:00');
INSERT INTO `comprobantes` VALUES (12, 'FACTURA', '01', 'F001', 7, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-30', 'USD', 3.4210, 'CREDITO', 7, 567.80, 102.20, 670.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-7.xml', 'R-20000000001-01-F001-7.zip', NULL, 0, '037', 12.00, 0.00, 1, 3.00, 20.10, '2026-03-23 15:05:36');
INSERT INTO `comprobantes` VALUES (13, 'FACTURA', '01', 'F001', 8, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'USD', 3.4210, 'CONTADO', 0, 762.71, 137.29, 900.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-8.xml', 'R-20000000001-01-F001-8.zip', NULL, 0, '037', 12.00, 0.00, 1, 3.00, 27.00, '2026-03-23 15:08:44');
INSERT INTO `comprobantes` VALUES (14, 'FACTURA', '01', 'F001', 9, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 161.02, 28.98, 190.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-9.xml', 'R-20000000001-01-F001-9.zip', NULL, 0, '037', 12.00, 0.00, 0, 3.00, 0.00, '2026-03-23 15:57:10');
INSERT INTO `comprobantes` VALUES (15, 'FACTURA', '01', 'F001', 10, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 127.12, 22.88, 150.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-10.xml', 'R-20000000001-01-F001-10.zip', NULL, 0, '037', 12.00, 0.00, 0, 3.00, 0.00, '2026-03-23 16:24:32');
INSERT INTO `comprobantes` VALUES (16, 'FACTURA', '01', 'F001', 11, 2, '20454537743', 'AUTOMOTRIZ CISNE S.R.L.', 'AV JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL - AREQUIPA - AREQUIPA - AREQUIPA', NULL, '', '', '2026-03-23', '2026-03-23', 'PEN', NULL, 'CONTADO', 0, 243.22, 43.78, 287.00, 'EMITIDO', 'ACEPTADO', '20000000001-01-F001-11.xml', 'R-20000000001-01-F001-11.zip', NULL, 0, '037', 12.00, 0.00, 0, 3.00, 0.00, '2026-03-23 16:33:32');

-- ----------------------------
-- Table structure for comprobantes_items
-- ----------------------------
DROP TABLE IF EXISTS `comprobantes_items`;
CREATE TABLE `comprobantes_items`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `comprobante_id` int NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NIU',
  `cantidad` decimal(10, 2) NOT NULL,
  `precio_unitario` decimal(10, 2) NOT NULL,
  `descuento` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `importe_total` decimal(10, 2) NOT NULL,
  `igv` decimal(10, 4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `comprobante_id`(`comprobante_id` ASC) USING BTREE,
  CONSTRAINT `comprobantes_items_ibfk_1` FOREIGN KEY (`comprobante_id`) REFERENCES `comprobantes` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comprobantes_items
-- ----------------------------
INSERT INTO `comprobantes_items` VALUES (1, 1, 'PRUEBA', 'EMISION DE PRUEBA PARA JORGHINO', 'NIU', 1.00, 300.00, 0.00, 300.00, 45.7627);
INSERT INTO `comprobantes_items` VALUES (2, 2, 'PRUEBA', 'PRUEBA GAAAAAA', 'NIU', 1.00, 450.00, 0.00, 450.00, 68.6441);
INSERT INTO `comprobantes_items` VALUES (3, 3, 'PRUEBA', 'PRUEBA GAAAAAA', 'NIU', 1.00, 200.00, 0.00, 200.00, 30.5085);
INSERT INTO `comprobantes_items` VALUES (4, 4, 'PRUEBA', 'PRUEBA GAAAAAA', 'NIU', 1.00, 250.00, 0.00, 250.00, 38.1356);
INSERT INTO `comprobantes_items` VALUES (5, 5, 'PRUEBA', 'PRUEBA GAAAAAA', 'NIU', 1.00, 250.00, 0.00, 250.00, 38.1356);
INSERT INTO `comprobantes_items` VALUES (6, 6, 'PRU', 'MATENIMIENTO Y REPARACION DE CORAZONES', 'NIU', 1.00, 1000.00, 0.00, 1000.00, 152.5424);
INSERT INTO `comprobantes_items` VALUES (7, 7, 'prueba', 'codigo de prueba', 'NIU', 1.00, 800.00, 0.00, 800.00, 122.0339);
INSERT INTO `comprobantes_items` VALUES (8, 8, 'gaa', 'gaaa', 'NIU', 1.00, 800.00, 0.00, 800.00, 122.0339);
INSERT INTO `comprobantes_items` VALUES (9, 9, 'jh', 'lkjhykuhlkjh', 'NIU', 1.00, 800.00, 0.00, 800.00, 122.0339);
INSERT INTO `comprobantes_items` VALUES (10, 10, 'gaaaa', 'gaaaaaaaaa', 'NIU', 1.00, 650.00, 0.00, 650.00, 99.1525);
INSERT INTO `comprobantes_items` VALUES (11, 11, 'ga', 'gaaaaaaaaaaaaaaaaaaaa', 'NIU', 1.00, 800.00, 0.00, 800.00, 122.0339);
INSERT INTO `comprobantes_items` VALUES (12, 12, 'fffff', 'recontra f', 'NIU', 1.00, 670.00, 0.00, 670.00, 102.2034);
INSERT INTO `comprobantes_items` VALUES (13, 13, 'sadad', 'saddasdasd', 'NIU', 1.00, 900.00, 0.00, 900.00, 137.2881);
INSERT INTO `comprobantes_items` VALUES (14, 14, 'fffff', 'ffffffffffffff', 'NIU', 1.00, 200.00, 10.00, 190.00, 28.9831);
INSERT INTO `comprobantes_items` VALUES (15, 15, 'asdasd', 'asdadada', 'NIU', 1.00, 200.00, 50.00, 150.00, 22.8814);
INSERT INTO `comprobantes_items` VALUES (16, 16, 'gaaa', 'gaaahhhhh', 'NIU', 1.00, 530.22, 243.22, 287.00, 43.7797);

-- ----------------------------
-- Table structure for empresa_config
-- ----------------------------
DROP TABLE IF EXISTS `empresa_config`;
CREATE TABLE `empresa_config`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `razon_social` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nombre_comercial` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ubigeo` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `departamento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `provincia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `distrito` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sol_usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sol_clave` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `certificado_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `logo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cuenta_banco_nacion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of empresa_config
-- ----------------------------
INSERT INTO `empresa_config` VALUES (1, '20000000001', 'TRANSPORTES TOMAS LINARES S.A.C.', 'TRANSPORTES TOMAS LINARES', 'AV. JACINTO IBAÑEZ 490 PARQUE INDUSTRIAL', '04001', 'AREQUIPA', 'AREQUIPA', 'AREQUIPA', 'MODDATOS', 'moddatos', 'data/certs/certificate.pem', 'data/img/logo_factura.png', '01546669004');

-- ----------------------------
-- Table structure for permisos
-- ----------------------------
DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `modulo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `mod_acc`(`modulo` ASC, `accion` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 27 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of permisos
-- ----------------------------
INSERT INTO `permisos` VALUES (1, 'usuarios', 'ver', 'Ver listado de usuarios');
INSERT INTO `permisos` VALUES (2, 'usuarios', 'crear', 'Crear nuevos usuarios');
INSERT INTO `permisos` VALUES (3, 'usuarios', 'editar', 'Editar usuarios existentes');
INSERT INTO `permisos` VALUES (4, 'usuarios', 'eliminar', 'Eliminar usuarios');
INSERT INTO `permisos` VALUES (5, 'roles', 'ver', 'Ver listado de roles');
INSERT INTO `permisos` VALUES (6, 'roles', 'crear', 'Crear nuevos roles');
INSERT INTO `permisos` VALUES (7, 'roles', 'editar', 'Editar roles existentes');
INSERT INTO `permisos` VALUES (8, 'roles', 'eliminar', 'Eliminar roles');
INSERT INTO `permisos` VALUES (9, 'dashboard', 'ver', 'Ver panel principal');
INSERT INTO `permisos` VALUES (10, 'clientes', 'ver', 'Ver listado de clientes');
INSERT INTO `permisos` VALUES (11, 'clientes', 'crear', 'Crear nuevos clientes');
INSERT INTO `permisos` VALUES (12, 'clientes', 'editar', 'Editar clientes existentes');
INSERT INTO `permisos` VALUES (13, 'clientes', 'eliminar', 'Eliminar clientes');
INSERT INTO `permisos` VALUES (14, 'facturacion_series', 'ver', 'Ver listado de series de facturacion');
INSERT INTO `permisos` VALUES (15, 'facturacion_series', 'crear', 'Crear nuevas series de facturacion');
INSERT INTO `permisos` VALUES (16, 'facturacion_series', 'editar', 'Editar series de facturacion');
INSERT INTO `permisos` VALUES (17, 'facturacion_series', 'eliminar', 'Eliminar series de facturacion');
INSERT INTO `permisos` VALUES (18, 'facturacion_emision', 'ver', 'Ver listado de comprobantes emitidos');
INSERT INTO `permisos` VALUES (19, 'facturacion_emision', 'crear', 'Emitir nuevos comprobantes');
INSERT INTO `permisos` VALUES (20, 'facturacion_emision', 'anular', 'Anular comprobantes');
INSERT INTO `permisos` VALUES (21, 'facturacion_emision', 'editar', 'Editar comprobantes diarios');
INSERT INTO `permisos` VALUES (22, 'facturacion_emision', 'eliminar', 'Eliminar registros');
INSERT INTO `permisos` VALUES (23, 'facturacion_tipo_cambio', 'ver', 'Ver Tipos de Cambio');
INSERT INTO `permisos` VALUES (24, 'facturacion_tipo_cambio', 'crear', 'Crear Tipos de Cambio');
INSERT INTO `permisos` VALUES (25, 'facturacion_tipo_cambio', 'editar', 'Editar Tipos de Cambio');
INSERT INTO `permisos` VALUES (26, 'facturacion_tipo_cambio', 'eliminar', 'Borrar Tipos de Cambio');

-- ----------------------------
-- Table structure for rol_permisos
-- ----------------------------
DROP TABLE IF EXISTS `rol_permisos`;
CREATE TABLE `rol_permisos`  (
  `rol_id` int NOT NULL,
  `permiso_id` int NOT NULL,
  PRIMARY KEY (`rol_id`, `permiso_id`) USING BTREE,
  INDEX `permiso_id`(`permiso_id` ASC) USING BTREE,
  CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `rol_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of rol_permisos
-- ----------------------------
INSERT INTO `rol_permisos` VALUES (1, 1);
INSERT INTO `rol_permisos` VALUES (1, 2);
INSERT INTO `rol_permisos` VALUES (1, 3);
INSERT INTO `rol_permisos` VALUES (1, 4);
INSERT INTO `rol_permisos` VALUES (1, 5);
INSERT INTO `rol_permisos` VALUES (1, 6);
INSERT INTO `rol_permisos` VALUES (1, 7);
INSERT INTO `rol_permisos` VALUES (1, 8);
INSERT INTO `rol_permisos` VALUES (1, 9);
INSERT INTO `rol_permisos` VALUES (1, 10);
INSERT INTO `rol_permisos` VALUES (1, 11);
INSERT INTO `rol_permisos` VALUES (1, 12);
INSERT INTO `rol_permisos` VALUES (1, 13);
INSERT INTO `rol_permisos` VALUES (1, 14);
INSERT INTO `rol_permisos` VALUES (1, 15);
INSERT INTO `rol_permisos` VALUES (1, 16);
INSERT INTO `rol_permisos` VALUES (1, 17);
INSERT INTO `rol_permisos` VALUES (1, 18);
INSERT INTO `rol_permisos` VALUES (1, 19);
INSERT INTO `rol_permisos` VALUES (1, 20);
INSERT INTO `rol_permisos` VALUES (1, 21);
INSERT INTO `rol_permisos` VALUES (1, 22);
INSERT INTO `rol_permisos` VALUES (1, 23);
INSERT INTO `rol_permisos` VALUES (1, 24);
INSERT INTO `rol_permisos` VALUES (1, 25);
INSERT INTO `rol_permisos` VALUES (1, 26);

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 'Administrador', 'Acceso total al sistema', '2026-03-23 11:03:02');

-- ----------------------------
-- Table structure for series_facturacion
-- ----------------------------
DROP TABLE IF EXISTS `series_facturacion`;
CREATE TABLE `series_facturacion`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_comprobante` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'BOLETA, FACTURA, NOTA_CREDITO, NOTA_DEBITO',
  `serie` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'B001, F001',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Para qué se destina esta serie',
  `correlativo_actual` int NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_tipo_serie`(`tipo_comprobante` ASC, `serie` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of series_facturacion
-- ----------------------------
INSERT INTO `series_facturacion` VALUES (1, 'BOLETA', 'B001', 'VENTA DE TODO', 3, 1, '2026-03-23 11:16:32', '2026-03-23 11:18:51');
INSERT INTO `series_facturacion` VALUES (2, 'FACTURA', 'F001', 'VENTA DE TODO', 12, 1, '2026-03-23 11:16:41', '2026-03-23 16:33:32');
INSERT INTO `series_facturacion` VALUES (3, 'NOTA_CREDITO', 'B001', 'VENTA DE TODO', 3, 1, '2026-03-23 11:16:50', '2026-03-23 11:23:25');
INSERT INTO `series_facturacion` VALUES (4, 'NOTA_CREDITO', 'F001', 'VENTA DE TODO', 1, 1, '2026-03-23 11:16:59', '2026-03-23 11:16:59');
INSERT INTO `series_facturacion` VALUES (5, 'NOTA_DEBITO', 'B001', 'VENTA DE TODO', 2, 1, '2026-03-23 11:17:10', '2026-03-23 11:24:11');
INSERT INTO `series_facturacion` VALUES (6, 'NOTA_DEBITO', 'F001', 'VENTA DE TODO', 1, 1, '2026-03-23 11:17:18', '2026-03-23 11:17:18');

-- ----------------------------
-- Table structure for tipo_cambio
-- ----------------------------
DROP TABLE IF EXISTS `tipo_cambio`;
CREATE TABLE `tipo_cambio`  (
  `fecha` date NOT NULL,
  `compra` decimal(10, 3) NOT NULL,
  `venta` decimal(10, 3) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`fecha`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tipo_cambio
-- ----------------------------
INSERT INTO `tipo_cambio` VALUES ('2026-03-23', 3.421, 3.421, '2026-03-23 11:16:19');

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'DNI',
  `numero_documento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NULL DEFAULT 1,
  `rol_id` int NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE,
  INDEX `fk_user_rol`(`rol_id` ASC) USING BTREE,
  CONSTRAINT `fk_user_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES (1, 'Administrador', 'Principal', 'DNI', '', 'admin@admin.com', '$2y$10$.8c9dXNGWO5YGNtVrdpXJeN6uuQoRQ4/xHfYEBR2mlMffCNu8kNgq', 1, 1, '2026-03-23 11:03:02');
INSERT INTO `usuarios` VALUES (2, 'Jorge', 'Quispe', 'DNI', '73965086', 'jl.quispe@cisne.com.pe', '$2y$10$7/qZfj2iy4HdxmASwKLUde2L8Gmd.11RJN/kETBpwFM4G/wp.z2ne', 1, 1, '2026-03-23 11:14:33');

SET FOREIGN_KEY_CHECKS = 1;
