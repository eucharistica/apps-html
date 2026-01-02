SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for emr_user_has_roles
-- ----------------------------
CREATE TABLE `emr_user_has_roles`  (
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`) USING BTREE,
  INDEX `fk_emr_uhr_role`(`role_id` ASC) USING BTREE,
  CONSTRAINT `fk_emr_uhr_role` FOREIGN KEY (`role_id`) REFERENCES `emr_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emr_uhr_user` FOREIGN KEY (`user_id`) REFERENCES `emr_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
