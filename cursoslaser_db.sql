-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 15-04-2025 a las 19:25:31
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
-- Base de datos: `cursoslaser_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditorias`
--

DROP TABLE IF EXISTS `auditorias`;
CREATE TABLE IF NOT EXISTS `auditorias` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `accion` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditorias`
--

INSERT INTO `auditorias` (`id`, `user_id`, `accion`, `descripcion`, `ip`, `created_at`, `updated_at`) VALUES
(1, 1, 'Actualización de usuario', 'Se actualizó el usuario JOSÉ con el rol administrador', '::1', '2025-04-15 02:55:45', '2025-04-15 02:55:45'),
(2, 1, 'Actualización de usuario', 'Se actualizó el usuario JOSÉ con el rol administrador', '::1', '2025-04-15 03:40:37', '2025-04-15 03:40:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

DROP TABLE IF EXISTS `aulas`;
CREATE TABLE IF NOT EXISTS `aulas` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lugar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacidad` int NOT NULL,
  `pizarra` tinyint(1) NOT NULL DEFAULT '0',
  `computadora` tinyint(1) NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `videobeam` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `aulas`
--

INSERT INTO `aulas` (`id`, `nombre`, `lugar`, `capacidad`, `pizarra`, `computadora`, `activa`, `videobeam`, `created_at`, `updated_at`) VALUES
(1, 'Aula 1 - Sauma', 'Sauma Centro', 20, 0, 1, 1, 1, '2025-04-14 22:04:59', '2025-04-14 22:04:59'),
(2, 'Aula 1 - Hangar', 'Hangar Laser', 22, 0, 1, 1, 1, '2025-04-14 22:10:28', '2025-04-14 22:10:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendario_instructor`
--

DROP TABLE IF EXISTS `calendario_instructor`;
CREATE TABLE IF NOT EXISTS `calendario_instructor` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint UNSIGNED NOT NULL,
  `curso_id` bigint UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('programado','confirmado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'programado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendario_instructor_instructor_id_foreign` (`instructor_id`),
  KEY `calendario_instructor_curso_id_foreign` (`curso_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colores_disponibles`
--

DROP TABLE IF EXISTS `colores_disponibles`;
CREATE TABLE IF NOT EXISTS `colores_disponibles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `colores_disponibles_color_unique` (`color`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `colores_disponibles`
--

INSERT INTO `colores_disponibles` (`id`, `color`, `disponible`, `created_at`, `updated_at`) VALUES
(1, '#FF0000', 0, NULL, NULL),
(2, '#007BFF', 0, NULL, NULL),
(3, '#28A745', 0, NULL, NULL),
(4, '#FFC107', 1, NULL, NULL),
(5, '#6610F2', 1, NULL, NULL),
(6, '#E83E8C', 1, NULL, NULL),
(7, '#FD7E14', 0, NULL, NULL),
(8, '#20C997', 1, NULL, NULL),
(9, '#6F42C1', 1, NULL, NULL),
(10, '#17A2B8', 1, NULL, NULL),
(11, '#343A40', 1, NULL, NULL),
(12, '#FF69B4', 1, NULL, NULL),
(13, '#00CED1', 1, NULL, NULL),
(14, '#FF6347', 1, NULL, NULL),
(15, '#ADFF2F', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coordinaciones`
--

DROP TABLE IF EXISTS `coordinaciones`;
CREATE TABLE IF NOT EXISTS `coordinaciones` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `coordinaciones`
--

INSERT INTO `coordinaciones` (`id`, `nombre`, `descripcion`, `color`, `activa`, `created_at`, `updated_at`) VALUES
(1, 'Coordinación de Tierra', NULL, '#28A745', 1, '2025-04-14 06:39:50', '2025-04-14 06:39:50'),
(2, 'Coordinación de Vuelo', NULL, '#007BFF', 1, '2025-04-14 06:45:05', '2025-04-14 06:45:05'),
(3, 'Coordinación de Mantenimiento', NULL, '#FD7E14', 1, '2025-04-14 07:07:01', '2025-04-14 07:07:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

DROP TABLE IF EXISTS `cursos`;
CREATE TABLE IF NOT EXISTS `cursos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `coordinacion_id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('inicial','recurrente','puntual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `duracion_horas` int NOT NULL,
  `requiere_notificacion_inac` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cursos_coordinacion_id_foreign` (`coordinacion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_grupo`
--

DROP TABLE IF EXISTS `curso_grupo`;
CREATE TABLE IF NOT EXISTS `curso_grupo` (
  `curso_id` bigint UNSIGNED NOT NULL,
  `grupo_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`curso_id`,`grupo_id`),
  KEY `curso_grupo_grupo_id_foreign` (`grupo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE IF NOT EXISTS `grupos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `coordinacion_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grupos_coordinacion_id_foreign` (`coordinacion_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupos`
--

INSERT INTO `grupos` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`, `coordinacion_id`) VALUES
(1, 'Grupo A', 'Grupo de prueba', '2025-04-14 10:46:55', '2025-04-14 10:46:55', 1),
(2, 'Grupo C', 'Otro grupo', '2025-04-14 10:46:55', '2025-04-15 21:29:19', 1),
(3, 'Grupo B', NULL, '2025-04-15 21:34:12', '2025-04-15 21:34:12', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_curso`
--

DROP TABLE IF EXISTS `grupo_curso`;
CREATE TABLE IF NOT EXISTS `grupo_curso` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `grupo_id` bigint UNSIGNED NOT NULL,
  `curso_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_curso_grupo_id_foreign` (`grupo_id`),
  KEY `grupo_curso_curso_id_foreign` (`curso_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructores`
--

DROP TABLE IF EXISTS `instructores`;
CREATE TABLE IF NOT EXISTS `instructores` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `coordinacion_id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `instructores_coordinacion_id_foreign` (`coordinacion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_04_13_052644_create_coordinaciones_table', 1),
(5, '2025_04_13_052651_create_cursos_table', 1),
(6, '2025_04_13_052655_create_grupos_table', 1),
(7, '2025_04_13_052658_create_curso_grupo_table', 1),
(8, '2025_04_13_053312_create_instructores_table', 1),
(9, '2025_04_13_053314_create_aulas_table', 1),
(10, '2025_04_13_053318_create_programaciones_table', 1),
(11, '2025_04_13_053322_create_calendario_instructor_table', 1),
(12, '2025_04_13_053324_add_rol_to_users_table', 1),
(13, '2025_04_13_235053_add_coordinacion_id_and_requiere_cambio_to_users_table', 2),
(14, '2025_04_14_010218_add_color_activa_to_coordinaciones_table', 3),
(15, '2025_04_14_010521_add_color_and_activa_to_coordinaciones_table', 4),
(16, '2025_04_14_011934_add_activa_to_coordinaciones_table', 5),
(17, '2025_04_14_012129_create_colores_disponibles_table', 6),
(18, '2025_04_14_041740_create_grupo_curso_table', 7),
(19, '2025_04_14_044314_create_auditorias_table', 7),
(20, '2025_04_14_064518_add_coordinacion_id_to_grupos_table', 7),
(21, '2025_04_14_175848_add_activa_to_aulas_table', 8),
(22, '2025_04_14_224519_add_is_active_to_users_table', 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programaciones`
--

DROP TABLE IF EXISTS `programaciones`;
CREATE TABLE IF NOT EXISTS `programaciones` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `curso_id` bigint UNSIGNED NOT NULL,
  `grupo_id` bigint UNSIGNED DEFAULT NULL,
  `instructor_id` bigint UNSIGNED NOT NULL,
  `aula_id` bigint UNSIGNED NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `notificado_inac` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_notificacion_inac` date DEFAULT NULL,
  `estado` enum('programado','confirmado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'programado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `programaciones_curso_id_foreign` (`curso_id`),
  KEY `programaciones_grupo_id_foreign` (`grupo_id`),
  KEY `programaciones_instructor_id_foreign` (`instructor_id`),
  KEY `programaciones_aula_id_foreign` (`aula_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('IFiFBw10TtTzqF5SDKf5kJLr46iXFV9TkVRWmcxP', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUmRsMkdianBSYmtuRndjeWFyc3Q3Y3NZQmVmTTFDMHZWYWtNcDlFQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo2OiIvYWRtaW4iO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo2MDoiaHR0cDovL2xvY2FsaG9zdC9jdXJzb3NsYXNlci9wdWJsaWMvaW5kZXgucGhwL2FkbWluL3VzdWFyaW9zIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1744744014);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordinacion_id` bigint UNSIGNED DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requiere_cambio` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rol` enum('administrador','coordinador','analista','instructor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'analista',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `coordinacion_id`, `email_verified_at`, `password`, `requiere_cambio`, `remember_token`, `created_at`, `updated_at`, `rol`, `is_active`) VALUES
(1, 'JOSÉ', 'instruccion@gmail.com', NULL, NULL, '$2y$12$1MUQDuGi/HM0yJ/.Os7o6uxX3OARF7dJ9TUMz87SmhBZIzsukOzFe', 0, '6iRQswSySBCf47Wot8sbKoXj3ucsXYoezpVS27jlggtHvGtJSV8miiJxVn3E', '2025-04-13 11:04:17', '2025-04-15 03:40:37', 'administrador', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
