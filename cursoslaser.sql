-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 13-04-2025 a las 05:13:00
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cursoslaser`
--
CREATE DATABASE IF NOT EXISTS `cursoslaser` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cursoslaser`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
CREATE TABLE IF NOT EXISTS `auditoria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `tabla` varchar(50) DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `detalle` text,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

DROP TABLE IF EXISTS `aulas`;
CREATE TABLE IF NOT EXISTS `aulas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `lugar` varchar(100) DEFAULT NULL,
  `capacidad` int DEFAULT NULL,
  `videobeam` tinyint(1) NOT NULL DEFAULT '0',
  `computador` tinyint(1) NOT NULL DEFAULT '0',
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `aulas`
--

INSERT INTO `aulas` (`id`, `nombre`, `lugar`, `capacidad`, `videobeam`, `computador`, `creado`, `activa`) VALUES
(1, 'Aula 1', 'Sauma Centro', 20, 1, 1, '2025-03-27 19:10:30', 1),
(2, 'Aula 2', 'Sauma Centro', 20, 1, 1, '2025-03-27 19:10:30', 1),
(3, 'Aula 3', 'Sauma Centro', 20, 1, 1, '2025-03-27 23:35:07', 1),
(4, 'Aula 4', 'Sauma Centro', 20, 1, 1, '2025-03-27 23:49:40', 1),
(5, 'Aula 5', 'Sauma Centro', 20, 1, 1, '2025-03-28 04:26:14', 1),
(6, 'Aula 6', 'Sauma Centro', 20, 1, 1, '2025-03-28 04:26:46', 1),
(7, 'Aula 7', 'Sauma Centro', 15, 1, 1, '2025-03-31 13:23:03', 1),
(9, 'Aula 8', 'Sauma Centro', 20, 0, 0, '2025-03-31 13:39:39', 0),
(10, 'Aula 1', 'Hangar', 20, 1, 1, '2025-03-31 15:45:49', 1),
(11, 'Aula 2', 'Hangar', 20, 1, 1, '2025-03-31 15:46:13', 1),
(12, 'Aula 3', 'Hangar', 12, 1, 0, '2025-03-31 15:46:41', 1),
(13, 'Aula 9', 'Sauma Centro', 22, 0, 0, '2025-04-04 13:30:06', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloques`
--

DROP TABLE IF EXISTS `bloques`;
CREATE TABLE IF NOT EXISTS `bloques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `grupo_id` int NOT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `grupo_id` (`grupo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloque_curso`
--

DROP TABLE IF EXISTS `bloque_curso`;
CREATE TABLE IF NOT EXISTS `bloque_curso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bloque_id` int NOT NULL,
  `curso_id` int NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bloque_id` (`bloque_id`),
  KEY `curso_id` (`curso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificaciones_instructor`
--

DROP TABLE IF EXISTS `certificaciones_instructor`;
CREATE TABLE IF NOT EXISTS `certificaciones_instructor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `instructor_id` int NOT NULL,
  `curso_id` int DEFAULT NULL,
  `curso_nombre` varchar(100) NOT NULL,
  `vencimiento_curso` date NOT NULL,
  `requiere_cert_inac` tinyint(1) DEFAULT '0',
  `vencimiento_inac` date DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `curso_id` (`curso_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `certificaciones_instructor`
--

INSERT INTO `certificaciones_instructor` (`id`, `instructor_id`, `curso_id`, `curso_nombre`, `vencimiento_curso`, `requiere_cert_inac`, `vencimiento_inac`, `creado`, `actualizado`) VALUES
(1, 2, NULL, 'prueba 1', '2026-03-21', 1, '2025-04-24', '2025-03-28 01:07:27', '2025-03-28 01:07:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colores_disponibles`
--

DROP TABLE IF EXISTS `colores_disponibles`;
CREATE TABLE IF NOT EXISTS `colores_disponibles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `color` varchar(7) NOT NULL,
  `disponible` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `color` (`color`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `colores_disponibles`
--

INSERT INTO `colores_disponibles` (`id`, `color`, `disponible`) VALUES
(1, '#FF5733', 1),
(2, '#33B5E5', 1),
(3, '#FFB700', 1),
(4, '#5A68C7', 1),
(5, '#00C851', 0),
(6, '#AA66CC', 0),
(7, '#FF4444', 1),
(8, '#0099CC', 0),
(9, '#9933CC', 1),
(10, '#2BBBAD', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `componente_docente`
--

DROP TABLE IF EXISTS `componente_docente`;
CREATE TABLE IF NOT EXISTS `componente_docente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `instructor_id` int NOT NULL,
  `vencimiento` date NOT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instructor_id` (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `componente_docente`
--

INSERT INTO `componente_docente` (`id`, `instructor_id`, `vencimiento`, `creado`, `actualizado`) VALUES
(1, 2, '2026-03-27', '2025-03-28 01:05:10', '2025-03-28 01:05:10'),
(2, 3, '2025-10-01', '2025-04-03 02:53:30', '2025-04-03 02:53:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coordinaciones`
--

DROP TABLE IF EXISTS `coordinaciones`;
CREATE TABLE IF NOT EXISTS `coordinaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#000000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `coordinaciones`
--

INSERT INTO `coordinaciones` (`id`, `nombre`, `color`) VALUES
(1, 'Coordinación de Tierra', '#00C851'),
(2, 'Coordinación de Vuelo', '#0099CC'),
(3, 'Coordinación de Mantenimiento', '#AA66CC');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

DROP TABLE IF EXISTS `cursos`;
CREATE TABLE IF NOT EXISTS `cursos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text,
  `tipo` enum('Inicial','Periódico','General') NOT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `duracion_horas` int DEFAULT '8',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `nombre`, `descripcion`, `tipo`, `creado`, `duracion_horas`) VALUES
(1, 'Seguridad en Plataforma', '', 'Inicial', '2025-03-27 19:10:30', 8),
(2, 'Primeros Auxilios', '', 'Periódico', '2025-03-27 19:10:30', 4),
(43, 'Inducción Empresarial', '', 'General', '2025-03-29 23:46:07', 6),
(44, 'Descripción de Aeronaves', 'Curso dirigido a personal de atención a la aeronave, para familiarizarse sobre su estructura, accesos y precauciones al estar en ella.', 'Inicial', '2025-04-01 02:13:28', 8),
(45, 'Seguridad Laboral', '', 'Inicial', '2025-04-03 03:23:37', 4),
(47, 'Gestión de la Seguridad Operacional (SMS)', '', 'Inicial', '2025-04-06 02:52:01', 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos_programados`
--

DROP TABLE IF EXISTS `cursos_programados`;
CREATE TABLE IF NOT EXISTS `cursos_programados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_id` int NOT NULL,
  `curso_id` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `aula_id` int DEFAULT NULL,
  `instructor_id` int DEFAULT NULL,
  `coordinacion_id` int DEFAULT NULL,
  `bloque_codigo` varchar(50) DEFAULT NULL,
  `estado` enum('Programado','Confirmado','Cancelado') DEFAULT 'Programado',
  `requiere_notificacion` tinyint(1) DEFAULT '0',
  `fecha_notificacion` date DEFAULT NULL,
  `usuario_creador_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado_instructor` enum('Programado','Confirmado') DEFAULT 'Programado',
  `creado_por` int NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `fecha_confirmacion_instructor` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `curso_id` (`curso_id`),
  KEY `aula_id` (`aula_id`),
  KEY `instructor_id` (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cursos_programados`
--

INSERT INTO `cursos_programados` (`id`, `grupo_id`, `curso_id`, `fecha_inicio`, `fecha_fin`, `hora_inicio`, `hora_fin`, `aula_id`, `instructor_id`, `coordinacion_id`, `bloque_codigo`, `estado`, `requiere_notificacion`, `fecha_notificacion`, `usuario_creador_id`, `created_at`, `updated_at`, `estado_instructor`, `creado_por`, `creado_en`, `actualizado_en`, `fecha_confirmacion_instructor`) VALUES
(1, 1, 1, '2025-03-28', '2025-03-28', '08:00:00', '17:00:00', 1, 2, 1, '358796', 'Programado', 0, NULL, NULL, '2025-03-28 03:34:08', '2025-03-30 00:47:47', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(2, 1, 2, '2025-03-28', '2025-03-28', '08:00:00', '17:00:00', 4, 2, NULL, '358796', 'Programado', 0, NULL, NULL, '2025-03-28 03:41:42', '2025-03-28 03:41:42', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(24, 2, 43, '2025-05-01', '2025-05-02', '08:00:00', '17:00:00', 5, 2, 3, 'BLOQ200', 'Confirmado', 1, '2025-04-30', 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(25, 3, 1, '2025-05-09', '2025-05-12', '08:00:00', '17:00:00', 6, 1, 1, 'BLOQ400', 'Confirmado', 1, '2025-05-08', 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(26, 2, 43, '2025-05-19', '2025-05-22', '08:00:00', '17:00:00', 4, 2, 3, 'BLOQ300', 'Cancelado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(27, 1, 43, '2025-05-09', '2025-05-11', '08:00:00', '17:00:00', 3, 2, 2, 'BLOQ300', 'Cancelado', 1, '2025-05-08', 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(28, 5, 43, '2025-05-10', '2025-05-13', '08:00:00', '17:00:00', 1, 2, 2, 'BLOQ300', 'Cancelado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(29, 2, 2, '2025-04-16', '2025-04-16', '08:00:00', '17:00:00', 5, 9, 3, 'BLOQ100', 'Programado', 1, '2025-04-14', 1, '2025-03-30 05:12:41', '2025-04-06 05:20:19', 'Programado', 0, '2025-04-03 22:34:19', '2025-04-06 01:20:19', NULL),
(31, 5, 1, '2025-05-18', '2025-05-21', '08:00:00', '17:00:00', 2, 2, 3, 'BLOQ400', 'Confirmado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(32, 3, 2, '2025-05-28', '2025-05-29', '08:00:00', '17:00:00', 2, 1, 1, 'BLOQ200', 'Programado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(33, 5, 43, '2025-05-26', '2025-05-28', '08:00:00', '17:00:00', 2, 1, 2, 'BLOQ400', 'Confirmado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(34, 1, 2, '2025-05-02', '2025-05-04', '08:00:00', '17:00:00', 5, 1, 1, 'BLOQ300', 'Cancelado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(36, 4, 2, '2025-05-19', '2025-05-21', '08:00:00', '17:00:00', 1, 1, 3, 'BLOQ100', 'Programado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(37, 2, 1, '2025-05-01', '2025-05-03', '08:00:00', '17:00:00', 3, 1, 2, 'BLOQ300', 'Cancelado', 1, '2025-04-30', 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(40, 2, 1, '2025-05-17', '2025-05-18', '08:00:00', '17:00:00', 6, 2, 3, 'BLOQ100', 'Confirmado', 0, NULL, 1, '2025-03-30 05:12:41', '2025-03-30 05:12:41', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(42, 2, 43, '2025-04-24', '2025-04-24', '08:00:00', '17:00:00', 5, 10, 2, 'BLOQ300', 'Programado', 1, '2025-04-22', 1, '2025-03-30 05:12:41', '2025-04-06 13:33:24', 'Programado', 0, '2025-04-03 22:34:19', '2025-04-06 09:33:24', NULL),
(43, 1, 2, '2025-03-11', '2025-03-05', '08:00:00', '17:00:00', 1, 1, 1, '', 'Programado', 0, NULL, NULL, '2025-03-30 03:54:52', '2025-03-31 15:40:26', 'Confirmado', 0, '2025-04-03 22:34:19', NULL, '2025-03-31 11:40:26'),
(44, 2, 43, '2025-03-27', '2025-03-27', '08:00:00', '17:00:00', 3, 2, 1, NULL, 'Programado', 0, NULL, NULL, '2025-03-30 08:41:20', '2025-03-30 19:54:45', 'Programado', 0, '2025-04-03 22:34:19', NULL, NULL),
(45, 1, 44, '2025-04-01', '2025-04-01', '08:00:00', '17:00:00', 2, 3, 1, NULL, 'Programado', 0, NULL, NULL, '2025-04-01 02:33:28', '2025-04-01 04:51:05', 'Confirmado', 0, '2025-04-03 22:34:19', NULL, '2025-04-01 00:51:05'),
(54, 2, 43, '2025-04-21', '2025-04-21', '08:30:00', '17:00:00', 4, 10, NULL, NULL, 'Programado', 0, NULL, NULL, '2025-04-04 05:30:37', '2025-04-08 02:55:32', 'Confirmado', 3, '2025-04-04 01:30:37', '2025-04-07 22:55:32', '2025-04-07 22:55:32'),
(56, 2, 45, '2025-04-23', '2025-04-23', '08:30:00', '17:00:00', 6, 9, NULL, NULL, 'Programado', 0, NULL, NULL, '2025-04-04 05:30:37', '2025-04-06 08:29:30', '', 3, '2025-04-04 01:30:37', '2025-04-06 04:29:30', NULL),
(57, 1, 44, '2025-04-01', '2025-04-02', '08:30:00', '17:00:00', 4, 6, 1, NULL, 'Programado', 0, NULL, NULL, '2025-04-07 16:10:40', '2025-04-07 16:12:43', 'Confirmado', 0, '2025-04-07 12:10:40', '2025-04-07 12:12:43', '2025-04-07 12:12:43'),
(58, 1, 47, '2025-04-24', '2025-04-25', '08:30:00', '17:00:00', 10, 7, NULL, NULL, 'Programado', 0, NULL, NULL, '2025-04-07 16:17:53', '2025-04-08 01:47:36', '', 3, '2025-04-07 12:17:53', '2025-04-07 21:47:36', NULL),
(59, 1, 1, '2025-04-28', '2025-04-28', '08:30:00', '17:00:00', NULL, NULL, NULL, NULL, 'Programado', 0, NULL, NULL, '2025-04-07 16:17:53', '2025-04-07 16:17:53', '', 3, '2025-04-07 12:17:53', NULL, NULL),
(60, 1, 44, '2025-04-29', '2025-04-29', '08:30:00', '17:00:00', NULL, NULL, NULL, NULL, 'Programado', 0, NULL, NULL, '2025-04-07 16:17:53', '2025-04-07 16:17:53', '', 3, '2025-04-07 12:17:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `feriados`
--

DROP TABLE IF EXISTS `feriados`;
CREATE TABLE IF NOT EXISTS `feriados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `feriados`
--

INSERT INTO `feriados` (`id`, `fecha`, `descripcion`) VALUES
(1, '2025-01-01', 'Año Nuevo'),
(2, '2025-02-24', 'Carnaval'),
(3, '2025-02-25', 'Carnaval'),
(4, '2025-03-19', 'Día de San José'),
(5, '2025-04-17', 'Jueves Santo'),
(6, '2025-04-18', 'Viernes Santo'),
(7, '2025-05-01', 'Día del Trabajador'),
(8, '2025-06-24', 'Batalla de Carabobo'),
(9, '2025-07-05', 'Día de la Independencia'),
(10, '2025-07-24', 'Natalicio de Bolívar'),
(11, '2025-10-12', 'Día de la Resistencia Indígena'),
(12, '2025-12-24', 'Nochebuena'),
(13, '2025-12-25', 'Navidad'),
(14, '2025-12-31', 'Fin de Año');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE IF NOT EXISTS `grupos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `coordinacion_id` int NOT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `coordinacion_id` (`coordinacion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `grupos`
--

INSERT INTO `grupos` (`id`, `nombre`, `coordinacion_id`, `creado`) VALUES
(1, 'Personal de Atención a la Aeronave', 1, '2025-03-28 01:15:04'),
(2, 'Personal Administrativo', 1, '2025-03-28 01:15:04'),
(3, 'Personal de Atención al Pasajero', 1, '2025-03-28 01:15:04'),
(4, 'Personal Avsec', 1, '2025-03-28 01:15:04'),
(5, 'Personal de la Comercial', 1, '2025-03-28 01:15:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_curso`
--

DROP TABLE IF EXISTS `grupo_curso`;
CREATE TABLE IF NOT EXISTS `grupo_curso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_id` int NOT NULL,
  `curso_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupo_id` (`grupo_id`,`curso_id`),
  UNIQUE KEY `grupo_id_2` (`grupo_id`,`curso_id`),
  KEY `curso_id` (`curso_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `grupo_curso`
--

INSERT INTO `grupo_curso` (`id`, `grupo_id`, `curso_id`) VALUES
(5, 1, 1),
(6, 1, 44),
(16, 1, 47),
(3, 2, 2),
(1, 2, 43),
(7, 2, 45),
(14, 2, 47),
(17, 3, 47),
(15, 4, 47),
(18, 5, 47);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructores`
--

DROP TABLE IF EXISTS `instructores`;
CREATE TABLE IF NOT EXISTS `instructores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `vencimiento_documentos` date DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `tipo` enum('interno','externo') NOT NULL DEFAULT 'externo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `instructores`
--

INSERT INTO `instructores` (`id`, `nombre`, `email`, `telefono`, `vencimiento_documentos`, `creado`, `activo`, `tipo`) VALUES
(1, 'Pedro González', 'pgonzalez@correo.com', '04123555037', '2025-08-15', '2025-03-27 19:10:30', 0, 'externo'),
(2, 'Maria Lopez', 'mlopez@correo.com', '04241328300', '2025-10-20', '2025-03-27 19:10:30', 0, 'externo'),
(3, 'José De Gouveia', 'instruccion@gmail.com', '04241328300', NULL, '2025-04-01 02:12:03', 1, 'externo'),
(4, 'Ricardo De Barros', 'rdebarros@laser.com.ve', '04129898037', NULL, '2025-04-03 02:56:40', 1, 'externo'),
(6, 'Andy Bello', 'abello@laser.com.ve', '(0412)365.45.07', NULL, '2025-04-06 01:13:35', 1, 'externo'),
(7, 'Daigel Poncho', 'dponcho@laser.com.ve', '(0424)1924642', NULL, '2025-04-06 01:15:25', 1, 'externo'),
(8, 'Joselyn Hurtado', 'jhurtado@laser.com.ve', '(0416)2396268', NULL, '2025-04-06 01:23:21', 1, 'externo'),
(9, 'Heisy Fernández', 'hfernandez@laser.com.ve', '(0424)2862397', NULL, '2025-04-06 03:03:24', 1, 'externo'),
(10, 'José Conde', 'jconde@laser.com.ve', '', NULL, '2025-04-06 05:21:08', 1, 'externo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructor_curso`
--

DROP TABLE IF EXISTS `instructor_curso`;
CREATE TABLE IF NOT EXISTS `instructor_curso` (
  `instructor_id` int NOT NULL,
  `curso_id` int NOT NULL,
  PRIMARY KEY (`instructor_id`,`curso_id`),
  KEY `curso_id` (`curso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `instructor_curso`
--

INSERT INTO `instructor_curso` (`instructor_id`, `curso_id`) VALUES
(1, 1),
(6, 1),
(1, 2),
(9, 2),
(10, 43),
(2, 44),
(3, 44),
(6, 44),
(9, 45),
(7, 47);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

DROP TABLE IF EXISTS `mensajes`;
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `remitente_id` int DEFAULT NULL,
  `destinatario_id` int NOT NULL,
  `curso_id` int DEFAULT NULL,
  `asunto` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuerpo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leido` tinyint(1) DEFAULT '0',
  `archivado` tinyint(1) DEFAULT '0',
  `creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `remitente_id` (`remitente_id`),
  KEY `destinatario_id` (`destinatario_id`),
  KEY `curso_id` (`curso_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `remitente_id`, `destinatario_id`, `curso_id`, `asunto`, `cuerpo`, `leido`, `archivado`, `creado`, `tipo`, `titulo`, `eliminado`) VALUES
(1, 2, 3, NULL, 'Confirmación de curso pendiente', 'Debes confirmar tu asistencia al curso de Seguridad', 0, 0, '2025-04-07 23:57:14', 'confirmacion', NULL, 1),
(2, 2, 3, NULL, 'Recordatorio', 'Recuerda confirmar tu asistencia.', 0, 1, '2025-04-08 00:06:14', 'notificacion', NULL, 1),
(3, 1, 3, NULL, 'Curso asignado', 'Has sido asignado a un nuevo curso.', 1, 0, '2025-04-08 00:06:14', 'confirmacion', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programaciones`
--

DROP TABLE IF EXISTS `programaciones`;
CREATE TABLE IF NOT EXISTS `programaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `curso_id` int DEFAULT NULL,
  `aula_id` int DEFAULT NULL,
  `instructor_id` int DEFAULT NULL,
  `grupo_destinatario` varchar(255) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `estado_instructor` enum('Programado','Confirmado') DEFAULT 'Programado',
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `curso_id` (`curso_id`),
  KEY `aula_id` (`aula_id`),
  KEY `instructor_id` (`instructor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `creado`) VALUES
(1, 'Administrador', 'Acceso total', '2025-03-27 19:01:38'),
(2, 'Coordinador', 'Gestiona programación', '2025-03-27 19:01:38'),
(3, 'Analista', 'Modifica registros, no borra', '2025-03-27 19:01:38'),
(4, 'Instructor', 'Confirma asistencia, visualiza agenda', '2025-03-27 19:01:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `rol_id` int DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario` varchar(50) NOT NULL,
  `coordinacion_id` int DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '0',
  `requiere_cambio` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  KEY `fk_coordinacion_usuario` (`coordinacion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `foto`, `rol_id`, `creado`, `usuario`, `coordinacion_id`, `activo`, `requiere_cambio`) VALUES
(1, 'José Administrador', 'admin@cursoslaser.com', '$2y$10$abcdefghijk12345678901234567890abcdefghijklm', NULL, 1, '2025-03-27 19:10:30', 'user1', NULL, 1, 0),
(2, 'Luis Coordinador', 'coordinador@cursoslaser.com', '$2y$10$abcdefghijk12345678901234567890abcdefghijklm', NULL, 2, '2025-03-27 19:10:30', 'user2', NULL, 0, 0),
(3, 'José', 'instruccion@gmail.com', '$2y$12$NfDz/oTg.T8pZDkFxqL7LuYl0i2vSrfkTDWuM.NAIhIOityVxX.fq', '67e5cd8d4670c_1621048997655.jpeg', 1, '2025-03-27 20:37:00', 'dego', 1, 1, 0),
(4, 'joseito', 'jdegouveia@laser.com.ve', '$2y$12$iKax6MYmfhfoR9.e8X2b3epdJTquirE3q8xxBxpOn0c06RZA2b35.', '67ea8fcabea58_avatar-default.png', 2, '2025-03-27 21:18:41', 'user3', 1, 1, 0),
(5, 'José Conde', 'jconde@laser.com.ve', '$2y$10$J.0OjP6MzJJNt1i.5aWBTecFtJ/1xc9Q0AQuDhBlXDE9DlXs0A1zu', NULL, 2, '2025-04-08 03:02:56', 'Conde', 1, 1, 0);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `bloques`
--
ALTER TABLE `bloques`
  ADD CONSTRAINT `bloques_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`);

--
-- Filtros para la tabla `bloque_curso`
--
ALTER TABLE `bloque_curso`
  ADD CONSTRAINT `bloque_curso_ibfk_1` FOREIGN KEY (`bloque_id`) REFERENCES `bloques` (`id`),
  ADD CONSTRAINT `bloque_curso_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `certificaciones_instructor`
--
ALTER TABLE `certificaciones_instructor`
  ADD CONSTRAINT `certificaciones_instructor_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructores` (`id`),
  ADD CONSTRAINT `certificaciones_instructor_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `componente_docente`
--
ALTER TABLE `componente_docente`
  ADD CONSTRAINT `componente_docente_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructores` (`id`);

--
-- Filtros para la tabla `cursos_programados`
--
ALTER TABLE `cursos_programados`
  ADD CONSTRAINT `cursos_programados_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `cursos_programados_ibfk_2` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`),
  ADD CONSTRAINT `cursos_programados_ibfk_3` FOREIGN KEY (`instructor_id`) REFERENCES `instructores` (`id`);

--
-- Filtros para la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD CONSTRAINT `grupos_ibfk_1` FOREIGN KEY (`coordinacion_id`) REFERENCES `coordinaciones` (`id`);

--
-- Filtros para la tabla `grupo_curso`
--
ALTER TABLE `grupo_curso`
  ADD CONSTRAINT `grupo_curso_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`),
  ADD CONSTRAINT `grupo_curso_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `instructor_curso`
--
ALTER TABLE `instructor_curso`
  ADD CONSTRAINT `instructor_curso_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructores` (`id`),
  ADD CONSTRAINT `instructor_curso_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `programaciones`
--
ALTER TABLE `programaciones`
  ADD CONSTRAINT `programaciones_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `programaciones_ibfk_2` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`),
  ADD CONSTRAINT `programaciones_ibfk_3` FOREIGN KEY (`instructor_id`) REFERENCES `instructores` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_coordinacion_usuario` FOREIGN KEY (`coordinacion_id`) REFERENCES `coordinaciones` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
