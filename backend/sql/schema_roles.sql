-- Users table with roles (admin/editor)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `pass_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- INSERT example (replace HASH_HERE by password_hash(...) output):
-- INSERT INTO users (username, pass_hash, role) VALUES ('admin', 'HASH_HERE', 'admin');
