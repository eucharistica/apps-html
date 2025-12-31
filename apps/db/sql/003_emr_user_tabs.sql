-- EMR: open tabs persisted per user
-- Table: emr_user_tabs
-- Notes:
-- - url should store relative app URL (e.g. apps/simrs/index.php?page=dashboard)
-- - key is a stable identifier (e.g. dashboard, rawat_jalan)

CREATE TABLE emr_user_tabs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  tab_key VARCHAR(64) NOT NULL,
  title VARCHAR(100) NOT NULL,
  url VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_user_tab (user_id, tab_key),
  KEY idx_user_sort (user_id, sort_order)
);
