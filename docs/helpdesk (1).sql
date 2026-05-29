-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 15, 2026 at 07:59 PM
-- Server version: 8.4.3
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `helpdesk`
--

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `department_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 2, 'Redes administrativas y Computo', '2026-05-15 15:18:56', NULL),
(2, 3, 'Gestión de CCEE', '2026-05-15 22:30:32', '2026-05-15 22:35:11'),
(3, 2, 'Bienestar Social', '2026-05-15 22:39:13', '2026-05-15 22:39:13'),
(4, 2, 'Caja 1', '2026-05-15 22:39:25', '2026-05-15 22:39:25'),
(5, 2, 'Caja 2', '2026-05-15 22:39:36', '2026-05-15 22:39:36'),
(6, 2, 'Concesiones', '2026-05-15 22:40:45', '2026-05-15 22:40:45'),
(7, 2, 'Contabilidad', '2026-05-15 22:40:59', '2026-05-15 22:40:59'),
(8, 2, 'Cuentas corrientes', '2026-05-15 22:41:15', '2026-05-15 22:41:15'),
(9, 2, 'Eventos', '2026-05-15 22:41:25', '2026-05-15 22:41:25'),
(10, 2, 'Gerencia General', '2026-05-15 22:41:42', '2026-05-15 22:41:42'),
(11, 2, 'Departamento Legal', '2026-05-15 22:41:55', '2026-05-15 22:41:55'),
(12, 2, 'Marketing', '2026-05-15 22:42:05', '2026-05-15 22:42:05'),
(13, 2, 'Personal', '2026-05-15 22:42:14', '2026-05-15 22:42:14'),
(14, 2, 'Presidencia', '2026-05-15 22:42:24', '2026-05-15 22:42:24'),
(15, 2, 'Seguridad', '2026-05-15 22:42:47', '2026-05-15 22:42:47'),
(16, 2, 'Stud Book', '2026-05-15 22:42:59', '2026-05-15 22:42:59'),
(17, 2, 'Maestranza', '2026-05-15 22:43:11', '2026-05-15 22:43:11'),
(18, 2, 'Almacen', '2026-05-15 22:43:30', '2026-05-15 22:43:30'),
(19, 2, 'Auditoria Int.', '2026-05-15 22:43:47', '2026-05-15 22:43:47'),
(20, 3, 'Biblioteca', '2026-05-15 22:44:05', '2026-05-15 22:44:05'),
(21, 3, 'Deportes', '2026-05-15 22:44:33', '2026-05-15 22:44:33'),
(22, 3, 'Gimnasio', '2026-05-15 22:44:42', '2026-05-15 22:44:42'),
(23, 3, 'Sauna mujeres', '2026-05-15 22:44:54', '2026-05-15 22:44:54'),
(24, 3, 'Sauna Spa', '2026-05-15 22:45:07', '2026-05-15 22:45:07'),
(25, 3, 'Gestión de Socios', '2026-05-15 22:45:24', '2026-05-15 22:45:24'),
(26, 3, 'Tranquera', '2026-05-15 22:45:36', '2026-05-15 22:45:36'),
(27, 1, 'Canal de TV', '2026-05-15 22:45:46', '2026-05-15 22:45:46'),
(28, 1, 'Central Telefónica', '2026-05-15 22:46:03', '2026-05-15 22:46:03'),
(29, 1, 'Comisarios', '2026-05-15 22:46:11', '2026-05-15 22:46:11'),
(30, 1, 'Comisión de programas', '2026-05-15 22:46:26', '2026-05-15 22:46:26'),
(31, 1, 'Computo', '2026-05-15 22:46:37', '2026-05-15 22:46:37'),
(32, 1, 'Fotochart', '2026-05-15 22:46:50', '2026-05-15 22:46:50'),
(33, 1, 'Juez de llegada', '2026-05-15 22:47:02', '2026-05-15 22:47:02'),
(34, 1, 'Oficina tribunas', '2026-05-15 22:47:17', '2026-05-15 22:47:17'),
(35, 1, 'Periodistas', '2026-05-15 22:47:28', '2026-05-15 22:47:28'),
(36, 1, 'Sport', '2026-05-15 22:47:40', '2026-05-15 22:47:40'),
(37, 1, 'Veterinaria', '2026-05-15 22:47:55', '2026-05-15 22:47:55'),
(38, 1, 'Gerencia Hípica', '2026-05-15 22:48:08', '2026-05-15 22:48:08'),
(39, 1, 'Superintendencia', '2026-05-15 22:48:22', '2026-05-15 22:48:22');

-- --------------------------------------------------------

--
-- Table structure for table `attachments_messages`
--

CREATE TABLE `attachments_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_message_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attachments_ticket`
--

CREATE TABLE `attachments_ticket` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Problemas de Hardware', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(2, 'Problemas de Software', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(3, 'Red y Conectividad', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(4, 'Correo Electrónico', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(5, 'Acceso y Credenciales', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(6, 'Instalación de Software', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(7, 'Actualizaciones y Parcheo', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(8, 'Problemas con Impresoras/Escáneres', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(9, 'Soporte Remoto', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(10, 'Requerimientos de Equipos', NULL, '2025-05-13 04:22:47', '2025-05-13 04:22:47'),
(11, 'Seguridad Informática', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(12, 'Soporte a Sistemas Internos', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(13, 'Bases de Datos', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(14, 'Telefonía y Comunicaciones', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(15, 'Solicitudes de Capacitación o Manuales', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(16, 'Backup y Recuperación de Datos', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(17, 'Configuración de Correo en Dispositivos Móviles', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(18, 'Integración de Servicios', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(19, 'Problemas de Rendimiento del Sistema', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(20, 'Errores de Inicio de Sesión', NULL, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(21, 'Soporte a Equipos Móviles', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(22, 'Solicitudes de Alta de Usuario', NULL, '2025-05-13 04:24:53', '2025-05-30 12:09:44'),
(23, 'Eliminación o Baja de Usuarios', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(24, 'Cambios de Configuración de Equipos', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(25, 'Problemas con Proyectores y Presentaciones', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(26, 'Soporte a Videollamadas y Conferencias', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(27, 'Control de Inventario de Equipos TI', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(28, 'Solicitudes de Licencias de Software', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(29, 'Soporte a Firma Digital o Certificados', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(30, 'Auditorías y Revisiones Técnicas', NULL, '2025-05-13 04:24:53', '2025-05-13 04:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Hípica', '2026-05-13 09:22:47', '2026-05-13 09:22:47'),
(2, 'Administrativas', '2026-05-13 09:22:47', '2026-05-15 20:24:05'),
(3, 'CCEE', '2026-05-13 09:22:47', '2026-05-13 09:22:47');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_11_022904_create_departments_table', 1),
(2, '2014_10_11_045412_create_areas_table', 1),
(3, '2014_10_12_000000_create_users_table', 1),
(4, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(5, '2019_08_19_000000_create_failed_jobs_table', 1),
(6, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(7, '2025_05_03_023131_create_status_table', 1),
(8, '2025_05_03_023201_create_priority_table', 1),
(9, '2025_05_03_023226_create_categories_table', 1),
(10, '2025_05_03_023243_create_tickets_table', 1),
(11, '2025_05_03_023529_create_ticket_messages_table', 1),
(12, '2025_05_03_023600_create_attachments_ticket_table', 1),
(13, '2025_05_03_023618_create_attachments_messages_table', 1),
(14, '2025_05_03_023640_create_logs_table', 1),
(15, '2025_05_03_023658_create_faqs_table', 1),
(16, '2025_05_29_170803_create_system_logs_table', 1),
(17, '2025_06_06_204738_alter_description_in_tickets_table', 1),
(18, '2025_06_07_170242_add_color_to_priorities_table', 1),
(19, '2025_06_08_000127_alter_message_column_in_ticket_messages_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priority`
--

CREATE TABLE `priority` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_time` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `priority`
--

INSERT INTO `priority` (`id`, `name`, `color`, `response_time`, `created_at`, `updated_at`) VALUES
(1, 'Muy Baja', 'bg-light text-dark', 359, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(2, 'Baja', 'bg-secondary	', 1191, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(3, 'Media', 'bg-warning text-dark	', 133, '2025-05-13 04:24:30', '2025-05-13 04:24:30'),
(4, 'Alta', 'bg-primary	', 362, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(5, 'Muy Alta', 'bg-danger	', 942, '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(6, 'Extrema', 'bg-dark	', 579, '2025-05-13 04:24:53', '2025-05-13 04:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `name`, `color`, `created_at`, `updated_at`) VALUES
(1, 'Sin Asignar', 'bg-danger\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(2, 'En Progreso', 'bg-info\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(3, 'En espera de respuesta del usuario', 'bg-warning text-dark\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(4, 'En espera de respuesta del Agente', 'bg-light text-dark\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(5, 'Resuelto', 'bg-primary\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(6, 'Cerrado', 'bg-success\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(7, 'Reabierto', 'bg-dark\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53'),
(8, 'Cancelado', 'bg-secondary\n\n', '2025-05-13 04:24:53', '2025-05-13 04:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `module`, `action`, `message`, `created_at`, `updated_at`) VALUES
(1, 2, 'department', 'update', 'Se actualizó el departamento ID 2', '2026-05-15 20:24:05', '2026-05-15 20:24:05'),
(2, 2, 'users', 'create', 'Se creó el usuario: miguel@gmail.com', '2026-05-15 21:36:18', '2026-05-15 21:36:18'),
(3, 2, 'users', 'update', 'Se actualizó el usuario ID 3', '2026-05-15 21:44:18', '2026-05-15 21:44:18'),
(4, 2, 'users', 'update', 'Se actualizó el usuario ID 3', '2026-05-15 21:44:31', '2026-05-15 21:44:31'),
(5, 2, 'area', 'create', 'Se creó el área: GEstión de CCEE', '2026-05-15 22:30:32', '2026-05-15 22:30:32'),
(6, 2, 'area', 'update', 'Se actualizó el área ID 2', '2026-05-15 22:34:56', '2026-05-15 22:34:56'),
(7, 2, 'area', 'update', 'Se actualizó el área ID 2', '2026-05-15 22:35:04', '2026-05-15 22:35:04'),
(8, 2, 'area', 'update', 'Se actualizó el área ID 2', '2026-05-15 22:35:11', '2026-05-15 22:35:11'),
(9, 2, 'area', 'create', 'Se creó el área: Bienestar Social', '2026-05-15 22:39:13', '2026-05-15 22:39:13'),
(10, 2, 'area', 'create', 'Se creó el área: Caja 1', '2026-05-15 22:39:25', '2026-05-15 22:39:25'),
(11, 2, 'area', 'create', 'Se creó el área: Caja 2', '2026-05-15 22:39:36', '2026-05-15 22:39:36'),
(12, 2, 'area', 'create', 'Se creó el área: Concesiones', '2026-05-15 22:40:45', '2026-05-15 22:40:45'),
(13, 2, 'area', 'create', 'Se creó el área: Contabilidad', '2026-05-15 22:40:59', '2026-05-15 22:40:59'),
(14, 2, 'area', 'create', 'Se creó el área: Cuentas corrientes', '2026-05-15 22:41:15', '2026-05-15 22:41:15'),
(15, 2, 'area', 'create', 'Se creó el área: Eventos', '2026-05-15 22:41:25', '2026-05-15 22:41:25'),
(16, 2, 'area', 'create', 'Se creó el área: Gerencia General', '2026-05-15 22:41:42', '2026-05-15 22:41:42'),
(17, 2, 'area', 'create', 'Se creó el área: Departamento Legal', '2026-05-15 22:41:55', '2026-05-15 22:41:55'),
(18, 2, 'area', 'create', 'Se creó el área: Marketing', '2026-05-15 22:42:05', '2026-05-15 22:42:05'),
(19, 2, 'area', 'create', 'Se creó el área: Personal', '2026-05-15 22:42:14', '2026-05-15 22:42:14'),
(20, 2, 'area', 'create', 'Se creó el área: Presidencia', '2026-05-15 22:42:24', '2026-05-15 22:42:24'),
(21, 2, 'area', 'create', 'Se creó el área: Seguridad', '2026-05-15 22:42:47', '2026-05-15 22:42:47'),
(22, 2, 'area', 'create', 'Se creó el área: Stud Book', '2026-05-15 22:42:59', '2026-05-15 22:42:59'),
(23, 2, 'area', 'create', 'Se creó el área: Maestranza', '2026-05-15 22:43:11', '2026-05-15 22:43:11'),
(24, 2, 'area', 'create', 'Se creó el área: Almacen', '2026-05-15 22:43:30', '2026-05-15 22:43:30'),
(25, 2, 'area', 'create', 'Se creó el área: Auditoria Int.', '2026-05-15 22:43:47', '2026-05-15 22:43:47'),
(26, 2, 'area', 'create', 'Se creó el área: Biblioteca', '2026-05-15 22:44:05', '2026-05-15 22:44:05'),
(27, 2, 'area', 'create', 'Se creó el área: Deportes', '2026-05-15 22:44:33', '2026-05-15 22:44:33'),
(28, 2, 'area', 'create', 'Se creó el área: Gimnasio', '2026-05-15 22:44:42', '2026-05-15 22:44:42'),
(29, 2, 'area', 'create', 'Se creó el área: Sauna mujeres', '2026-05-15 22:44:54', '2026-05-15 22:44:54'),
(30, 2, 'area', 'create', 'Se creó el área: Sauna Spa', '2026-05-15 22:45:07', '2026-05-15 22:45:07'),
(31, 2, 'area', 'create', 'Se creó el área: Gestión de Socios', '2026-05-15 22:45:24', '2026-05-15 22:45:24'),
(32, 2, 'area', 'create', 'Se creó el área: Tranquera', '2026-05-15 22:45:36', '2026-05-15 22:45:36'),
(33, 2, 'area', 'create', 'Se creó el área: Canal de TV', '2026-05-15 22:45:46', '2026-05-15 22:45:46'),
(34, 2, 'area', 'create', 'Se creó el área: Central Telefónica', '2026-05-15 22:46:03', '2026-05-15 22:46:03'),
(35, 2, 'area', 'create', 'Se creó el área: Comisarios', '2026-05-15 22:46:11', '2026-05-15 22:46:11'),
(36, 2, 'area', 'create', 'Se creó el área: Comisión de programas', '2026-05-15 22:46:26', '2026-05-15 22:46:26'),
(37, 2, 'area', 'create', 'Se creó el área: Computo', '2026-05-15 22:46:37', '2026-05-15 22:46:37'),
(38, 2, 'area', 'create', 'Se creó el área: Fotochart', '2026-05-15 22:46:50', '2026-05-15 22:46:50'),
(39, 2, 'area', 'create', 'Se creó el área: Juez de llegada', '2026-05-15 22:47:02', '2026-05-15 22:47:02'),
(40, 2, 'area', 'create', 'Se creó el área: Oficina tribunas', '2026-05-15 22:47:17', '2026-05-15 22:47:17'),
(41, 2, 'area', 'create', 'Se creó el área: Periodistas', '2026-05-15 22:47:28', '2026-05-15 22:47:28'),
(42, 2, 'area', 'create', 'Se creó el área: Sport', '2026-05-15 22:47:40', '2026-05-15 22:47:40'),
(43, 2, 'area', 'create', 'Se creó el área: Veterinaria', '2026-05-15 22:47:55', '2026-05-15 22:47:55'),
(44, 2, 'area', 'create', 'Se creó el área: Gerencia Hípica', '2026-05-15 22:48:08', '2026-05-15 22:48:08'),
(45, 2, 'area', 'create', 'Se creó el área: Superintendencia', '2026-05-15 22:48:22', '2026-05-15 22:48:22');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `priority_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_id` bigint UNSIGNED NOT NULL,
  `role` enum('admin','agent','client') COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `area_id`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Sandro Nilton', 'sandro91111@gmail.com', '2026-05-13 09:24:53', '$2y$12$QTmu62ZW2Am65.WnqFPB.etnPXCjpH6/WSwj52iTlLflG0.77OxJa', 1, 'client', 'b7tcNfxLh9gldEpPWbYMWZiq7tCxrz38Eq3pPXIZhaC4mJMRgXd5VIsrqa0Z', '2025-05-13 09:24:53', '2025-05-13 09:24:53'),
(2, 'Jean Paul', 'jeanpaul@gmail.com', '2026-06-13 09:24:53', '$2y$12$QTmu62ZW2Am65.WnqFPB.etnPXCjpH6/WSwj52iTlLflG0.77OxJa', 1, 'admin', '8ugEcdbwZJBo6E7BlpcRnJqgIfBRtKOS5zdI2kKfC8slxJbreQFveWDuuwQN', '2025-05-13 09:24:53', '2025-05-13 09:24:53'),
(3, 'Eddy', 'eddy@gmail.com', '2026-05-13 09:24:53', '$2y$12$QTmu62ZW2Am65.WnqFPB.etnPXCjpH6/WSwj52iTlLflG0.77OxJa', 1, 'agent', 'LqpvBV1c6kBSCy3uFNFMywwEth1C2Si7UfcpMJC7Sm3otkbFVHaQ8TkDcg3m', '2025-05-13 09:24:53', '2026-05-15 21:44:31'),
(4, 'Miguel', 'miguel@gmail.com', '2026-05-15 19:29:12', '$2y$12$lLgfX0GS3s3nw1IFRl4JsuK3MjeYugd/6TgHhejU75.aC0togPRu6', 1, 'agent', NULL, '2026-05-15 21:36:17', '2026-05-15 21:36:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `areas_department_id_foreign` (`department_id`);

--
-- Indexes for table `attachments_messages`
--
ALTER TABLE `attachments_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachments_messages_ticket_message_id_foreign` (`ticket_message_id`);

--
-- Indexes for table `attachments_ticket`
--
ALTER TABLE `attachments_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachments_ticket_ticket_id_foreign` (`ticket_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logs_ticket_id_foreign` (`ticket_id`),
  ADD KEY `logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `priority`
--
ALTER TABLE `priority`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_user_id_foreign` (`user_id`),
  ADD KEY `tickets_assigned_to_foreign` (`assigned_to`),
  ADD KEY `tickets_status_id_foreign` (`status_id`),
  ADD KEY `tickets_priority_id_foreign` (`priority_id`),
  ADD KEY `tickets_category_id_foreign` (`category_id`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_messages_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_messages_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_area_id_foreign` (`area_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `attachments_messages`
--
ALTER TABLE `attachments_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attachments_ticket`
--
ALTER TABLE `attachments_ticket`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priority`
--
ALTER TABLE `priority`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `areas_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attachments_messages`
--
ALTER TABLE `attachments_messages`
  ADD CONSTRAINT `attachments_messages_ticket_message_id_foreign` FOREIGN KEY (`ticket_message_id`) REFERENCES `ticket_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attachments_ticket`
--
ALTER TABLE `attachments_ticket`
  ADD CONSTRAINT `attachments_ticket_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `tickets_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `priority` (`id`),
  ADD CONSTRAINT `tickets_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`),
  ADD CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
