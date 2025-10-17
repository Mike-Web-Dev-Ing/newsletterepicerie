-- Schema SQL pour newsletterepicerie (MySQL/MariaDB)
-- Base: epicerie (adapter si besoin)
-- Utilisation: mysql -u root -p epicerie < data/schema.sql

-- Table: subscribers
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) DEFAULT NULL,
  `email` VARCHAR(190) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: promotions
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `product_image` VARCHAR(255) NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `old_price` DECIMAL(10,2) NULL,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active_created` (`active`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: comments (avis clients)
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL,
  `rating` TINYINT UNSIGNED DEFAULT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données d'exemple (optionnel)
INSERT INTO `promotions` (`title`, `description`, `product_image`, `price`, `old_price`, `start_date`, `end_date`, `active`) VALUES
('Panier fruits locaux', 'Panier 2kg de fruits de saison.\nProvenance locale.', NULL, 9.90, 12.90, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1),
('Café moulu 500g', 'Arabica 100% torréfié artisanalement.', NULL, 4.50, 5.90, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);
