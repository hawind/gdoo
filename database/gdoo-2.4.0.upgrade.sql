SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `product_formula`;

DROP TABLE IF EXISTS `product_material`;
CREATE TABLE `product_material`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) NULL DEFAULT NULL COMMENT '仓库',
  `product_id` int(11) NULL DEFAULT NULL COMMENT '产品名称',
  `material_id` int(11) NULL DEFAULT NULL COMMENT '物料名称',
  `quantity` decimal(10, 2) NULL DEFAULT NULL COMMENT '用量',
  `loss_rate` decimal(18, 2) NULL DEFAULT NULL COMMENT '损耗率(%)',
  `created_id` int(11) NULL DEFAULT NULL,
  `created_by` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` int(11) NULL DEFAULT NULL,
  `updated_at` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `updated_by` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '更新人',
  `updated_id` int(11) NULL DEFAULT NULL COMMENT '编辑人ID',
  `status` tinyint(3) NOT NULL DEFAULT 1,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_product_id`(`product_id`) USING BTREE,
  INDEX `idx_material_id`(`material_id`) USING BTREE,
  INDEX `idx_warehouse_id`(`warehouse_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `widget`(`name`, `color`, `type`, `sort`, `url`, `more_url`, `receive_id`, `receive_name`, `status`, `default`, `icon`, `updated_by`, `updated_at`, `created_at`, `created_by`, `updated_id`, `created_id`, `code`, `grid`) VALUES ('销售订单(元)', '#FF6600', 2, 0, 'order/widget/orderCount', 'order/order/index', 'all', '全体人员', 1, 0, 'fa-file-text-o', '系统管理员', 1636268193, 1636267940, '系统管理员', 1, 1, 'info_order_count', 8);
INSERT INTO `widget`(`name`, `color`, `type`, `sort`, `url`, `more_url`, `receive_id`, `receive_name`, `status`, `default`, `icon`, `updated_by`, `updated_at`, `created_at`, `created_by`, `updated_id`, `created_id`, `code`, `grid`) VALUES ('客户', '#66CC00', 2, 0, 'customer/widget/customerCount', 'customer/customer/index', 'all', '全体人员', 1, 0, 'fa-users', '系统管理员', 1636269063, 1636268914, '系统管理员', 1, 1, 'info_customer_count', 8);
INSERT INTO `widget`(`name`, `color`, `type`, `sort`, `url`, `more_url`, `receive_id`, `receive_name`, `status`, `default`, `icon`, `updated_by`, `updated_at`, `created_at`, `created_by`, `updated_id`, `created_id`, `code`, `grid`) VALUES ('客户联系人', '#3399FF', 2, 0, 'customer/widget/customerContactCount', 'customer/contact/index', 'all', '全体人员', 1, 0, 'fa-address-book-o', '系统管理员', 1636269516, 1636268914, '系统管理员', 1, 1, 'info_customer_contact_count', 8);