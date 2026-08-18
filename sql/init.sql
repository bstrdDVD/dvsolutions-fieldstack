-- ============================================
-- TABLAS PERSONALIZADAS PARA SISTEMA DE TOURS
-- ============================================

-- Tabla: Horarios de Tours
CREATE TABLE IF NOT EXISTS `wp_tour_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tour_id` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` int(1) NOT NULL COMMENT '0=Lunes, 6=Domingo',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_capacity` int(11) NOT NULL DEFAULT 20,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tour_id` (`tour_id`),
  KEY `day_of_week` (`day_of_week`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: Reservas
CREATE TABLE IF NOT EXISTS `wp_reservations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED,
  `tour_id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED NOT NULL,
  `reservation_date` date NOT NULL COMMENT 'Fecha de la reserva',
  `num_persons` int(11) NOT NULL,
  `total_amount` decimal(10, 2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'CLP',
  `status` enum('pendiente', 'confirmada', 'pagada', 'cancelada') NOT NULL DEFAULT 'pendiente',
  `payment_reference` varchar(255),
  `participant_names` longtext COMMENT 'JSON array con nombres de participantes',
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `special_requirements` longtext,
  `confirmation_token` varchar(255),
  `confirmed_at` datetime,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `confirmation_token` (`confirmation_token`),
  KEY `user_id` (`user_id`),
  KEY `tour_id` (`tour_id`),
  KEY `schedule_id` (`schedule_id`),
  KEY `status` (`status`),
  KEY `reservation_date` (`reservation_date`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: Pagos
CREATE TABLE IF NOT EXISTS `wp_tour_payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10, 2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'CLP',
  `status` enum('pendiente', 'procesando', 'completado', 'fallido', 'reembolso') NOT NULL DEFAULT 'pendiente',
  `payment_method` varchar(50) NOT NULL COMMENT 'webcheckout, tarjeta, transferencia',
  `transaction_id` varchar(255),
  `bank_response_code` varchar(10),
  `bank_response_message` longtext,
  `payment_date` datetime,
  `receipt_url` varchar(500),
  `error_message` longtext,
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `status` (`status`),
  KEY `payment_date` (`payment_date`),
  CONSTRAINT `fk_payments_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `wp_reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: Configuración de Banco de Chile
CREATE TABLE IF NOT EXISTS `wp_bch_configuration` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL UNIQUE,
  `value` longtext NOT NULL,
  `encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: Logs de Transacciones
CREATE TABLE IF NOT EXISTS `wp_transaction_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) UNSIGNED,
  `payment_id` bigint(20) UNSIGNED,
  `event_type` varchar(50) NOT NULL COMMENT 'payment_initiated, payment_callback, payment_confirmed, etc',
  `request_data` longtext,
  `response_data` longtext,
  `status_code` int(11),
  `ip_address` varchar(45),
  `user_agent` longtext,
  `notes` longtext,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `payment_id` (`payment_id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: Reseñas y Calificaciones (para futuro)
CREATE TABLE IF NOT EXISTS `wp_tour_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED,
  `rating` int(1) NOT NULL COMMENT '1-5 stars',
  `title` varchar(255) NOT NULL,
  `review_text` longtext NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `user_id` (`user_id`),
  KEY `approved` (`approved`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ÍNDICES PARA PERFORMANCE
-- ============================================

-- Índice compuesto para búsquedas frecuentes de disponibilidad
ALTER TABLE `wp_tour_schedules` ADD INDEX `tour_active_dow` (`tour_id`, `is_active`, `day_of_week`);

-- Índice compuesto para búsquedas de reservas por tour y fecha
ALTER TABLE `wp_reservations` ADD INDEX `tour_date_status` (`tour_id`, `reservation_date`, `status`);

-- Índice compuesto para búsquedas de pagos
ALTER TABLE `wp_tour_payments` ADD INDEX `reservation_status_date` (`reservation_id`, `status`, `payment_date`);

-- ============================================
-- DATOS INICIALES (OPCIONAL)
-- ============================================

-- Nota: Los siguientes datos iniciales se pueden agregar manualmente a través del admin de WordPress
-- O se pueden ejecutar después de que esté configurado WordPress y ACF

-- Ejemplo: Insertar configuración por defecto de Banco de Chile
-- INSERT INTO `wp_bch_configuration` (`key`, `value`) VALUES
-- ('environment', 'sandbox'),
-- ('commerce_code', ''),
-- ('api_key', ''),
-- ('secret_key', '');
