-- ============================================
-- TABLAS PERSONALIZADAS PARA AVENTURA TOURISM
-- ============================================
-- Ejecutar este archivo después de que WordPress esté instalado
-- Opción 1: Desde línea de comandos
-- mysql -u wordpress -p wordpress < install-tables.sql
--
-- Opción 2: Desde WordPress - Dashboard → Tools → Database Client
-- Opción 3: Desde phpmyadmin

-- ============================================
-- 1. HORARIOS DE TOURS
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_tour_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tour_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID del tour (post_id)',
  `day_of_week` int(1) NOT NULL COMMENT '0=Lunes, 1=Martes, ... 6=Domingo',
  `start_time` time NOT NULL COMMENT 'Hora de inicio (08:00)',
  `end_time` time NOT NULL COMMENT 'Hora de fin (14:00)',
  `max_capacity` int(11) NOT NULL DEFAULT 20 COMMENT 'Capacidad máxima de personas',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Desactivo, 1=Activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tour_id` (`tour_id`),
  KEY `day_of_week` (`day_of_week`),
  KEY `is_active` (`is_active`),
  KEY `tour_active_dow` (`tour_id`, `is_active`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Horarios recurrentes de cada tour';

-- ============================================
-- 2. RESERVAS
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_reservations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID usuario WordPress',
  `tour_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID del tour (post_id)',
  `schedule_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID del horario',
  `reservation_date` date NOT NULL COMMENT 'Fecha del tour (2026-08-20)',
  `num_persons` int(11) NOT NULL COMMENT 'Cantidad de personas',
  `total_amount` decimal(10, 2) NOT NULL COMMENT 'Monto total en CLP',
  `currency` varchar(3) NOT NULL DEFAULT 'CLP',
  `status` enum('pendiente','confirmada','pagada','cancelada') NOT NULL DEFAULT 'pendiente'
    COMMENT 'Estado de la reserva',
  `payment_reference` varchar(255) DEFAULT NULL COMMENT 'Referencia de pago banco',
  `participant_names` longtext COMMENT 'JSON array con {name, age} de participantes',
  `email` varchar(255) NOT NULL COMMENT 'Email de contacto',
  `phone` varchar(20) NOT NULL COMMENT 'Teléfono de contacto',
  `special_requirements` longtext COMMENT 'Requisitos especiales (movilidad, alergias, etc)',
  `confirmation_token` varchar(255) UNIQUE COMMENT 'Token para confirmar por email',
  `confirmed_at` datetime DEFAULT NULL COMMENT 'Timestamp de confirmación',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `confirmation_token` (`confirmation_token`),
  KEY `user_id` (`user_id`),
  KEY `tour_id` (`tour_id`),
  KEY `schedule_id` (`schedule_id`),
  KEY `status` (`status`),
  KEY `reservation_date` (`reservation_date`),
  KEY `email` (`email`),
  KEY `tour_date_status` (`tour_id`, `reservation_date`, `status`),
  FOREIGN KEY (`tour_id`) REFERENCES `wp_posts` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Reservas de tours realizadas por clientes';

-- ============================================
-- 3. PAGOS
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_tour_payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID reserva (relación)',
  `amount` decimal(10, 2) NOT NULL COMMENT 'Monto pagado en CLP',
  `currency` varchar(3) NOT NULL DEFAULT 'CLP',
  `status` enum('pendiente','procesando','completado','fallido','reembolso')
    NOT NULL DEFAULT 'pendiente' COMMENT 'Estado del pago',
  `payment_method` varchar(50) NOT NULL COMMENT 'webcheckout, tarjeta, transferencia',
  `transaction_id` varchar(255) UNIQUE DEFAULT NULL COMMENT 'ID único del banco',
  `bank_response_code` varchar(10) DEFAULT NULL COMMENT 'Código respuesta banco (0=OK)',
  `bank_response_message` longtext COMMENT 'Mensaje del banco',
  `payment_date` datetime DEFAULT NULL COMMENT 'Timestamp de procesamiento',
  `receipt_url` varchar(500) DEFAULT NULL COMMENT 'URL del comprobante PDF',
  `error_message` longtext COMMENT 'Mensaje de error si falló',
  `retry_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de reintentos',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `status` (`status`),
  KEY `payment_date` (`payment_date`),
  KEY `reservation_status_date` (`reservation_id`, `status`, `payment_date`),
  CONSTRAINT `fk_payments_reservation` FOREIGN KEY (`reservation_id`)
    REFERENCES `wp_reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Pagos de reservas procesados por Banco de Chile';

-- ============================================
-- 4. CONFIGURACIÓN BANCO DE CHILE
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_bch_configuration` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL UNIQUE COMMENT 'Clave de configuración',
  `value` longtext NOT NULL COMMENT 'Valor (puede estar encriptado)',
  `encrypted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Encriptado, 0=Texto plano',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuración segura de Banco de Chile (credenciales, URLs)';

-- ============================================
-- 5. LOGS DE TRANSACCIONES
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_transaction_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID reserva asociada',
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID pago asociado',
  `event_type` varchar(50) NOT NULL COMMENT 'payment_initiated, payment_callback, etc',
  `request_data` longtext COMMENT 'JSON de solicitud enviada',
  `response_data` longtext COMMENT 'JSON de respuesta recibida',
  `status_code` int(11) DEFAULT NULL COMMENT 'HTTP status (200, 400, 500, etc)',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP del cliente/webhook',
  `user_agent` longtext COMMENT 'User agent del navegador/webhook',
  `notes` longtext COMMENT 'Notas adicionales',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `payment_id` (`payment_id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log detallado de todas las transacciones para auditoría';

-- ============================================
-- 6. RESEÑAS Y CALIFICACIONES
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_tour_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID reserva',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID usuario WordPress',
  `rating` int(1) NOT NULL COMMENT '1-5 estrellas',
  `title` varchar(255) NOT NULL COMMENT 'Título de la reseña',
  `review_text` longtext NOT NULL COMMENT 'Contenido de la reseña',
  `approved` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Pendiente, 1=Aprobado',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `user_id` (`user_id`),
  KEY `approved` (`approved`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Reseñas y calificaciones de clientes sobre tours (futuro)';

-- ============================================
-- DATOS INICIALES (Opcional)
-- ============================================

-- Configuración base (los valores reales se cargan desde .env)
INSERT IGNORE INTO `wp_bch_configuration` (`key`, `value`, `encrypted`) VALUES
('environment', 'sandbox', 0),
('commerce_code', '', 0),
('api_key', '', 1),
('secret_key', '', 1);

-- ============================================
-- INFORMACIÓN DE INSTALACIÓN
-- ============================================
/*
Tablas creadas exitosamente para Aventura Tourism.

Tabla de Capacidad Aproximada (sin índices adicionales):
- wp_tour_schedules: ~10 filas por tour (7 días x múltiples horarios)
- wp_reservations: Escalable (1M+ filas)
- wp_tour_payments: Escalable (1M+ filas)
- wp_transaction_logs: Escalable (10M+ filas)
- wp_tour_reviews: Escalable (100K+ filas)

Espacio estimado en disco:
- Datos vacíos: ~2 MB
- Por cada 10,000 reservas: +10 MB
- Por cada 100,000 logs: +50 MB

Recomendaciones:
1. Hacer backup antes de cambios grandes
2. Crear índices según patrón de búsqueda
3. Purgar logs antiguos periódicamente
4. Monitorear tamaño de tabla de logs

Para verificar tablas:
SHOW TABLES LIKE 'wp_%';
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size in MB'
FROM information_schema.TABLES WHERE table_schema = 'wordpress';
*/
