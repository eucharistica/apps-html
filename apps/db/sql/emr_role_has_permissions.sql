SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for emr_role_has_permissions
-- ----------------------------
CREATE TABLE `emr_role_has_permissions`  (
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`) USING BTREE,
  INDEX `fk_emr_rhp_perm`(`permission_id` ASC) USING BTREE,
  CONSTRAINT `fk_emr_rhp_perm` FOREIGN KEY (`permission_id`) REFERENCES `emr_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emr_rhp_role` FOREIGN KEY (`role_id`) REFERENCES `emr_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for emr_roles
-- ----------------------------
DROP TABLE IF EXISTS `emr_roles`;
CREATE TABLE `emr_roles`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_emr_roles_name`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for emr_user_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `emr_user_has_permissions`;
CREATE TABLE `emr_user_has_permissions`  (
  `user_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `permission_id`) USING BTREE,
  INDEX `fk_emr_uhp_perm`(`permission_id` ASC) USING BTREE,
  CONSTRAINT `fk_emr_uhp_perm` FOREIGN KEY (`permission_id`) REFERENCES `emr_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emr_uhp_user` FOREIGN KEY (`user_id`) REFERENCES `emr_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for emr_user_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `emr_user_has_roles`;
CREATE TABLE `emr_user_has_roles`  (
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`) USING BTREE,
  INDEX `fk_emr_uhr_role`(`role_id` ASC) USING BTREE,
  CONSTRAINT `fk_emr_uhr_role` FOREIGN KEY (`role_id`) REFERENCES `emr_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emr_uhr_user` FOREIGN KEY (`user_id`) REFERENCES `emr_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
