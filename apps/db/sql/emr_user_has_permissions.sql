SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for emr_user_has_permissions
-- ----------------------------
CREATE TABLE `emr_user_has_permissions`  (
  `user_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `permission_id`) USING BTREE,
  INDEX `fk_emr_uhp_perm`(`permission_id` ASC) USING BTREE,
  CONSTRAINT `fk_emr_uhp_perm` FOREIGN KEY (`permission_id`) REFERENCES `emr_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emr_uhp_user` FOREIGN KEY (`user_id`) REFERENCES `emr_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
