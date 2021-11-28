<?php namespace Gdoo\Produce\Services;

use DB;

use Gdoo\Stock\Services\StockService;

class ProduceService
{
    /**
     * 获取生产计划
     * 
	 * @date 日期
	 * @department_id 车间
	 * @isCalAgain 是否重新计算
     */
    public static function getMaterialPlanDay($date, $department_id, $isCalAgain)
    {
        $user = auth()->user();

        $department_id = (int)$department_id;
        $isCalAgain = (int)$isCalAgain;

        $rows = [];

        if ($date) {
            if ($isCalAgain) {
                // 删除先有计划
                DB::delete("delete FROM material_plan_day where date = '$date' and dept_id = $department_id");

                // 获取生产计划
                $items = DB::select("
                    SELECT 
                    d.product_id, 
                    d.plan_num,
					c.quantity as material_num,
                    c.material_id,
                    d.department_id,
                    ISNULL(d.plan_num, 0) * ISNULL(c.quantity, 0) + (ISNULL(c.quantity, 0) * ISNULL(c.loss_rate, 0)) AS total_num
                    FROM produce_plan m, produce_plan_data d 
                    left join(
                        select product_id, material_id, quantity, loss_rate
                        from product_material
                    ) c on c.product_id = d.product_id
                    WHERE m.id = d.plan_id
                    AND isnull(department_id, 0) = $department_id
                    AND plan_num <> 0 
                    AND plan_num IS NOT NULL
                    AND m.date = '$date'
                ");

                foreach($items as $item) {
                    DB::table("material_plan_day")->insert([
                        'date' => $date, 
                        'code' => '', 
                        'dept_id' => $department_id, 
                        'product_id' => $item['product_id'], 
                        'product_num' => $item['plan_num'], 
                        'material_id' => $item['material_id'],
                        'material_num' => $item['material_num'],
                        'total_num' => $item['total_num'],
                        'creator_id' => $user['id'], 
                        'creator_name' => $user['name'], 
                        'create_date' => date('Y-m-d H:i:s'), 
                        'remark' => '',
                    ]);
                }
            }

            $rows = DB::select("
                select a.id, a.date,
                a.dept_id,
                e.name as dept_name,
                a.product_id,
                b.name as product_name,
                b.spec as product_spec,
                c.name as product_unit,
                a.product_num,
                a.material_id,
                dc.name as category_name,
                d.name as material_name,
                a.material_num,
                a.total_num,
                a.creator_id,
                a.creator_name,
                a.create_date,
                a.remark
                from material_plan_day a
                left join product b on a.product_id = b.id
                left join product_unit AS c ON b.unit_id = c.id
                left join product d on a.material_id = d.id
                left join product_category dc on dc.id = d.category_id
                left join department e on a.dept_id = e.id
                where a.date = '$date' and a.dept_Id = $department_id
            ");
        }
        return $rows;
    }

    public static function getPreShipDate($order_id, $date) 
    {
        $order_id = (int)$order_id;

        $sql = "a.product_id, sum(a.ky_num) stock_num
        from (".StockService::getStockSelectSql().") a, (select product_id from customer_order_data where order_id = $order_id group by product_id) b
        where a.product_id = b.product_id and (a.warehouse_name like '%成品%' or a.warehouse_code ='07') and a.warehouse_code <> '25'
        group by a.product_id";
        
        $stock = DB::query()->selectRaw($sql)->pluck('stock_num', 'product_id');

        $sql = "product_id, SUM(plan_num) plan_num from(
            SELECT d.product_id,
            SUM(d.delivery_quantity)-ISNULL(SUM(i.num), 0)-ISNULL(SUM(r.num), 0) as plan_num
            FROM customer_order_data AS d 
            LEFT JOIN customer_order AS m ON m.id = d.order_id 
            LEFT JOIN (select dd.sale_data_id, SUM(dd.quantity) num, SUM(dd.money) money, sum(dd.other_money) other_money
                from stock_delivery_data dd, stock_delivery mm
                where mm.id = dd.delivery_id 
                GROUP BY dd.sale_data_id
            ) as i ON i.sale_data_id = d.id
            
            LEFT JOIN (select dd.sale_data_id, SUM(dd.quantity) num from stock_allocation_data dd, stock_allocation mm
                where mm.id = dd.allocation_id
                GROUP BY dd.sale_data_id
            ) as r ON r.sale_data_id = d.id 
                
            where ISNULL(d.use_close, 0) = 0 and m.status > 0";
            
            if ($date) {
                $sql .= " AND m.plan_delivery_dt <= '$date'";
            }
             
            $sql .= " and d.product_id in (select product_id from customer_order_data where order_id = $order_id)
            and d.order_id <> $order_id
            GROUP BY m.id, d.product_id, m.status
            HAVING SUM(d.delivery_quantity)-ISNULL(SUM(i.num),0) - ISNULL(SUM(r.num),0) > 0
        ) aa
        group by product_id";

        $plan = DB::query()->selectRaw($sql)->pluck('plan_num', 'product_id');

        // 获取三天的生产计划
        $start_dt = date('Y-m-d');
        $end_dt = date('Y-m-d', strtotime('+2 day'));

        $sql = "a.product_id, b.date, sum(a.plan_num) plan_num
		from produce_plan_data a
		left join produce_plan b on b.id = a.plan_id
		left join customer_order_data c on c.product_id = a.product_id
		where b.date BETWEEN ? and ? and a.product_id = c.product_id and c.order_id = $order_id
        group by a.product_id, b.date";
        $rows = DB::query()->selectRaw($sql, [$start_dt, $end_dt])->get();
        $days = [];

        $dates = date_range($start_dt, $end_dt);
        foreach($dates as $index => $date) {
            foreach($rows as $row) {
                if ($date == $row['date']) {
                    $days[$index][$row['product_id']] += $row['plan_num'];
                }
            }
        }

        $sql = "order_id, product_id, p.name as product_name, p.spec as product_spec, SUM(delivery_quantity) num
			from customer_order_data 
			left join product as p on p.id = product_id
			where order_id = $order_id
			group by order_id ,product_id, p.name, p.spec
        ";
        $rows = DB::query()->selectRaw($sql)->get();

        $rows->transform(function($row) use ($stock, $plan, $days) {
            $product_id = $row['product_id'];
            $row['product_num'] = $stock[$product_id] - $plan[$product_id];
            $row['need_num'] = $row['num'] - ($stock[$product_id] - $plan[$product_id]);

            $row['day1'] = $days[0][$product_id];
            $row['day2'] = $days[1][$product_id];
            $row['day3'] = $days[2][$product_id];
            return $row;
        });

        return $rows;
    }

    public static function getProducePlanQuantity($date) {
        // 计划日期
        $end_dt = $date;
        // 计划日期两天前
        $start_dt = date('Y-m-d', strtotime($date.' -2 day'));

        $now_month = date('Y-m', strtotime($date));
        $last_month = date('Y-m', strtotime($date.' -1 month'));

        // 查询未发内销订单
        $sql[] = "
        select a.product_id, 
        sum(wf_num) as wf_num, 
        sum(wf_num_ydk) as wf_num_ydk, 
        sum(wf_num_wdk) as wf_num_wdk,
        0 as fhjh_num,
        0 as stock_num,
        0 as ljrk_num,
        0 as syfh_num,
        0 yxjh_num,
		0 as yxjh_num1,
		0 as yxjh_num2,
		0 as plan_num1,
        0 as plan_num2,
        null as batch_sn
        from (
            select d.product_id,
            SUM(d.delivery_quantity) - ISNULL(SUM(sd.quantity), 0) - ISNULL(SUM(sa.quantity), 0) as wf_num,
            CASE WHEN ISNULL(m.pay_dt, null) <> null or m.status = 1 THEN SUM(d.delivery_quantity) - ISNULL(SUM(sd.quantity), 0)-ISNULL(SUM(sa.quantity), 0) ELSE 0 END as wf_num_ydk,
            CASE WHEN ISNULL(m.pay_dt, null) = null or m.status <> 0 THEN SUM(d.delivery_quantity) - ISNULL(SUM(sd.quantity), 0)-ISNULL(SUM(sa.quantity), 0) ELSE 0 END as wf_num_wdk
            from customer_order_data d
            left join customer_order as m ON m.id = d.order_id

            LEFT JOIN (select sdd.sale_data_id, SUM(sdd.quantity) quantity
                from stock_delivery_data sdd, stock_delivery sd
                where sd.id = sdd.delivery_id 
                GROUP BY sdd.sale_data_id
            ) as sd ON sd.sale_data_id = d.id 

            LEFT JOIN (select sad.sale_data_id, SUM(sad.quantity) quantity
                from stock_allocation_data sad, stock_allocation sa
                where sa.id = sad.allocation_id 
                GROUP BY sad.sale_data_id
            ) as sa ON sa.sale_data_id = d.id

            LEFT JOIN product p on d.product_id = p.id
            LEFT JOIN product_category pc on pc.id = p.category_id
            where ISNULL(d.use_close, 0) = 0
            and m.status > 0
            AND isnull(p.is_export, 0) = 0
            GROUP BY d.product_id, ISNULL(m.pay_dt, null), m.status
            HAVING SUM(d.delivery_quantity) - ISNULL(SUM(sd.quantity), 0) - ISNULL(SUM(sa.quantity),0) <> 0
        ) as a

        GROUP BY a.product_id
        ";

        // 内销库存
        $sql[] = "
        select product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        0 as fhjh_num,
        SUM(ISNULL(ky_num, 0)) as stock_num,
        0 as ljrk_num,
        0 as syfh_num,
        0 yxjh_num,
		0 as yxjh_num1,
		0 as yxjh_num2,
		0 as plan_num1,
        0 as plan_num2,
        null as batch_sn
        from (".StockService::getStockSelectSql().") kc
        LEFT JOIN product_category pc on pc.id = category_id
        WHERE product_type = 1 and warehouse_name LIKE '%成品%' and is_export <> 1 and warehouse_code <> '25'
        GROUP by product_id
        HAVING sum(ky_num) <> 0";

        // 外销库存
        $sql[] = "
        select product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        0 as fhjh_num,
        SUM(ISNULL(ky_num, 0)) as stock_num,
        0 as ljrk_num,
        0 as syfh_num,
        0 yxjh_num,
		0 as yxjh_num1,
		0 as yxjh_num2,
		0 as plan_num1,
        0 as plan_num2,
        batch_sn
        from (".StockService::getStockSelectSql().") ss
        LEFT JOIN product_category pc on pc.id = category_id
        WHERE product_type = 1 and warehouse_name LIKE '%成品%' and is_export = 1 and warehouse_code <> '25'
        GROUP by product_id, batch_sn
        HAVING sum(ky_num) <> 0";

		// 查询未发外贸订单
        $sql[] = "
        select a.product_id, 
        sum(wf_num) as wf_num, 
        sum(wf_num_ydk) as wf_num_ydk, 
        sum(wf_num_wdk) as wf_num_wdk,
        0 as fhjh_num,
        0 as stock_num,
        sum(wxrk_num) as ljrk_num,
        0 as syfh_num,
        0 as yxjh_num,
		0 as yxjh_num1,
		0 as yxjh_num2,
		0 as plan_num1,
        0 as plan_num2,
        a.batch_sn
        from (
            select d.product_id, ISNULL(d.batch_sn, null) as batch_sn,
            SUM(ISNULL(y.wxrk_num, 0)) as wxrk_num,
            SUM(d.delivery_quantity)-ISNULL(SUM(sd.quantity),0) - ISNULL(SUM(sa.quantity), 0) as wf_num,
            -- 获取已打款和审核生效未发货的订单
            CASE WHEN ISNULL(m.pay_dt, null) <> null or m.status = 1 THEN SUM(d.delivery_quantity) - ISNULL(SUM(sd.quantity),0) - ISNULL(SUM(sa.quantity), 0) ELSE 0 END as wf_num_ydk,
            -- 获取未打款未审核通过的订单
            CASE WHEN ISNULL(m.pay_dt, null) = null and m.status <> 1 THEN SUM(d.delivery_quantity) - ISNULL(SUM(sd.quantity),0) - ISNULL(SUM(sa.quantity), 0) ELSE 0 END as wf_num_wdk
            from customer_order_data d
            left join customer_order as m ON m.id = d.order_id

            -- 获取发货单
            LEFT JOIN (select sdd.sale_data_id, SUM(sdd.quantity) quantity
                from stock_delivery_data sdd, stock_delivery sd
                where sd.id = sdd.delivery_id 
                GROUP BY sdd.sale_data_id
            ) as sd ON sd.sale_data_id = d.id

            -- 获取调拨单
            LEFT JOIN (select sad.sale_data_id, SUM(sad.quantity) quantity
                from stock_allocation_data sad, stock_allocation sa
                where sa.id = sad.allocation_id 
                GROUP BY sad.sale_data_id
            ) as sa ON sa.sale_data_id = d.id

            -- 外销入库
            LEFT JOIN (select dd.batch_sn, dd.product_id, SUM(dd.quantity) wxrk_num
                from stock_record10_data dd, stock_record10 mm
                where mm.id = dd.record10_id
                GROUP BY dd.batch_sn, dd.product_id
            ) as y ON y.product_id = d.product_id AND y.batch_sn = d.batch_sn

            LEFT JOIN product p on d.product_id = p.id
            LEFT JOIN product_category pc on pc.id = p.category_id
            where ISNULL(d.use_close,0) = 0
            AND m.status > 0
            and p.is_export = 1

            GROUP BY d.product_id, d.batch_sn, ISNULL(m.pay_dt, null), m.status
            HAVING SUM(d.delivery_quantity) - ISNULL(SUM(sd.quantity), 0) - ISNULL(SUM(sa.quantity), 0) <> 0
        ) as a

        GROUP BY a.product_id, a.batch_sn
        ";

        // 内销发货计划
        $sql[] = "
        SELECT d.product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        SUM(d.delivery_quantity) as fhjh_num,
        0 as stock_num,
        0 as ljrk_num,
        0 as syfh_num,
        0 as yxjh_num,
		0 as yxjh_num1,
		0 as yxjh_num2,
		0 as plan_num1,
        0 as plan_num2,
        null as batch_sn
		FROM customer_order_data AS d
		LEFT JOIN customer_order AS m ON m.id = d.order_id 
		LEFT JOIN product p on d.product_id = p.id
		LEFT JOIN product_category pc on pc.id = p.category_id
		where m.plan_delivery_dt BETWEEN '$start_dt' and '$end_dt'
	    AND ISNULL(d.use_close, 0) = 0 AND isnull(p.is_export, 0) = 0
        GROUP BY d.product_id
        ";
        
		// 外销发货计划
        $sql[] = "
        SELECT d.product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        SUM(d.delivery_quantity) as fhjh_num,
        0 as stock_num,
        0 as ljrk_num,
        0 as syfh_num,
        0 as yxjh_num,
		0 as yxjh_num1,
		0 as yxjh_num2,
		0 as plan_num1,
        0 as plan_num2,
        ISNULL(d.batch_sn, null) as batch_sn
		FROM customer_order_data AS d
		LEFT JOIN customer_order AS m ON m.id = d.order_id
		LEFT JOIN product p on d.product_id = p.id
		LEFT JOIN product_category pc on pc.id = p.category_id
		where m.plan_delivery_dt BETWEEN '$start_dt' and '$end_dt'
	    AND ISNULL(d.use_close, 0) = 0 AND p.is_export = 1
        GROUP BY d.product_id, d.batch_sn";

        // 营销计划数量
        // 获取按天计算时间差的sql片段
        $sql_day_diff = sql_day_diff('d.date', $end_dt);
        $sql[] = "
        SELECT product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        0 as fhjh_num,
        0 as stock_num,
        0 as ljrk_num,
        0 as syfh_num,
        sum(yxjh_num) as yxjh_num,
		sum(yxjh_num1) as yxjh_num1,
		sum(yxjh_num2) as yxjh_num2,
		0 as plan_num1,
        0 as plan_num2,
        null as batch_sn
        FROM (
            SELECT d.product_id,
            -- 后天营销
            sum(CASE WHEN ".$sql_day_diff." = 0 THEN ISNULL(d.quantity, 0) ELSE 0 END) as yxjh_num,
            -- 明天营销
            sum(CASE WHEN ".$sql_day_diff." = 1 THEN ISNULL(d.quantity, 0) ELSE 0 END) as yxjh_num1,
            -- 今天营销
            sum(CASE WHEN ".$sql_day_diff." = 2 THEN ISNULL(d.quantity, 0) ELSE 0 END) as yxjh_num2
            FROM produce_data AS d
            where d.date BETWEEN '$start_dt' and '$end_dt'
            GROUP BY d.product_id, d.date
        ) as a 
        GROUP BY a.product_id";

        // 生产计划数量
        // 获取按天计算时间差的sql片段
        $sql_day_diff = sql_day_diff('m.date', $end_dt);
        $sql[] = "
        SELECT product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        0 as fhjh_num,
        0 as stock_num,
        0 as ljrk_num,
        0 as syfh_num,
        0 as yxjh_num,
		0 as yxjh_num1,
		0 as yxjh_num2,
		sum(plan_num1) as plan_num1,
        sum(plan_num2) as plan_num2,
        batch_sn
        FROM (
            SELECT d.product_id, ISNULL(d.batch_sn, null) as batch_sn,
            -- 明天计划数量 
            sum(CASE WHEN ".$sql_day_diff." = 1 THEN ISNULL(d.plan_num, 0) ELSE 0 END) as plan_num1,
            -- 今天计划数量 
            sum(CASE WHEN ".$sql_day_diff." = 2 THEN ISNULL(d.plan_num, 0) ELSE 0 END) as plan_num2
            FROM produce_plan m, produce_plan_data AS d 
            where m.date BETWEEN '$start_dt' and '$end_dt'
            and m.id = d.plan_id
            and m.status = 1
            GROUP BY m.date, d.product_id, d.batch_sn
        ) as a
        GROUP BY a.product_id, a.batch_sn";

        // 入库数量
        $sql[] = "
        SELECT
        d.product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        0 as fhjh_num,
        0 as stock_num,
        SUM(d.quantity) as ljrk_num,
        0 as syfh_num,
        0 as yxjh_num,
        0 as yxjh_num1,
        0 as yxjh_num2,
        0 as plan_num1,
        0 as plan_num2,
        null as batch_sn
        FROM stock_record10 AS m
        INNER JOIN stock_record10_data AS d ON m.id = d.record10_id
        LEFT JOIN product p on d.product_id = p.id
        LEFT JOIN product_category pc on pc.id = p.category_id
        where ".sql_year_month('m.invoice_dt')." = '$now_month'
        and isnull(p.is_export, 0) = 0
        GROUP BY d.product_id";

        // 上月发货数量
        $sql[] = "
        SELECT d.product_id, 
        0 as wf_num, 
        0 as wf_num_ydk, 
        0 as wf_num_wdk,
        0 as fhjh_num,
        0 as stock_num,
        0 as ljrk_num,
        SUM(d.quantity) as syfh_num,
        0 as yxjh_num,
        0 as yxjh_num1,
        0 as yxjh_num2,
        0 as plan_num1,
        0 as plan_num2,
        null as batch_sn
		from stock_delivery as m 
		left join stock_delivery_data as d on m.id = d.delivery_id
		LEFT JOIN product p on d.product_id = p.id
		LEFT JOIN product_category pc on pc.id = p.category_id
		where ".sql_year_month('m.invoice_dt')." = '$last_month'
		and isnull(p.is_export, 0) = 0
		group by d.product_id";

        $sql = "select p.department_id, dep.name department_name, dep.name department_id_name,p.category_id,
			p.id product_id, p.code product_code,p.name product_name,p.spec product_spec, u.name product_unit,
            sum(wf_num) dphz_num,
			sum(yxjh_num) as yxjh_num,
			sum(yxjh_num1) as yxjh_num1,
			sum(yxjh_num2) as yxjh_num2,
			sum(ljrk_num) as ljrk_num,
			sum(plan_num1) as plan_num1,
			sum(plan_num2) as plan_num2,
			sum(fhjh_num) as fhjh_num,
			sum(stock_num) as stock_num,
			ISNULL(sum(wf_num),0) - ISNULL(sum(stock_num),0) as xqzc_num,
			ISNULL(sum(wf_num_ydk),0) - ISNULL(sum(stock_num),0) as dkzc_num,
			sum(syfh_num) as syfh_num, batch_sn
		from product p
		left join (".join(' UNION ALL ', $sql).") as temp on p.id = temp.product_id
		LEFT JOIN product_category pc on pc.id = p.category_id
		LEFT join product_unit AS u ON p.unit_id = u.id
        LEFT join department dep ON p.department_id = dep.id
		where p.product_type = 1 and pc.type = 1 and p.code != ''
		group by p.department_id, dep.name, p.category_id, pc.name, p.id, p.code, p.name, p.spec, u.name, batch_sn
        order by p.code asc";
        return DB::select($sql);
    }

    /**
     * 生产计划(营销)
     * @start_dt
	 * @end_dt
	 * @warehouse_id
	 * @product_category_id
	 * @ny --内销1 外销2
     */

    public static function getPlanDetail($start_dt, $end_dt, $warehouse_id, $product_category_id, $ny)
    {
        $warehouse_id = (int)$warehouse_id;
        $product_category_id = (int)$product_category_id;
        $ny = (int)$ny;

        // 产品编码
        $category_code = DB::table('product_category')->where('id', $product_category_id)->value('code');
        $last_month = date('Y-m', strtotime($start_dt.' -1 month'));

        // 内销订单 
        $sql = "
        SELECT product_id,
        '' batch_sn,
        null invoice_dt,
        0 wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        sum(wf_num) wf_num,
        sum(wf_num_ydk) wf_num_ydk,
        sum(wf_num_wdk) wf_num_wdk, 
        0 waitin_num, 
        0 kc_num,
        0 syfh_num
        from (
            SELECT d.product_id, 
            SUM(d.delivery_quantity)-ISNULL(SUM(i.quantity),0)-ISNULL(SUM(r.quantity),0) as wf_num,
            CASE WHEN ISNULL(m.pay_dt, null) <> null or m.status = 1 then SUM(d.delivery_quantity)-ISNULL(SUM(i.quantity),0)-ISNULL(SUM(r.quantity),0) ELSE 0 end as wf_num_ydk,
            CASE WHEN ISNULL(m.pay_dt, null) = null or m.status <> 0 then SUM(d.delivery_quantity)-ISNULL(SUM(i.quantity),0)-ISNULL(SUM(r.quantity),0) ELSE 0 end as wf_num_wdk
            FROM customer_order AS m
            INNER JOIN customer_order_data AS d ON m.id = d.order_id
            LEFT OUTER JOIN(
            select dd.sale_data_id, SUM(dd.quantity) quantity, SUM(dd.money) money, sum(dd.other_money) other_money
            from stock_delivery_data dd, stock_delivery mm
            where mm.id = dd.delivery_id
            GROUP BY dd.sale_data_id
            ) as i ON i.sale_data_id = d.id  
            LEFT OUTER JOIN (
                select dd.sale_data_id, SUM(dd.quantity) quantity
                from stock_allocation_data dd, stock_allocation mm
                where mm.id = dd.allocation_id
                GROUP BY dd.sale_data_id
            ) as r ON r.sale_data_id = d.id
                
            LEFT JOIN product p on d.product_id = p.id
            LEFT JOIN product_category pc on pc.id = p.category_id
        
            where ISNULL(d.use_close, 0) = 0 
            and m.status > 0 
            AND isnull(p.is_export, 0) = 0
            GROUP BY d.product_id, ISNULL(m.pay_dt, null), m.status 
            HAVING SUM(d.delivery_quantity) - ISNULL(SUM(i.quantity),0) - ISNULL(SUM(r.quantity),0) <> 0
        ) as a
        GROUP BY product_id

        UNION ALL
	
        --未发外销订单
        SELECT product_id,
        batch_sn,
        null invoice_dt,
        0 wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        sum(wf_num) as wf_num,
        sum(wf_num_ydk) as wf_num_ydk,
        sum(wf_num_wdk) as wf_num_wdk,
        0 waitin_num,
        0 kc_num,
        0 syfh_num
        from (
            SELECT d.product_id, d.batch_sn,
            SUM(ISNULL(y.wxrk_num, 0)) as wxrk_num,
            SUM(d.delivery_quantity)-ISNULL(SUM(i.quantity),0)-ISNULL(SUM(r.quantity),0) as wf_num, 
            CASE WHEN ISNULL(m.pay_dt, null) <> null or m.status = 1 then SUM(d.delivery_quantity)-ISNULL(SUM(i.quantity),0)-ISNULL(SUM(r.quantity),0) ELSE 0 end as wf_num_ydk,
            CASE WHEN ISNULL(m.pay_dt, null) = null or m.status <> 0 then SUM(d.delivery_quantity)-ISNULL(SUM(i.quantity),0)-ISNULL(SUM(r.quantity),0) ELSE 0 end as wf_num_wdk 
            FROM customer_order AS m
            INNER JOIN customer_order_data AS d ON m.id = d.order_id
            
            LEFT JOIN (select dd.sale_data_id,SUM(dd.quantity) quantity, SUM(dd.money) money, sum(dd.other_money) other_money
                from stock_delivery_data dd, stock_delivery mm
                where mm.id = dd.delivery_id
                GROUP BY dd.sale_data_id
            ) as i ON i.sale_data_id=d.id

            LEFT JOIN (select dd.sale_data_id,SUM(dd.quantity) quantity
                from stock_allocation_data dd, stock_allocation mm
                where mm.id = dd.allocation_id
                GROUP BY dd.sale_data_id
            ) as r ON r.sale_data_id = d.id

            -- 外销入库
            LEFT JOIN (select dd.batch_sn, dd.product_id, SUM(dd.quantity) wxrk_num
                from stock_record10_data dd, stock_record10 mm
                where mm.id = dd.record10_id
                GROUP BY dd.batch_sn, dd.product_id
            ) as y ON y.product_id = d.product_id AND y.batch_sn = d.batch_sn
            
            LEFT JOIN product p on d.product_id = p.id
            LEFT JOIN product_category pc on pc.id = p.category_id
                
            where ISNULL(d.use_close, 0) = 0
            AND m.status > 0 
            and p.is_export = 1
            GROUP BY d.product_id, d.batch_sn, ISNULL(m.pay_dt, null), m.status
            HAVING SUM(d.delivery_quantity)-ISNULL(SUM(i.quantity),0)-ISNULL(SUM(r.quantity),0) <> 0
        ) as b
        GROUP BY product_id, batch_sn

        UNION ALL

        --内销库存(包含物料)
        SELECT product_id,
        '' batch_sn,
        null invoice_dt,
        0 wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 waitin_num, 
        sum(kc.ky_num) as kc_num,
        0 syfh_num
		from (".StockService::getStockSelectSql().") kc
		LEFT JOIN product p on kc.product_id = p.id
		LEFT JOIN product_category pc on pc.id = p.category_id
		WHERE (p.product_type=1 or p.material_type > 0) and 
		(kc.warehouse_name LIKE '%成品%' or kc.warehouse_code = '07') and kc.warehouse_code <> '25'
	    and ($warehouse_id = 0 OR kc.warehouse_id = $warehouse_id)
	    AND isnull(p.is_export, 0) = 0
		GROUP by kc.product_id 
        HAVING sum(kc.ky_num) <> 0
        
        UNION ALL
	
		--外销库存
        SELECT
        kc.product_id,
        kc.batch_sn,
        null invoice_dt,
        0 wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 waitin_num, 
        sum(kc.ky_num) as kc_num,
        0 syfh_num
		from (".StockService::getStockSelectSql().") kc
		LEFT JOIN product p on kc.product_id = p.id
		LEFT JOIN product_category pc on pc.id = p.category_id
		WHERE p.product_type=1 and kc.warehouse_name LIKE '%成品%' and kc.warehouse_code <> '25'
		AND ($warehouse_id = 0 OR kc.warehouse_id = $warehouse_id)
		AND p.is_export = 1
		GROUP by kc.product_id, kc.batch_sn
        HAVING sum(kc.ky_num) <> 0
        
        UNION ALL
		
        --待入库存
        SELECT
        kc.product_id,
        '' batch_sn,
        null invoice_dt,
        0 wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        sum(kc.ky_num) as waitin_num, 
        0 kc_num,
        0 syfh_num
		from (".StockService::getStockSelectSql().") kc
		LEFT JOIN product p on kc.product_id = p.id
		WHERE p.product_type = 1 and kc.warehouse_code = '22'
	    and ($warehouse_id = 0 OR kc.warehouse_id = $warehouse_id)
		GROUP by kc.product_id
        HAVING sum(kc.ky_num) <> 0
        
        UNION ALL
		
        --上月发货
        SELECT
        d.product_id,
        '' batch_sn,
        null invoice_dt,
        0 wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 waitin_num, 
        0 kc_num,
        sum(d.quantity) as syfh_num
	    from stock_delivery_data d
	    LEFT JOIN stock_delivery m ON d.delivery_id = m.id
	    LEFT JOIN product p on d.product_id = p.id
        WHERE m.id = d.delivery_id
        AND ".sql_year_month('m.invoice_dt')." = '$last_month'
	    GROUP by d.product_id
        HAVING sum(d.quantity) <> 0
        
        UNION ALL

		--内销发货计划
        SELECT
        d.product_id,
        '' batch_sn,
        m.plan_delivery_dt as invoice_dt,
        0 wfhjh_num,
        SUM(d.delivery_quantity) as fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num,
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 waitin_num, 
        0 kc_num,
        0 syfh_num
		FROM customer_order_data AS d
		LEFT JOIN customer_order AS m ON m.id = d.order_id
		LEFT JOIN product p on d.product_id = p.id
		LEFT JOIN product_category pc on pc.id = p.category_id
		where m.plan_delivery_dt between '$start_dt' and '$end_dt'
		AND ISNULL(d.use_close, 0) = 0 and m.status > 0 AND isnull(p.is_export, 0) = 0
        GROUP BY m.plan_delivery_dt, d.product_id

        UNION ALL
        
        --外销发货计划
        SELECT d.product_id,
        d.batch_sn,
        m.plan_delivery_dt as invoice_dt,
        0 wfhjh_num,
        SUM(d.delivery_quantity) as fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 waitin_num,
        0 kc_num,
        0 syfh_num
		FROM customer_order_data AS d
		left JOIN customer_order AS m ON m.id = d.order_id
		LEFT JOIN product p on d.product_id = p.id
        LEFT JOIN product_category pc on pc.id = p.category_id
        where m.plan_delivery_dt between '$start_dt' and '$end_dt'
		AND ISNULL(d.use_close, 0) = 0 and m.status > 0 and p.is_export = 1
        GROUP BY m.plan_delivery_dt, d.product_id, d.batch_sn
        
        UNION ALL

        --发货计划未发
        SELECT product_id,
        '' batch_sn,
        invoice_dt,
        SUM(wfhjh_num) as wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 waitin_num, 
        0 kc_num,
        0 syfh_num
        FROM (
            SELECT d.product_id,
            m.plan_delivery_dt as invoice_dt,
            ISNULL(SUM(d.delivery_quantity), 0) - ISNULL(SUM(i.quantity), 0) - ISNULL(SUM(r.quantity), 0) as wfhjh_num
            FROM customer_order_data AS d
            LEFT JOIN customer_order AS m ON m.id = d.order_id

            LEFT JOIN (select dd.sale_data_id, SUM(dd.quantity) quantity
                from stock_delivery_data dd, stock_delivery mm
                where mm.id = dd.delivery_id
                GROUP BY dd.sale_data_id
            ) as i ON i.sale_data_id = d.id

            LEFT JOIN (select dd.sale_data_id, SUM(dd.quantity) quantity 
                from stock_allocation_data dd, stock_allocation mm
                where mm.id = dd.allocation_id
                GROUP BY dd.sale_data_id
            ) as r ON r.sale_data_id = d.id
            
            LEFT JOIN product p on d.product_id = p.id
            LEFT JOIN product_category pc on pc.id = p.category_id
            
            where m.plan_delivery_dt between '$start_dt' and '$end_dt'
            AND ISNULL(d.use_close, 0) = 0 and m.status > 0 and isnull(p.is_export, 0) = 0
            GROUP BY m.plan_delivery_dt, d.id, d.product_id, i.quantity, r.quantity
            having ISNULL(SUM(d.delivery_quantity), 0) - ISNULL(SUM(i.quantity), 0) - ISNULL(SUM(r.quantity), 0) <> 0
        ) as c
        GROUP BY invoice_dt, product_id

        UNION ALL
        
        --发货计划未发
        SELECT d.product_id,
        '' batch_sn,
        null invoice_dt,
        SUM(wfhjh_num) as wfhjh_num,
        0 fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 as waitin_num,
        0 as kc_num,
        0 as syfh_num
        FROM (
            SELECT d.product_id,
            m.plan_delivery_dt as invoice_dt,
            isnull(SUM(d.delivery_quantity), 0) - isnull(SUM(i.quantity), 0)-isnull(SUM(r.quantity), 0) as wfhjh_num
            FROM customer_order_data AS d
            left JOIN customer_order AS m ON m.id = d.order_id

            LEFT JOIN (select dd.sale_data_id, SUM(dd.quantity) quantity
                from stock_delivery_data dd, stock_delivery mm
                where mm.id = dd.delivery_id
                GROUP BY dd.sale_data_id
            ) as i ON i.sale_data_id = d.id

            LEFT JOIN (select dd.sale_data_id, SUM(dd.quantity) quantity
                from stock_allocation_data dd, stock_allocation mm
                where mm.id = dd.allocation_id 
                GROUP BY dd.sale_data_id
            ) as r ON r.sale_data_id = d.id
            
            LEFT JOIN product p on d.product_id = p.id
            LEFT JOIN product_category pc on pc.id = p.category_id
            
            where m.plan_delivery_dt between '$start_dt' and '$end_dt'
            AND ISNULL(d.use_close,0) = 0 and m.status > 0 and p.is_export = 1
            GROUP BY m.plan_delivery_dt, d.id, d.product_id, i.quantity, r.quantity
            having isnull(SUM(d.delivery_quantity), 0) - isnull(SUM(i.quantity), 0) - isnull(SUM(r.quantity), 0) <> 0
        ) as d
        GROUP BY invoice_dt, product_id

        UNION ALL
        
        --营销计划数量
        SELECT d.product_id,
        '' batch_sn,
        d.date as invoice_dt,
        0 wfhjh_num,
        0 fhjh_num,
        SUM(d.quantity) sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 as waitin_num,
        0 as kc_num,
        0 as syfh_num
        FROM produce_data AS d
        where d.date between '$start_dt' and '$end_dt'
        GROUP BY d.date, d.product_id
        
        UNION ALL
		  
        --生产计划数量
        SELECT d.product_id,
        d.batch_sn,
        m.date as invoice_dt,
        0 as wfhjh_num,
        0 as fhjh_num,
        0 sale_plan_num,
        SUM(d.plan_num) pro_plan_num,
        0 pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 as waitin_num,
        0 as kc_num,
        0 as syfh_num
        FROM produce_plan m, produce_plan_data AS d
        where m.date between '$start_dt' and '$end_dt'
		and m.id = d.plan_id
	    and m.status = 1
	    and plan_num <> 0
        GROUP BY m.date, d.product_id, d.batch_sn

        UNION ALL
        
        --生产计划变更数量
        SELECT d.product_id,
        d.batch_sn,
        m.date as invoice_dt,
        0 as wfhjh_num,
        0 as fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        SUM(d.plan_num) pro_bg_num,
        0 rk_num, 
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 as waitin_num,
        0 as kc_num,
        0 as syfh_num
		FROM produce_plan m, produce_plan_data AS d 
        where m.date between '$start_dt' and '$end_dt'
		and m.id = d.plan_id and m.type = 2
	    and m.status = 1
	    and plan_num <> 0
        GROUP BY m.date, d.product_id, d.batch_sn
        
        UNION ALL
		
        --入库数量
        SELECT d.product_id,
        '' batch_sn,
        m.invoice_dt,
        0 as wfhjh_num,
        0 as fhjh_num,
        0 sale_plan_num,
        0 pro_plan_num,
        0 pro_bg_num,
        SUM(d.quantity) rk_num,
        0 wf_num,
        0 wf_num_ydk,
        0 wf_num_wdk,
        0 as waitin_num,
        0 as kc_num,
        0 as syfh_num
		FROM stock_record10_data AS d
		LEFT JOIN stock_record10 AS m ON m.id = d.record10_id
		LEFT JOIN product p on d.product_id = p.id
        LEFT JOIN product_category pc on pc.id = p.category_id
        where m.invoice_dt between '$start_dt' and '$end_dt'
		and isnull(p.is_export, 0) = 0
        GROUP BY m.invoice_dt, d.product_id";
        
        $dates = date_range($start_dt, $end_dt);
        $sql_col = [];
        foreach ($dates as $date) {
            $_date = str_replace('-', '_', $date);
            $sql_col[] = "sum(case when invoice_dt='$date' then wfhjh_num else 0 end) as wfhjh_num_$_date";
            $sql_col[] = "sum(case when invoice_dt='$date' then fhjh_num else 0 end) as fhjh_num_$_date";
            $sql_col[] = "sum(case when invoice_dt='$date' then sale_plan_num else 0 end) as sale_plan_num_$_date";
            $sql_col[] = "sum(case when invoice_dt='$date' then pro_plan_num else 0 end) as produce_plan_num_$_date";
            $sql_col[] = "sum(case when invoice_dt='$date' then pro_bg_num else 0 end) as produce_bg_num_$_date";
            $sql_col[] = "sum(case when invoice_dt='$date' then rk_num else 0 end) as rk_num_$_date";
        }
	
        $sql2[] = "select p.id,
        pc.id as category_id, 
        pc.code as category_code, 
        pc.name as category_name, 
        p.id as product_id,
        p.code as product_code, 
        p.name as product_name, 
        p.spec as product_spec, 
        Concat(p.name,' ', isnull(p.spec,'')) as product_name_spec, 
        isnull(batch_sn,'') as batch_sn,
        pu.name as product_unit,
        sum(wf_num) dphz_num,
        sum(wf_num_ydk) ydk_num,
        sum(wf_num_wdk) wdk_num, 
        sum(kc_num) kc_num,
        ISNULL(sum(wf_num), 0) - ISNULL(sum(kc_num), 0) as xqzc_num, 
        ISNULL(sum(wf_num_ydk) ,0) as yhk_num,
        ISNULL(sum(wf_num_ydk), 0) - ISNULL(sum(kc_num), 0) as kfzc_num,
        ISNULL(sum(wfhjh_num), 0) - ISNULL(sum(kc_num), 0) as kfjh_num,
        ISNULL(sum(syfh_num), 0) syfh_num,
        ".join("\n,", $sql_col)."
	    from product as p
	    LEFT JOIN (".$sql.") as temp on temp.product_id = p.id
	    LEFT JOIN product_category pc on pc.id = p.category_id
	    LEFT JOIN product_unit pu on pu.id = p.unit_id
        where pc.type = 1 and p.status = 1";

        if ($category_code) {
            $sql2[] = "and pc.code like '$category_code%";
        }
     
        if ($ny == 1) {
            $sql2[] = "and isnull(p.is_export, 0) = 0";
        } else if($ny == 2) {
            $sql2[] = "and p.is_export = 1";
        }

        $sql2[] = "group by pc.id,pc.name,pc.code,p.id,p.code,p.name,isnull(batch_sn,''),p.spec,pu.name
        order by p.code";
        $rows = DB::select(join(' ', $sql2));
        return $rows;
    }
}