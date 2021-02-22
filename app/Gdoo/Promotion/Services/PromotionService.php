<?php namespace Gdoo\Promotion\Services;

use Gdoo\Index\Services\BadgeService;

class PromotionService
{
    /**
     * 获取待办的促销申请
     */
    public static function getBadge()
    {
        return BadgeService::getModelTodo('promotion');
    }

    /**
     * 获取为使用的促销sql
     * 
     */
    public static function getSurplusPromotionSql() 
    {
        return "SELECT * FROM (SELECT
        isnull(d.quantity, 0) AS ysy_num,
        isnull(a.quantity, 0) - isnull(d.quantity, 0) AS wsy_num,
        b.type_id,
        b.id AS promotion_id
        FROM promotion_data a
        LEFT JOIN promotion b ON a.promotion_id = b.id
        LEFT JOIN customer c ON b.customer_id = c.id
        LEFT JOIN (
            SELECT sum(isnull(customer_order_data.delivery_quantity, 0)) AS quantity,
            customer_order_data.promotion_data_id
            FROM customer_order_data
            WHERE customer_order_data.promotion_data_id IS NOT NULL
            GROUP BY customer_order_data.promotion_data_id) d ON a.id = d.promotion_data_id
        ) as temp
        WHERE isnull(temp.wsy_num, 0) > 0";
    }
}