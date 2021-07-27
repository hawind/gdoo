/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3308
 Source Server Type    : MySQL
 Source Server Version : 100508
 Source Host           : localhost:3308
 Source Schema         : gdoo_demo

 Target Server Type    : MySQL
 Target Server Version : 100508
 File Encoding         : 65001

 Date: 28/07/2021 04:51:07
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for share
-- ----------------------------
DROP TABLE IF EXISTS `share`;
CREATE TABLE `share`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `source_id` int(11) NOT NULL,
  `source_type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_repeat` tinyint(3) NOT NULL DEFAULT 0 COMMENT '重复标记',
  `receive_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '共享编号',
  `receive_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '共享名称',
  `permissions` smallint(6) NULL DEFAULT NULL,
  `start_at` int(11) NULL DEFAULT NULL COMMENT '开始时间',
  `end_at` int(11) NULL DEFAULT NULL COMMENT '结束时间',
  `created_by` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_id` int(11) NULL DEFAULT NULL,
  `created_at` int(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_source_id`(`source_id`) USING BTREE,
  INDEX `idx_source_type`(`source_type`) USING BTREE,
  INDEX `idx_is_repeat`(`is_repeat`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of share
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
