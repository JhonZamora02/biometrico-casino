-- ============================================================
--  Base de datos: db_biometrico_casino
--  Sistema Biométrico — Casino
--  MariaDB / MySQL 10.4+
--  Huella: se guarda la RUTA de la imagen JPG en disco
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `db_biometrico_casino`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `db_biometrico_casino`;

-- ============================================================
--  TABLA: empleados
--  `huella` guarda la ruta relativa al JPG capturado,
--  ej: huellas_img/huella_10000001.jpg
-- ============================================================
CREATE TABLE `empleados` (
  `cedula`   int(15)      NOT NULL,
  `nombre`   varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `correo`   varchar(255) NOT NULL,
  `area`     varchar(255) NOT NULL,
  `huella`   varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos de ejemplo
INSERT INTO `empleados` (`cedula`,`nombre`,`apellido`,`correo`,`area`,`huella`) VALUES
(10000001,'Carlos',  'Ramírez', 'carlos.ramirez@casino.com', 'Administración',''),
(10000002,'María',   'González','maria.gonzalez@casino.com', 'Cocina',        ''),
(10000003,'Andrés',  'López',   'andres.lopez@casino.com',   'Seguridad',     ''),
(10000004,'Patricia','Torres',  'patricia.torres@casino.com','Sistemas',      ''),
(10000005,'Luis',    'Herrera', 'luis.herrera@casino.com',   'Casino',        '');

-- ============================================================
--  TABLA: asistencias
-- ============================================================
CREATE TABLE `asistencias` (
  `cedula_id_cedula` int(15) NOT NULL,
  `fecha`            date    NOT NULL,
  `hora`             time    NOT NULL,
  KEY `fk_asistencias_empleado` (`cedula_id_cedula`),
  CONSTRAINT `fk_asistencias_empleado`
    FOREIGN KEY (`cedula_id_cedula`) REFERENCES `empleados`(`cedula`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos de ejemplo
INSERT INTO `asistencias` (`cedula_id_cedula`,`fecha`,`hora`) VALUES
(10000001, CURDATE(), '08:15:00'),
(10000002, CURDATE(), '09:02:30'),
(10000003, CURDATE(), '07:50:00');

COMMIT;
