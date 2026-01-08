CREATE TABLE IF NOT EXISTS emr_registry_routes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  app_key VARCHAR(50) NOT NULL,              -- contoh: admin, simrs, home
  route_key VARCHAR(100) NOT NULL,           -- key array di registry: users/roles/permissions
  title VARCHAR(150) NULL,
  url VARCHAR(255) NULL,                     -- opsional kalau kamu punya path menu/route; boleh null
  file_path VARCHAR(255) NOT NULL,           -- contoh: ./pages/users/list.php
  permission VARCHAR(150) NOT NULL,          -- contoh: admin.users.view
  registry_path VARCHAR(255) NOT NULL,       -- lokasi file registry.php
  checksum CHAR(64) NULL,                    -- sha256 dari payload route untuk deteksi perubahan
  is_active TINYINT(1) NOT NULL DEFAULT 1,

  first_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  synced_at DATETIME NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_registry_route (app_key, route_key),
  KEY idx_permission (permission),
  KEY idx_synced_at (synced_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
