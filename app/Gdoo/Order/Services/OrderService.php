<?php namespace Gdoo\Order\Services;

use DB;
use Gdoo\Index\Services\BadgeService;
use Gdoo\Stock\Services\StockService;

class OrderService
{
    /**
     * 获取待审订单
     */
    public static function getBadge()
    {
        return BadgeService::getModelTodo('customer_order');
    }

    /**
	 * 自定义查询打印数据
	 */
    public static function getPrintData($id)
    {
		$master = DB::table('customer_order as co')->where('co.id', $id)
        ->leftJoin('customer as c', 'c.id', '=', 'co.customer_id')
        ->leftJoin('customer_tax as ct', 'ct.id', '=', 'co.tax_id')
        ->leftJoin('sale_type as st', 'st.id', '=', 'co.type_id')
        ->selectRaw('co.*, ct.name as tax_name, c.name as customer_name, st.name as type_name')
        ->first();

        $rows = DB::table('customer_order_data as cod')
        ->leftJoin('customer_order as co', 'co.id', '=', 'cod.order_id')
        ->leftJoin('product as p', 'p.id', '=', 'cod.product_id')
        ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
        ->leftJoin('customer_order_type as cot', 'cot.id', '=', 'cod.type_id')
        ->where('co.id', $id)
        ->selectRaw('
            cod.*,
            cod.delivery_quantity * p.weight as total_weight,
            p.name as product_name,
            p.spec as product_spec,
            cot.name as type_name,
            pu.name as product_unit,
            p.material_type,
            p.product_type
        ')
        ->get();

        $master['fee_money'] = 0;
		return [
			'master' => $master,
			'rows' => $rows,
		];
    }

    /**
     * 获取销售未发订单
     * 
     */
    public static function getSaleOrderDataSql() 
    {
        return "SELECT f.sn AS delivery_sn,
        f.invoice_dt AS delivery_date,
        sd.id AS sale_data_id,
        sd.order_id,
        sd.order_id AS sale_id,
        sd.product_id,
        p.name AS product_name,
        p.barcode AS product_barcode,
        p.spec AS product_spec,
        p.unit_id,
        p.product_type,
        p.material_type,
        u.name AS product_unit,
        sd.quantity,
        sd.delivery_quantity,
        sd.price,
        sd.money,
        p.code AS product_code,
        isnull(r.quantity, 0) AS stock_quantity,
        sd.weight,
        (sd.weight * sd.delivery_quantity) AS total_weight,
        isnull(sd.use_close, 0) AS use_close,
        sm.customer_id,
        c.type_id AS customer_type_id,
        c.is_direct,
        c.is_allocate,
        CASE WHEN isnull(sd.use_close, 0) = 0 THEN '否' ELSE '是' END AS use_close_name,
        isnull(sd.delivery_quantity, 0) - isnull(f.quantity, 0) AS wf_num,
        f.quantity AS yf_num,
        sd.type_id,
        cot.name AS type_id_name,
        sd.customer_stock,
        sd.other_money,
        p.is_produce_plan,
        pc.code AS category_code,
        p.barcode,
        isnull(sd.money, 0) + isnull(sd.other_money, 0) AS hj_money,
        sd.remark,
        u.code AS unit_code,
        CASE WHEN sd.type_id <= 1 THEN 1 ELSE CASE WHEN sd.type_id = 2 THEN 2 ELSE CASE WHEN sd.type_id = 3 THEN 3 ELSE 4 END END END AS cstcode,
        sd.batch_sn,
        sd.batch_date,
        sd.fee_data_id,
        sd.fee_src_id,
        sd.fee_src_type_id,
        sd.fee_src_sn,
        sd.promotion_sn,
        sd.promotion_data_id,
        sd.fee_category_id,
        bt.name AS fee_category_id_name,
        sd.customer_stock_dt
        FROM customer_order_data sd
        LEFT JOIN customer_order sm ON sm.id = sd.order_id
        LEFT JOIN customer_order_type cot ON cot.id = sd.type_id
        LEFT JOIN product p ON p.id = sd.product_id
        LEFT JOIN product_category pc ON pc.id = p.category_id
        LEFT JOIN product_unit u ON u.id = p.unit_id
        LEFT JOIN customer c ON sm.customer_id = c.id
             
        LEFT JOIN (SELECT sum(isnull(ky_num, 0)) AS quantity, product_id
            FROM (".StockService::getStockSelectSql().") ss
            GROUP BY product_id
        ) r ON sd.product_id = r.product_id
            
        LEFT JOIN (SELECT sum(d.quantity) AS quantity,d.sale_data_id, min(m.invoice_dt) AS invoice_dt, min(m.sn) AS sn
            FROM stock_delivery_data d
            JOIN stock_delivery m ON d.delivery_id = m.id
            WHERE m.status = 1
            GROUP BY d.sale_data_id
        ) f ON sd.id = f.sale_data_id

        LEFT JOIN customer_cost_category bt ON sd.fee_category_id = bt.id";
    }

    /**
     * 获取销售订单未发货
     * 
     */
    public static function getSaleOrderSelectDetailNotDeliverySql() 
    {
        return "SELECT d.sale_id,
        d.sale_data_id,
        d.product_id,
        d.quantity AS num,
        (d.quantity - d.delivery_quantity) - isnull(yf.yf_num, 0) AS wf_num,
        d.delivery_quantity + isnull(yf.yf_num, 0) AS yf_num,
        d.price,
        CASE WHEN isnull(d.type_id, 0) = 2 THEN '1' ELSE '0' END AS is_gift,
        d.batch_sn,
        d.batch_date,
        d.promotion_sn,
        d.promotion_data_id,
        d.remark,
        d.product_code,
        d.product_spec,
        d.product_name,
        d.product_barcode,
        d.product_unit,
        d.weight,
        d.total_weight,
        d.fee_data_id,
        d.fee_src_id,
        d.fee_src_type_id,
        d.fee_src_sn,
        d.fee_category_id,
        d.fee_category_id_name,
        d.type_id,
        d.type_id_name
        FROM (".static::getSaleOrderDataSql().") d
        LEFT JOIN (SELECT dd.ref_sale_data_id, dd.product_id, sum(dd.quantity) AS yf_num
            FROM customer_order_data dd
            GROUP BY dd.ref_sale_data_id, dd.product_id
        ) yf ON d.sale_data_id = yf.ref_sale_data_id AND d.product_id = yf.product_id

        WHERE (d.quantity - d.delivery_quantity) - isnull(yf.yf_num, 0) > 0 AND isnull(d.use_close, 0) = 0";
    }

    /**
     * 获取销售订单发货表
     * 
     */
    public static function getSaleOrderSelectDetailSql() 
    {
        return "
        SELECT isnull(temp.wf_num, 0) * isnull(temp.weight, 0) AS total_weight,
        temp.*
        FROM (
			SELECT isnull(d.delivery_quantity, 0) AS quantity,
			isnull(sdd.quantity, 0) AS yf_num,
			isnull(sdd.bs, 0) AS yf_bs,
			isnull(d.delivery_quantity, 0) - isnull(sdd.quantity, 0) AS wf_num,
            isnull(e.ky_num, 0) AS ky_num,
            d.product_id,
            d.sale_id,
            d.sale_data_id,
            d.type_id,
            d.type_id_name,
            d.other_money,
            d.price,
            d.money,
            m.sn AS sale_sn,
			CASE WHEN (isnull(d.type_id, 0) = 2) THEN '1' ELSE '0' END AS is_gift,
            d.batch_sn,
            d.batch_date,
            d.fee_data_id,
            d.fee_src_id,
            d.fee_src_type_id,
            d.fee_src_sn,
            d.fee_category_id,
            d.fee_category_id_name,
            d.promotion_sn,
            d.promotion_data_id,
            d.product_name,
            d.product_spec,
            d.product_code,
            d.product_unit,
            d.product_barcode,
            d.material_type,
            d.weight
           FROM (".static::getSaleOrderDataSql().") d
           LEFT JOIN customer_order m ON d.order_id = m.id
						 
           LEFT JOIN (SELECT sum(isnull(quantity, 0)) AS quantity, count(*) AS bs, product_id, sale_data_id
                FROM stock_delivery_data
               GROUP BY sale_data_id, product_id
		    ) sdd ON d.sale_data_id = sdd.sale_data_id
									
           LEFT JOIN (SELECT product_id, sum(isnull(ky_num, 0)) AS ky_num
                FROM (".StockService::getStockSelectSql().") ss
                WHERE (warehouse_name like '%成品%' OR warehouse_code = '07') AND warehouse_code <> '25'
                GROUP BY product_id
			) e ON d.product_id = e.product_id
									
            WHERE isnull(d.use_close, 0) = 0 AND (isnull(d.is_allocate, 0) = 0 OR (isnull(d.is_allocate, 0) = 1 AND isnull(d.material_type, 0) > 0))
	    ) temp
        WHERE isnull(temp.wf_num, 0) > 0 OR (isnull(temp.yf_bs, 0) = 0 AND temp.product_code like '99%')";
    }

    /**
     * 获取销售订单调拨发货
     * 
     */
    public static function getSaleOrderSelectDetailReqSql() 
    {
        return "
        SELECT isnull(temp.wf_num, 0) * isnull(temp.weight, 0) AS total_weight,
        temp.quantity,
        temp.yf_num,
        temp.yf_bs,
        temp.wf_num,
        temp.ky_num,
        temp.product_id,
        temp.sale_id,
        temp.sale_data_id,
        temp.type_id,
        temp.type_id_name,
        temp.other_money,
        temp.price,
        temp.money,
        temp.sale_sn,
        temp.is_gift,
        temp.batch_sn,
        temp.batch_date,
        temp.fee_data_id,
        temp.fee_src_id,
        temp.fee_src_type_id,
        temp.fee_src_sn,
        temp.fee_category_id,
        temp.fee_category_id_name,
        temp.promotion_sn,
        temp.promotion_data_id,
        temp.product_name,
        temp.product_spec,
        temp.product_code,
        temp.product_unit,
        temp.product_barcode,
        temp.product_type,
        temp.material_type,
        temp.weight
        FROM (SELECT isnull(d.delivery_quantity, 0) AS quantity,
            isnull(sdd.quantity, 0) AS yf_num,
            isnull(sdd.bs, 0) AS yf_bs,
            isnull(d.delivery_quantity, 0) - isnull(sdd.quantity, 0) AS wf_num,
            isnull(e.ky_num, 0) AS ky_num,
            d.product_id,
            d.sale_id,
            d.sale_data_id,
            d.type_id,
            d.type_id_name,
            d.other_money,
            d.price,
            d.money,
            m.sn AS sale_sn,
            CASE WHEN (isnull(d.type_id, 0) = 2) THEN '1' ELSE '0' END AS is_gift,
            d.batch_sn,
            d.batch_date,
            d.fee_data_id,
            d.fee_src_id,
            d.fee_src_type_id,
            d.fee_src_sn,
            d.fee_category_id,
            d.fee_category_id_name,
            d.promotion_sn,
            d.promotion_data_id,
            d.product_name,
            d.product_spec,
            d.product_code,
            d.product_unit,
            d.product_barcode,
            d.product_type,
            d.material_type,
            d.weight
            FROM (".static::getSaleOrderDataSql().") d
            LEFT JOIN customer_order m ON d.order_id = m.id
            LEFT JOIN (SELECT sum(isnull(quantity, 0)) AS quantity,
                count(*) AS bs,
                product_id,
                sale_data_id
                FROM stock_allocation_data
                GROUP BY sale_data_id, product_id
            ) sdd ON d.sale_data_id = sdd.sale_data_id
                                        
            LEFT JOIN (SELECT product_id,
                sum(isnull(ky_num, 0)) AS ky_num
                FROM (".StockService::getStockSelectSql().") ss
                WHERE (warehouse_name like '%成品%') AND warehouse_code <> '25'
                GROUP BY product_id
            ) e ON d.product_id = e.product_id

            WHERE isnull(d.use_close, 0) = 0 AND isnull(d.is_allocate, 0) = 1
        ) temp
        WHERE isnull(temp.wf_num, 0) > 0";
    }

    /**
     * 获取赠品其他出库单明细
     * 
     */
    public static function getSampleSelectDetailSql() 
    {
        return "
        SELECT temp.quantity,
        temp.yc_num,
        temp.wc_num,
        temp.ky_num,
        temp.product_id,
        temp.sample_id,
        temp.sample_data_id,
        temp.price,
        temp.money,
        temp.sample_sn,
        temp.use_close,
        isnull(p.weight, 0) AS weight,
        p.name AS product_name,
        p.code AS product_code,
        pc.code AS category_code,
        p.spec AS product_spec,
        pu.name AS product_unit,
        isnull(temp.wc_num, 0) * isnull(p.weight, 0) AS total_weight
        FROM (SELECT isnull(d.quantity, 0) AS quantity,
            isnull(srd.quantity, 0) AS yc_num,
            isnull(d.quantity, 0) - isnull(srd.quantity, 0) AS wc_num,
            isnull(e.ky_num, 0) AS ky_num,
            d.product_id,
            d.sample_id,
            d.id AS sample_data_id,
            d.price,
            d.money,
            m.sn AS sample_sn,
            d.use_close
            FROM sample_apply_data d
            LEFT JOIN sample_apply m ON m.id = d.sample_id
            LEFT JOIN (SELECT sum(isnull(quantity, 0)) AS quantity,product_id,sample_data_id
                FROM stock_record09_data
                WHERE sample_data_id <> 0
                GROUP BY sample_data_id, product_id
            ) srd ON d.id = srd.sample_data_id
                            
            LEFT JOIN (SELECT product_id,sum(isnull(ky_num, 0)) AS ky_num
                FROM (".StockService::getStockSelectSql().") ss
                GROUP BY product_id
            ) e ON d.product_id = e.product_id
                            
            WHERE m.status = 1
        ) temp
        LEFT JOIN product p ON temp.product_id = p.id
        LEFT JOIN product_unit pu ON p.unit_id = pu.id
        LEFT JOIN product_category pc ON pc.id = p.category_id
        WHERE isnull(temp.wc_num, 0) > 0";
    }
}