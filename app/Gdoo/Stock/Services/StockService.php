<?php namespace Gdoo\Stock\Services;

use DB;

class StockService
{
    /**
     * 商品批次选择(可筛选是否取不满件)
     * 
     * @warehouse_id 仓库ID
     * @product_ids 存货档案ID
     * @value 存货档案编码, 存货档案名称、批次、地区标
     * @customer_id 客户ID
     */
    public static function getBatchSelect($warehouse_id = 0, $product_ids = '', $value = '', $customer_id = 0) 
    {
        $count = 0;
        if ($customer_id > 0) {
            $count = DB::table('customer')
            ->where('id', $customer_id)
            ->whereRaw("RIGHT(name, 1) = 'A'")
            ->count();
        }

        $model = DB::query()->selectRaw('* FROM('.static::getStockSelectSql().') ss')
        ->whereRaw("ISNULL(ky_num, 0) > 0");

        // 获取产品id
        if ($product_ids) {
            $model->whereIn('product_id', explode(',', $product_ids));
        }

        if ($value) {
            $v = join("','", explode(',', $value));
            $model->whereRaw("(
                product_code LIKE '%{$value}%'
                OR product_name LIKE '%{$value}%'
                OR batch_sn IN('{$v}')
            )");
        }

        // 只取成品库和物料库，不包括成品待检验库
        $model->whereRaw("
            (warehouse_name LIKE '%成品%' or warehouse_code LIKE '07')
            and warehouse_code <> '25' -- 成品待检验库
        ");

        // 取仓库
        if ($warehouse_id > 0) {
            $model->where("warehouse_id", $warehouse_id);
        }
        
        // 是否取不满件
        if ($count > 0) {
            $model->whereRaw("
                warehouse_code <> '28' -- 味聚特辣酱不满件库
                and warehouse_code <> '23' -- 成品泡菜不满件库
            ");
        }
        
        $model->orderByRaw('warehouse_code asc, product_code ASC, batch_sn ASC');
        return $model->get();
    }

    /**
     * 商品批次选择
     *
     * @warehouse_id 仓库ID
     * @product_ids 存货档案ID
     * @value 存货档案编码, 存货档案名称、批次、地区标
     * @customer_id 客户ID
     */
    public static function getBatchSelectAll($warehouse_id = 0, $product_id = 0, $value = '', $customer_id = 0) 
    {
        $model = DB::query()->selectRaw('* FROM('.static::getStockSelectSql().') ss')
        ->whereRaw("ISNULL(ky_num, 0) > 0");

        // 获取产品id
        if ($product_id) {
            $model->where('product_id', $product_id);
        }

        if ($value) {
            $model->whereRaw("(
                product_code LIKE '%{$value}%'
                OR product_name LIKE '%{$value}%'
                OR batch_sn LIKE '%{$value}%'
            )");
        }

        // 取仓库
        if ($warehouse_id > 0) {
            $model->where("warehouse_id", $warehouse_id);
        }
        
        $model->orderByRaw('warehouse_code asc, product_code ASC, batch_sn ASC');
        return $model->get();
    }

    /**
     * 商品批次选择(直营)
     * 
     * @warehouse_id 仓库ID
     * @product_ids 存货档案ID
     * @value 存货档案编码, 存货档案名称、批次、地区标
     * @customer_id 客户ID
     */
    public static function getBatchSelectZY($warehouse_id = 0, $product_ids = '', $value = '') 
    {
        $model = DB::query()->selectRaw('* FROM('.static::getStockSelectSql().') ss')
        ->whereRaw("ISNULL(ky_num, 0) > 0");

        // 获取产品id
        if ($product_ids) {
            $model->whereIn('product_id', explode(',', $product_ids));
        }

        if ($value) {
            $v = join("','", explode(',', $value));
            $model->whereRaw("(
                product_code LIKE '%{$value}%'
                OR product_name LIKE '%{$value}%'
                OR batch_sn IN('{$v}')
            )");
        }

        // 取仓库
        if ($warehouse_id > 0) {
            $model->where("warehouse_id", $warehouse_id);
        }
        
        $model->orderByRaw('product_code ASC, batch_sn');
        return $model->get();
    }

    /**
     * 进出存汇总表
     *
     * @warehouse_id 仓库ID
     * @product_code 存货编码
     * @NY 内销1 外贸2
     * @made_start_dt 生产日期起始日期
     * @made_end_dt 生产日期截至日期
     * @user_id 用户id
     * @SFPH 是否显示批号
     * @HBBMJ 是否合并不满件
     */
    public static function reportOrderStockTotal($warehouse_id = 0, $product_code = '', $ny = '', $made_start_dt = '', $made_end_dt = '', $user_id = 0, $SFPH = 0, $HBBMJ = 0) 
    {
        $warehouse_id = (int)$warehouse_id;
        $SFPH = (int)$SFPH;
        $HBBMJ = (int)$HBBMJ;

        $sql = [];
        $sql[] = "select warehouse_code,product_code,batch_sn,batch_date,poscode,posname,warehouse_name,
        product_name,product_spec,unit_name,product_id,warehouse_id,SUM(Num) num,SUM(ky_Num) kynum,SUM(fh_Num) fhnum,SUM(Ck_Num) cknum,SUM(Rk_Num) rknum,SUM(Max_Num) maxnum 
        from (
            SELECT case when {$HBBMJ}=1 AND warehouse_code='23' THEN '10'
                when {$HBBMJ}=1 AND warehouse_code='27' THEN '21'
                when {$HBBMJ}=1 AND warehouse_code='28' THEN '11' ELSE warehouse_code end AS warehouse_code,
                    rd.product_code,batch_sn,batch_date,
            case when {$HBBMJ}=1 and poscode='91' THEN '99'
                when {$HBBMJ}=1 and poscode='92' THEN '98'
                when {$HBBMJ}=1 and poscode='93' THEN '98' ELSE poscode end AS poscode,
            case when {$HBBMJ}=1 and posname='不满件' THEN '小菜'
                when {$HBBMJ}=1 and posname='川南辣酱不满件' THEN '辣酱'
                when {$HBBMJ}=1 and posname='味聚特辣酱不满件' THEN '辣酱' ELSE posname end as posname,
            case when {$HBBMJ}=1 and warehouse_name='成品不满件库' THEN '成品库小菜'
                when {$HBBMJ}=1 and warehouse_name='川南辣酱不满件库' THEN '川南酱库'
                when {$HBBMJ}=1 and warehouse_name='味聚特辣酱不满件库' THEN '成品库酱' ELSE warehouse_name end AS warehouse_name, rd.product_name,rd.product_spec,rd.unit_name,rd.product_id,
            CASE WHEN {$HBBMJ}=1 AND warehouse_id=20005 THEN 139
                WHEN {$HBBMJ}=1 AND warehouse_id=20047 THEN 20001
                WHEN {$HBBMJ}=1 AND warehouse_id=20048 THEN 140 ELSE warehouse_id end AS warehouse_id,Num,ky_Num,fh_Num,Ck_Num,Rk_Num,Max_Num 
            FROM (".StockService::getStockSelectSql().") rd
            left join (
                select i.product_id, i.product_name,i.product_spec,i.unit_name,invc.NY, i.product_code from (
                select id, case substring(name, 0, 3) when '外销' then 2 else 1 end as NY
                from product_category) as invc right join (
                    SELECT a.id as product_id, a.name as product_name,a.spec as product_spec, b.name as unit_name, a.code as product_code, a.category_id
                    FROM product a
                    LEFT JOIN product_unit b ON a.unit_id = b.id) i on i.category_id = invc.id
                ) as ic on rd.product_id = ic.product_id
                WHERE (ISNULL(ky_Num,0) <> 0 OR ISNULL(fh_Num,0) <> 0 or Ck_Num<>0 or Rk_Num<>0)";
            
                if ($ny) {
                    $sql[] = "and (ic.NY = {$ny})";
                }

                if ($made_start_dt && $made_end_dt) {
                    $sql[] = "and (batch_date >= '$made_start_dt') and (batch_date <= '$made_end_dt')";
                }
            
                $sql[] = "AND (({$warehouse_id} = 0 OR warehouse_id = {$warehouse_id} OR (
                    ({$HBBMJ}=1 and warehouse_id = 20005 AND {$warehouse_id} = 139) OR ({$HBBMJ} = 1 and warehouse_id = 20047 AND {$warehouse_id} = 20001) OR ({$HBBMJ} = 1 and warehouse_id = 20048 AND {$warehouse_id} = 140))
                ) and warehouse_id in (
                    SELECT uwh.warehouse_id FROM user_warehouse uwh LEFT JOIN warehouse wh ON uwh.warehouse_id = wh.id where uwh.user_id = {$user_id})
                )";

                if ($product_code) {
                    $sql[] = "and (rd.product_code LIKE '%{$product_code}%')";
                }
            
            $sql[] = ") as a
            GROUP BY warehouse_code,product_code,batch_sn,batch_date,poscode,posname,warehouse_name,
            product_name,product_spec,unit_name,product_id,warehouse_id 
            order by warehouse_code desc,product_code,batch_sn";

        return DB::select(join(" ", $sql));
    }

    /**
     * 进销存汇总表
     *
     * @warehouse_id 仓库ID
     * @product_id 存货ID
     * @batch_sn 批号
     * @ny 内销  外销，''-全部 
     * @start_dt 起始日期
     * @end_dt 截至日期
     * @user_id 用户ID
     * @SFPH 是否显示批号
     * @HBBMJ 是否合并不满件
     */
    public static function reportOrderStockInOut($warehouse_id = 0, $product_id = 0, $batch_sn = '', $ny = '', $start_dt = '', $end_dt = '', $user_id = 0, $SFPH = 0, $HBBMJ = 0) 
    {
        $warehouse_id = (int)$warehouse_id;
        $product_id = (int)$product_id;
        $SFPH = (int)$SFPH;
        $HBBMJ = (int)$HBBMJ;

        $invoice_dt = sql_year_month_day('m.invoice_dt');

        // 期初
        $sqlqc = "
        warehouse_id,
        product_id,
        isnull(poscode,'') as poscode,
        isnull(posname,'') as posname,
        isnull(batch_sn,'') as batch_sn,
        batch_date,
        0 RkNum,
        0 Rknum_Sc,
        0 Rknum_Qt,
        0 Rknum_Qr,
        0 Rknum_Th, 
        0 Rknum_No,
        0 Cknum,
        0 Cknum_Fh,
        0 Cknum_Zy,
        0 Cknum_Qt,
        0 Cknum_Dc,
        0 Cknum_No,
        SUM(Num) QcNum
        from (
  		    SELECT SUM(d.quantity) AS num, m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			FROM stock_record10 m
			LEFT JOIN stock_record10_data d ON m.id = d.record10_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
			and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))
			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' ='' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL
			
			SELECT SUM(d.quantity) AS RkNum, m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_record08 m
			LEFT JOIN  stock_record08_data d ON m.id = d.record08_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
			($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))
			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL
			
			SELECT SUM(0-d.quantity),
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			
			FROM stock_record09 m
			LEFT JOIN  stock_record09_data d ON m.id = d.record09_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
                OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' ='' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL
			
			SELECT SUM(0-d.quantity),
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_record11 m
			LEFT JOIN  stock_record11_data d ON m.id = d.record11_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
                OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' ='' OR d.batch_sn = '$batch_sn')
			and ('$ny' ='' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL
			
			SELECT SUM(0-d.quantity) AS RkNum, out_warehouse_id,d.product_id,d.batch_sn,d.out_poscode,d.out_posname,d.batch_date
			FROM  stock_allocation m
			LEFT JOIN  stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." < '$start_dt'
            
            and ($warehouse_id = 0 OR m.out_warehouse_id = $warehouse_id OR (
                ($HBBMJ=1 AND m.out_warehouse_id=20005 AND $warehouse_id=139)
                OR ($HBBMJ=1 AND m.out_warehouse_id=20047 AND $warehouse_id=20001)
                OR ($HBBMJ=1 AND m.out_warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.out_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.out_warehouse_id,d.product_id,d.batch_sn,d.out_poscode,d.out_posname,batch_date
			
			UNION ALL
			
			SELECT SUM(d.quantity) AS RkNum, in_warehouse_id,d.product_id,d.batch_sn,d.in_poscode,d.in_posname,d.batch_date
			FROM stock_allocation m
			LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." < '$start_dt' 
            
            and ($warehouse_id = 0 OR m.in_warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND m.in_warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND m.in_warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND m.in_warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' ='' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.in_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.in_warehouse_id,d.product_id,d.batch_sn,d.in_poscode,d.in_posname,batch_date
			
			UNION ALL
			
			-- 发货数量
			SELECT
			SUM(0-d.quantity) Cknum, 
			d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_delivery_data d 
			LEFT JOIN stock_delivery m on m.id = d.delivery_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
            and ($warehouse_id = 0 OR d.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' ='' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			
			UNION ALL
			
			SELECT 
			SUM(0-d.quantity) Cknum, m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_direct_data d 
			LEFT JOIN stock_direct m on m.id = d.direct_id 
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
            and ($warehouse_id = 0 OR m.warehouse_id = $warehouse_id OR (
                ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139)
                OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' ='' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			
			UNION ALL 
			
			-- 退货数量
			SELECT SUM(0-d.quantity) AS RkNum, d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_cancel_data d 
			left join stock_cancel m on m.id = d.cancel_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL and m.status = 1
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
            and ($warehouse_id = 0 OR d.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139)
                OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))
            and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
            and ('$ny' ='' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id=$product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
		) as a 
		group by warehouse_id, product_id, isnull(batch_sn,''), isnull(poscode,''), isnull(posname,''), batch_date
        having SUM(Num) <> 0";

        $sqlfs = "
        warehouse_id,
        product_id,
        isnull(poscode,'') as poscode,
        isnull(posname,'') as posname,
        isnull(batch_sn,'') as batch_sn,
        batch_date,
        SUM(RkNum) RkNum,
        SUM(Rknum_Sc) Rknum_Sc,
        SUM(Rknum_Qt) Rknum_Qt,
        SUM(Rknum_Qr) Rknum_Qr,
        SUM(Rknum_Th) Rknum_Th, 
        SUM(Rknum_No) Rknum_No,
        SUM(Cknum) Cknum,
        SUM(Cknum_Fh) Cknum_Fh,
        SUM(Cknum_Zy) Cknum_Zy,
        SUM(Cknum_Qt) Cknum_Qt,
        SUM(Cknum_Dc) Cknum_Dc,
        SUM(Cknum_No) Cknum_No,
        0 QcNum
        from (
  		    SELECT SUM(d.quantity) AS RkNum,
			SUM(d.quantity) Rknum_Sc,
			0 Rknum_Qt,
			0 Rknum_Qr,
			0 Rknum_Th,
			SUM(ISNULL(case when ISNULL(m.status, 0) <> 1 then d.quantity ELSE 0 end,0)) Rknum_No,
			0 Cknum,
			0 Cknum_Fh, 
			0 Cknum_Zy,
			0 Cknum_Qt, 
			0 Cknum_Dc, 
            0 Cknum_No,
            m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			FROM stock_record10 m
			LEFT JOIN stock_record10_data d ON m.ID = d.record10_id
			
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
            AND ".$invoice_dt." <= '$end_dt'
            
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL
			
			SELECT SUM(d.quantity) AS RkNum,
			0 Rknum_Sc,
			SUM(d.quantity) Rknum_Qt,
			0 Rknum_Qr,
			0 Rknum_Th,
			SUM(ISNULL(case when ISNULL(m.status,0) <> 1 then d.quantity ELSE 0 end, 0)) Rknum_No,
			0 Cknum,
			0 Cknum_Fh, 
			0 Cknum_Zy,
			0 Cknum_Qt, 
			0 Cknum_Dc, 
			0 Cknum_No,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			FROM stock_record08 m
			LEFT JOIN stock_record08_data d ON m.id = d.record08_id
			
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
            AND ".$invoice_dt." <= '$end_dt'
            
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139)
                OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
                OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn'='' OR d.batch_sn = '$batch_sn')
			and ('$ny'='' OR (pc.name like '%外销%' AND '$ny'='外销') OR  (pc.name not like '%内销%' AND '$ny'='内销') )
			and ($product_id = 0 OR d.product_id=$product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL
			
			SELECT 0 AS RkNum,
			0 Rknum_Sc,
			0 Rknum_Qt,
			0 Rknum_Qr,
			0 Rknum_Th,
			0 Rknum_No,
			SUM(d.quantity) Cknum,
			0 Cknum_Fh, 
			0 Cknum_Zy,
			SUM(d.quantity) Cknum_Qt, 
			0 Cknum_Dc, 
			SUM(ISNULL(case when ISNULL(m.status,0) <> 1 then d.quantity ELSE 0 end,0)) Cknum_No,
			
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date

			FROM stock_record09 m
			LEFT JOIN stock_record09_data d ON m.id = d.record09_id
			
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
            AND ".$invoice_dt." <= '$end_dt'
			
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139)
                OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
                OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL 
			
			SELECT 0 AS RkNum,
			0 Rknum_Sc,
			0 Rknum_Qt,
			0 Rknum_Qr,
			0 Rknum_Th,
			0 Rknum_No,
			SUM(d.quantity) Cknum,
			0 Cknum_Fh, 
			0 Cknum_Zy,
			0 Cknum_Qt, 
			SUM(d.quantity) Cknum_Dc, 
			SUM(ISNULL(case when ISNULL(m.status,0) <> 1 then d.quantity ELSE 0 end,0)) Cknum_No,
			m.out_warehouse_id,d.product_id,d.batch_sn,d.out_poscode,d.out_posname,batch_date
			
			FROM stock_allocation m
			LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id=d.product_id
            LEFT JOIN product_category pc on p.category_id = pc.id
            WHERE ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." >= '$start_dt'
            AND ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." <= '$end_dt'
            
            and ($warehouse_id = 0 OR m.out_warehouse_id=$warehouse_id OR (                                        
                ($HBBMJ=1 AND m.out_warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND m.out_warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND m.out_warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.out_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.out_warehouse_id,d.product_id,d.batch_sn,d.out_poscode,d.out_posname,batch_date
			
			UNION ALL
			
			SELECT SUM(d.quantity) AS RkNum,
			0 Rknum_Sc,
			0 Rknum_Qt,
			SUM(d.quantity) Rknum_Qr,
			0 Rknum_Th,
			SUM(ISNULL(case when ISNULL(m.status,0) <> 1 then d.quantity ELSE 0 end,0)) Rknum_No,
			0 Cknum,
			0 Cknum_Fh, 
			0 Cknum_Zy,
			0 Cknum_Qt, 
			0 Cknum_Dc, 
			0 Cknum_No,
			m.in_warehouse_id,d.product_id,d.batch_sn,d.in_poscode,d.in_posname,batch_date
			FROM stock_allocation m
			LEFT JOIN  stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id=d.product_id
            LEFT JOIN product_category pc on p.category_id = pc.id
            WHERE ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." >= '$start_dt'
            AND ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." <= '$end_dt'
			--WHERE CONVERT(varchar(10), case when isnull(m.delivery_dt, '') <> '' then m.delivery_dt else m.invoice_dt end, 121)>=@SdDate
			--AND CONVERT(varchar(10), case when isnull(m.delivery_dt, '') <> '' then m.delivery_dt else m.invoice_dt end, 121)<=@EdDate
            
            and ($warehouse_id = 0 OR m.in_warehouse_id=$warehouse_id OR (                                        
                ($HBBMJ=1 AND m.in_warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND m.in_warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND m.in_warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.in_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.in_warehouse_id,d.product_id,d.batch_sn,d.in_poscode,d.in_posname,batch_date
			
			UNION ALL
			
			-- 发货数量
			SELECT 0 AS RkNum,
			0 Rknum_Sc,
			0 Rknum_Qt,
			0 Rknum_Qr,
			0 Rknum_Th,
			0 Rknum_No,
			SUM(d.quantity) Cknum,
			SUM(d.quantity) Cknum_Fh, 
			0 Cknum_Zy,
			0 Cknum_Qt, 
			0 Cknum_Dc, 
			SUM(ISNULL(case when ISNULL(m.status,0) <> 1 then d.quantity ELSE 0 end,0)) Cknum_No,
			d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			FROM stock_delivery_data d 
			LEFT JOIN stock_delivery m on m.id = d.delivery_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
            AND ".$invoice_dt." <= '$end_dt'
            
			and ($warehouse_id = 0 OR d.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销') )
			and ($product_id = 0 OR d.product_id = $product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL
			
			SELECT 0 AS RkNum,
			0 Rknum_Sc,
			0 Rknum_Qt,
			0 Rknum_Qr,
			0 Rknum_Th,
			0 Rknum_No,
			SUM(d.quantity) Cknum,
			0 Cknum_Fh, 
			SUM(d.quantity) Cknum_Zy,
			0 Cknum_Qt, 
			0 Cknum_Dc,
			SUM(ISNULL(case when ISNULL(m.status, 0) <> 1 then d.quantity ELSE 0 end,0)) Cknum_No,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			FROM stock_direct_data d 
			LEFT JOIN stock_direct m on m.id = d.direct_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
            AND ".$invoice_dt." <= '$end_dt'
            
			and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (                                        
                ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
                OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
                OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			
			UNION ALL 
			
			-- 退货数量
			SELECT 0 AS RkNum,
			0 Rknum_Sc,
			0 Rknum_Qt,
			0 Rknum_Qr,
			0 Rknum_Th,
			0 Rknum_No,
			SUM(d.quantity) Cknum,
			SUM(d.quantity) Cknum_Fh,
			0 Cknum_Zy,
			0 Cknum_Qt, 
			0 Cknum_Dc, 
			SUM(ISNULL(case when ISNULL(m.status,0) <> 1 then d.quantity ELSE 0 end,0)) Cknum_No,
			d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
			FROM stock_cancel_data d 
			left join stock_cancel m on m.id = d.cancel_id
			LEFT JOIN product p on p.id=d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL and m.status = 1
			AND ".$invoice_dt." >= '$start_dt'
            AND ".$invoice_dt." <= '$end_dt'
            
			and ($warehouse_id = 0 OR d.warehouse_id=$warehouse_id OR (
                ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139)
                OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)
                OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id=$product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,batch_date
		) as b
        group by warehouse_id,product_id,isnull(poscode,''),isnull(posname,''),isnull(batch_sn,''),batch_date";
        
        $sql[] = "
        w.id as warehouse_id,
        w.code as warehouse_code,
        w.name as warehouse_name,
        c.product_id,
        i.code as product_code,
        i.name as product_name,
        i.spec as product_spec,
        g.name as product_unit, 
        isnull(c.poscode,'') as poscode, 
        isnull(c.posname,'') as posname,";
    
        if ($SFPH == 1) {
            $sql[] = "isnull(c.batch_sn,'') as batch_sn, c.batch_date,";
        }

        $sql[] = "sum(QcNum) as qc_num,
        SUM(RkNum) rk_num,
        SUM(Rknum_Sc) rk_num_sc,
        SUM(Rknum_Qt) rk_num_qt,
        SUM(Rknum_Qr) rk_num_qr,
        SUM(Rknum_Th) rk_num_th, 
        SUM(Rknum_No) rk_num_no,
        SUM(Cknum) ck_num,
        SUM(Cknum_Fh) ck_num_fh,
        SUM(Cknum_Zy) ck_num_zy,
        SUM(Cknum_Qt) ck_num_qt,
        SUM(Cknum_Dc) ck_num_dc,
        SUM(Cknum_No) ck_num_no,
        ISNULL(sum(QcNum),0)+ISNULL(SUM(RkNum),0)-isnull(SUM(CkNum),0) as qm_num
        from (
            SELECT ".$sqlfs."
            union all
            SELECT ".$sqlqc."
        ) as c, product i,product_unit g,warehouse w
        where i.id = c.product_id and g.id = i.unit_id AND w.id=c.warehouse_id
        group by w.id,w.code,w.name,c.product_id,i.code,i.name,i.spec,g.name,isnull(c.poscode,''),isnull(c.posname,'')
        ";

        if ($SFPH == 1) {
            $sql[] = ",isnull(c.batch_sn,''),c.batch_date";
        }

        $sql[] = "order by w.code,i.code";
        
        if ($SFPH == 1) {
            $sql[] = ", isnull(c.batch_sn,'')";
        }
        $rows = DB::query()->selectRaw(join(" ", $sql))->get();
        return $rows;
    }

    /**
     * 库存明细表
     *
     * @warehouse_id 仓库ID
     * @product_id 存货ID
     * @batch_sn 批号
     * @ny 内销 外销，''-全部 
     * @start_dt 起始日期
     * @end_dt 截至日期
     * @user_id 用户ID
     * @HBBMJ 是否合并不满件库
     */
    public static function reportOrderStockDetail($warehouse_id = 0, $product_id = 0, $batch_sn = '', $ny = '', $start_dt = '', $end_dt = '', $user_id = 0, $HBBMJ = 0) 
    {
        $warehouse_id = (int)$warehouse_id;
        $product_id = (int)$product_id;
        $HBBMJ = (int)$HBBMJ;
        $user_id = (int)$user_id;

        // 期初
        $sql = "isnull(SUM(Num), 0) as qcnum
        from (
  		SELECT SUM(d.quantity) AS Num, m.warehouse_id, d.product_id, d.batch_sn, d.poscode, d.posname, batch_date
			FROM stock_record10 m
			LEFT JOIN stock_record10_data d ON m.id = d.record10_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
			
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			
			UNION ALL
			
			-- 原材料入库
			SELECT SUM(d.quantity) AS Num, m.warehouse_id, d.product_id, d.batch_sn, d.poscode, d.posname, batch_date
			FROM stock_record01 m
			LEFT JOIN stock_record01_data d ON m.id = d.record01_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
			
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))
																				
			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			
			UNION ALL
			
			SELECT SUM(d.quantity) AS RkNum, 
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_record08 m
			LEFT JOIN stock_record08_data d ON m.id = d.record08_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'

            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			
			UNION ALL
			
			SELECT SUM(0-d.quantity) as Num,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_record09 m
			LEFT JOIN  stock_record09_data d ON m.id = d.record09_id 
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
			and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139)
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date																	

			UNION ALL
			
			-- 原材料出库
			SELECT SUM(0-d.quantity) as Num,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_record11 m
			LEFT JOIN  stock_record11_data d ON m.id = d.record11_id 
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
            
			and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (                                        
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id= $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)
			GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date																	

			UNION ALL
			
			-- 调拨出
			SELECT SUM(0-d.quantity) AS RkNum, 
			m.out_warehouse_id,d.product_id,batch_sn,d.out_poscode,d.out_posname,d.batch_date
			FROM stock_allocation m
			LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." < '$start_dt'
            
            and ($warehouse_id = 0 OR m.out_warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.out_warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.out_warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.out_warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.out_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY d.product_id,m.out_warehouse_id,d.batch_sn,d.out_poscode,d.out_posname,d.batch_date
			
			UNION ALL
			
			SELECT  SUM(d.quantity) AS RkNum, 
			m.in_warehouse_id,d.product_id,d.batch_sn,d.in_poscode,d.in_posname,d.batch_date
			FROM stock_allocation m
			LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id = d.product_id
            LEFT JOIN product_category pc on p.category_id = pc.id
            WHERE ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." < '$start_dt'
			
            and ($warehouse_id = 0 OR m.in_warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.in_warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.in_warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.in_warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销')OR  (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.in_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY d.product_id,m.in_warehouse_id,d.batch_sn,d.in_poscode,d.in_posname,d.batch_date
			
			UNION ALL
			
			-- 发货数量
			SELECT  
			SUM(0-d.quantity) Cknum, 
			d.warehouse_id,d.product_id, d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_delivery_data d 
			LEFT JOIN stock_delivery m on m.id = d.delivery_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
            WHERE d.product_id IS NOT NULL 
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
			
            and ($warehouse_id = 0 OR d.warehouse_id=$warehouse_id OR (                                        
            ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id=$product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)     
			GROUP BY d.product_id,d.batch_sn,d.warehouse_id,d.poscode,d.posname,d.batch_date 
			
			UNION ALL
			
			-- 直营发货
			SELECT 
			SUM(0-d.quantity) Cknum, 
			m.warehouse_id,d.product_id, d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_direct_data d LEFT JOIN stock_direct m on m.id = d.direct_id 
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL 
			AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
				
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id=$product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)
			GROUP BY d.product_id,d.batch_sn,m.warehouse_id,d.poscode,d.posname,d.batch_date
			
			UNION ALL 
			
			-- 退货数量
			SELECT SUM(0-d.quantity) AS RkNum, 
			d.warehouse_id,d.product_id, d.batch_sn,d.poscode,d.posname,d.batch_date
			FROM stock_cancel_data d left join stock_cancel m on m.id = d.cancel_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id  
            WHERE d.product_id IS NOT NULL and m.status = 1
            AND ".sql_year_month_day('m.invoice_dt')." < '$start_dt'
			
            and ($warehouse_id = 0 OR d.warehouse_id=$warehouse_id OR (                                        
            ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id=$product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			GROUP BY d.product_id,d.batch_sn,d.warehouse_id,d.poscode,d.posname,d.batch_date
        ) as a 
        having SUM(Num) <> 0";
        $qcnum = DB::query()->selectRaw($sql)->value('qcnum');

        $invoice_dt = sql_year_month_day('m.invoice_dt');
        $sql = "
        c.product_id,
        c.warehouse_id,
        c.id,
        c.sn,
        c.invoice_dt,
        w.code as warehouse_code,
        w.name as warehouse_name,
        p.code as product_code, 
        p.name as product_name, 
        p.spec as product_spec, 
        g.name as unit_name,
		isnull(batch_sn,'') as batch_sn,
		isnull(c.poscode,'') as poscode,
		isnull(c.posname,'') as posname,
		isnull(batch_date, null) batch_date,
        c.bill_type,
        c.bill_name,
        c.RkNum as rk_num,
        c.CkNum as ck_num,
        0 as qm_num
        from (
  		SELECT d.quantity AS RkNum, 0 CkNum,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_record10' as bill_type,'产成品入库' as bill_name, m.id, m.sn,
			m.invoice_dt
			FROM stock_record10 m
			LEFT JOIN stock_record10_data d ON m.id = d.record10_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (                                        
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id=$product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			
			UNION ALL
			
			SELECT d.quantity AS RkNum, 0 CkNum,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_record08' as bill_type,'其他入库' as bill_name,m.id,m.sn,
			m.invoice_dt
			FROM stock_record08 m
			LEFT JOIN stock_record08_data d ON m.id = d.record08_id 
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139)
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny'='外销') OR (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0 OR d.product_id=$product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id=$user_id)
			
			UNION ALL
			
			SELECT d.quantity AS RkNum, 0 CkNum,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_record01' as bill_type,'原材料入库' as bill_name,m.id,m.sn,
			m.invoice_dt
			FROM stock_record01 m
			LEFT JOIN stock_record01_data d ON m.id = d.record01_id 
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
            and ($warehouse_id = 0 OR m.warehouse_id=$warehouse_id OR (                                        
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)
			
			UNION ALL
			
			SELECT 0 AS RkNum, d.quantity CkNum,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_record09' as bill_type,'其他出库' as bill_name,m.id,m.sn,m.invoice_dt
			FROM stock_record09 m
			LEFT JOIN  stock_record09_data d ON m.id = d.record09_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
            WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
			and ($warehouse_id = 0 OR m.warehouse_id = $warehouse_id OR (                                        
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id = 139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id = 20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id = 140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销')OR  (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id) 
			
			UNION ALL 
			
			SELECT 0 AS RkNum, d.quantity CkNum,
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_record11' as bill_type,'原材料出库' as bill_name,m.id,m.sn,m.invoice_dt
			FROM stock_record11 m
			LEFT JOIN stock_record11_data d ON m.id = d.record11_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
			and ($warehouse_id = 0 OR m.warehouse_id = $warehouse_id OR (
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id = 139)
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id = 20001)
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id = 140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id) 
			
			UNION ALL 
			
			SELECT 0 AS RkNum,
			d.quantity Cknum, 
			m.out_warehouse_id,d.product_id,d.batch_sn,d.out_poscode,d.out_posname,d.batch_date,
			'stock_allocation' as bill_type,'产成品调拨' as bill_name, m.id, m.sn, m.invoice_dt
			FROM stock_allocation m
			LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			
			where ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." >= '$start_dt'
			AND ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." <= '$end_dt'
				 
            and ($warehouse_id = 0 OR m.out_warehouse_id = $warehouse_id OR (                                        
            ($HBBMJ=1 AND m.out_warehouse_id=20005 AND $warehouse_id = 139) 
            OR ($HBBMJ=1 AND m.out_warehouse_id=20047 AND $warehouse_id = 20001)
            OR ($HBBMJ=1 AND m.out_warehouse_id=20048 AND $warehouse_id = 140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.out_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)
			
			UNION ALL
			
			SELECT d.quantity AS RkNum, 
			0 Cknum, 
			m.in_warehouse_id,d.product_id,d.batch_sn,d.in_poscode,d.in_posname,d.batch_date,
			'stock_allocation' as bill_type,'产成品调拨' as bill_name, m.id,m.sn, m.invoice_dt
			
			FROM stock_allocation m
			LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			
			where ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." >= '$start_dt'
			AND ".sql_year_month_day("case when m.delivery_dt <> null then m.delivery_dt else m.invoice_dt end")." <= '$end_dt'
				 
		 	--and (ISNULL(@cWhID,'')='' OR m.in_warehouse_id = @cWhID OR ($HBBMJ=1 AND m.in_warehouse_id=20005))
			
            and ($warehouse_id = 0 OR m.in_warehouse_id = $warehouse_id OR (
            ($HBBMJ=1 AND m.in_warehouse_id = 20005 AND $warehouse_id = 139)
            OR ($HBBMJ=1 AND m.in_warehouse_id = 20047 AND $warehouse_id = 20001)
            OR ($HBBMJ=1 AND m.in_warehouse_id = 20048 AND $warehouse_id = 140)
            ))

			--and ('$batch_sn' = '' OR d.cBatch = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.in_warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)
			
			UNION ALL
			
			-- 发货数量
			
			SELECT 0 AS RkNum,
			d.quantity Cknum,
			d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_delivery' as bill_type,'发货' as bill_name, m.id, m.sn,
			m.invoice_dt
			FROM stock_delivery_data d
			LEFT JOIN stock_delivery m on m.id = d.delivery_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
            and ($warehouse_id = 0 OR d.warehouse_id = $warehouse_id OR (                                        
            ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销')OR  (pc.name not like '%内销%' AND '$ny'='内销'))
			and ($product_id = 0  OR d.product_id = $product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)   
			
			UNION ALL
			
			SELECT 0 AS RkNum, 
			d.quantity Cknum, 
			m.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_direct' as bill_type,'发货(直营)' as bill_name,m.id,m.sn,
			m.invoice_dt
			FROM stock_direct_data d 
			LEFT JOIN stock_direct m on m.id = d.direct_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
            and ($warehouse_id = 0 OR m.warehouse_id = $warehouse_id OR (                                        
            ($HBBMJ=1 AND m.warehouse_id=20005 AND $warehouse_id=139) 
            OR ($HBBMJ=1 AND m.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND m.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and m.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id)  
			
			UNION ALL 
			
			-- 退货数量
			SELECT 0 AS RkNum,
			d.quantity Cknum, 
			d.warehouse_id,d.product_id,d.batch_sn,d.poscode,d.posname,d.batch_date,
			'stock_cancel' as bill_type,'退货申请' as bill_name,m.id,m.sn,
			m.invoice_dt
			FROM stock_cancel_data d 
			left join stock_cancel m on m.id = d.cancel_id
			LEFT JOIN product p on p.id = d.product_id
			LEFT JOIN product_category pc on p.category_id = pc.id
			WHERE d.product_id IS NOT NULL
			AND ".$invoice_dt." >= '$start_dt'
			AND ".$invoice_dt." <= '$end_dt'
			
			and ($warehouse_id = 0 OR d.warehouse_id=$warehouse_id OR (
            ($HBBMJ=1 AND d.warehouse_id=20005 AND $warehouse_id=139)
            OR ($HBBMJ=1 AND d.warehouse_id=20047 AND $warehouse_id=20001)  
            OR ($HBBMJ=1 AND d.warehouse_id=20048 AND $warehouse_id=140)
            ))

			and ('$batch_sn' = '' OR d.batch_sn = '$batch_sn')
			and ('$ny' = '' OR (pc.name like '%外销%' AND '$ny' = '外销') OR (pc.name not like '%内销%' AND '$ny' = '内销'))
			and ($product_id = 0 OR d.product_id = $product_id)
			and d.warehouse_id in (SELECT uwh.warehouse_id FROM user_warehouse uwh where user_id = $user_id) 
        ) as c, product p, product_unit g, warehouse w
        
        where p.id = c.product_id and g.id = p.unit_id and w.id = c.warehouse_id order by c.invoice_dt, c.RkNum";
        $rows = DB::query()->selectRaw($sql)->get();
        // 插入初期
        $rows->prepend(['bill_name' => '期初', 'qm_num' => $qcnum]);
        return $rows;
    }

    /**
     * 校验单据批次
     * @warehouse_id 仓库编码
     * @product_id 存货编码
     * @batch_sn 批次
	 * @poscode 货位
	 * @delivery_data_id 发货单表体ID 
	 * @sample_data_id 样品单表体ID
	 * @direct_data_id 直营单表体ID 
     */
    public static function verfyInvoiceBatch($warehouse_id, $product_id, $batch_sn, $poscode, $delivery_data_id = 0, $sample_data_id = 0, $direct_data_id = 0)
    {
        $warehouse_id = (int)$warehouse_id;
        $product_id = (int)$product_id;
        $delivery_data_id = (int)$delivery_data_id;
        $sample_data_id = (int)$sample_data_id;
        $direct_data_id = (int)$direct_data_id;
        
        return DB::select("
        select sum(CAST((ROUND(ISNULL(a.num,0) - ISNULL(a.fh_num,0) ,4)) AS decimal(20,4))) as ky_num
		FROM (	
			-- 合并库存、出入库数量
			-- 产成品入库单
			SELECT SUM(ISNULL(num, 0)) num, sum(isnull(fh_num, 0)) as fh_num, product_id, warehouse_id, batch_sn,poscode
			FROM (
				SELECT SUM(ISNULL(d.quantity, 0)) num, 0 as fh_num, m.warehouse_id, d.product_id, d.batch_sn, d.poscode
				FROM stock_record10_data d
				LEFT JOIN stock_record10 m ON m.id = d.record10_id
				WHERE d.product_id IS NOT NULL
				GROUP BY m.warehouse_id,d.product_id,d.batch_sn,d.poscode
				
				UNION ALL
				
				-- 采购入库单
				SELECT SUM(ISNULL(d.quantity, 0)) num, 
				NULL AS fh_num, m.warehouse_id, d.product_id, d.batch_sn, d.poscode
				FROM stock_record01_data d
				LEFT JOIN stock_record01 m ON m.id = d.record01_id
				WHERE d.product_id IS NOT NULL
				GROUP BY m.warehouse_id, d.product_id, d.batch_sn, d.poscode
				
				UNION ALL
				
				-- 其他入库
				SELECT SUM(ISNULL(d.quantity,0)) num, 0 fh_num,warehouse_id,d.product_id,d.batch_sn,poscode
				FROM stock_record08 m
				LEFT JOIN  stock_record08_data d ON m.id = d.record08_id
				WHERE d.product_id IS NOT NULL
				GROUP BY warehouse_id,d.product_id,d.batch_sn,poscode
				
				UNION ALL
				
				-- 其他出库单
				SELECT 0 - SUM(ISNULL(d.quantity,0)) num,0 fh_num,warehouse_id,d.product_id,d.batch_sn,poscode
				FROM stock_record09 m
				LEFT JOIN  stock_record09_data d ON m.id = d.record09_id
				WHERE d.product_id IS NOT NULL and d.id <> $sample_data_id
				GROUP BY warehouse_id,d.product_id,d.batch_sn,poscode
				
				UNION ALL
				
				-- 调拨出库
				SELECT 0 - SUM(ISNULL(quantity,0)) AS num,0 fh_num,out_warehouse_id,product_id,batch_sn,out_poscode
				FROM stock_allocation m
				LEFT JOIN  stock_allocation_data d ON m.id = d.allocation_id
				GROUP BY product_id, out_warehouse_id, batch_sn, out_poscode
				
				UNION ALL
				
				-- 调拨入库
				SELECT SUM(ISNULL(quantity,0)) AS num, 0 fh_num, in_warehouse_id, product_id, batch_sn, in_poscode
				FROM stock_allocation m
				LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
				GROUP BY product_id,in_warehouse_id,batch_sn,in_poscode
				
				UNION ALL
				
				-- 发货数量
				SELECT 0 - SUM(ISNULL(case when ISNULL(m.status, 0) = 1 then d.quantity ELSE 0 end,0)) num,
				SUM(ISNULL(case when ISNULL(m.status, 0) <> 1 then d.quantity ELSE 0 end, 0)) fh_num,
				d.warehouse_id,d.product_id,d.batch_sn,poscode
				FROM stock_delivery_data d,stock_delivery m
				WHERE m.id = d.delivery_id and d.id <> $delivery_data_id
				GROUP BY d.product_id,d.batch_sn,d.warehouse_id,poscode
				
				union all
				
				-- 直营发货数量
				SELECT 0 - SUM(ISNULL(case when ISNULL(m.status, 0) = 1 then d.quantity ELSE 0 end,0)) num,
				SUM(ISNULL(case when ISNULL(m.status, 0) <> 1 then d.quantity ELSE 0 end, 0)) fh_num,
				m.warehouse_id,d.product_id,d.batch_sn,d.poscode
				FROM stock_direct_data d,stock_direct m
				WHERE m.id = d.direct_id and d.id <> $direct_data_id
				GROUP BY d.product_id,d.batch_sn,m.warehouse_id,d.poscode
				
				union all
			
				-- 退货数量
				SELECT 0 - SUM(ISNULL(case when ISNULL(m.status, 0) = 1 then d.quantity ELSE 0 end,0)) num,
				SUM(ISNULL(case when ISNULL(m.status,0) <> 1 then d.quantity ELSE 0 end,0)) fh_num,
				d.warehouse_id,d.product_id,d.batch_sn,poscode
				FROM stock_cancel_data d,stock_cancel m
				WHERE m.id = d.cancel_id AND m.status = 1
				GROUP BY d.product_id,d.batch_sn,d.warehouse_id,poscode
				
			) a1 
			GROUP BY product_id,warehouse_id,batch_sn,poscode
		HAVING SUM(ISNULL(num, 0)) <> 0 OR sum(isnull(fh_num, 0)) <> 0
		) a
		WHERE a.warehouse_id = $warehouse_id
		AND a.product_id = $product_id
		AND (ISNULL('$batch_sn', '') = '' or a.batch_sn = '$batch_sn')
		AND (ISNULL('$poscode', '') = '' or a.poscode = '$poscode')
        ");
    }

    /**
     * 查询库存
     */
    public static function getStockSelectSql()
    {
        return "
        SELECT w.code AS warehouse_code, p.code AS product_code, a.batch_sn AS batch_sn, a.batch_date, 
        a.poscode AS poscode, a.posname AS posname, w.type as warehouse_type, w.name AS warehouse_name, p.name AS product_name, p.product_type, 
        p.spec AS product_spec, u.id AS unit_id, u.name AS product_unit, u.name AS unit_name, p.category_id, p.id AS product_id, w.id AS warehouse_id, 
        round(a.Num, 4) AS num, 
        round(isnull(a.Num, 0) - isnull(a.FhNum, 0) - isnull(a.Cknum, 0) + isnull(a.Rknum, 0), 4) AS ky_num,
        round(isnull(a.FhNum, 0), 4) AS fh_num, 
        round(isnull(a.Cknum, 0), 4) AS ck_num, 
        round(isnull(a.Rknum, 0), 4) AS rk_num, 
        round(isnull(a.Num, 0) - isnull(a.FhNum, 0), 4) AS max_num
        FROM (
            SELECT SUM(isnull(a1.Num, 0)) AS Num, 
            SUM(isnull(a1.FhNum, 0)) AS FhNum, 
            SUM(isnull(a1.Rknum, 0)) AS Rknum, 
            SUM(isnull(a1.Cknum, 0)) AS Cknum, 
            a1.product_id, a1.warehouse_id, a1.batch_sn, a1.batch_date, 
            CASE WHEN a1.poscode = '' THEN NULL ELSE a1.poscode END AS poscode, 
            CASE WHEN a1.posname = '' THEN NULL ELSE a1.posname END AS posname
            FROM (
                -- 采购入库
                SELECT SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                NULL AS FhNum, 
                SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Rknum, 
                0 AS Cknum, m.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_record01 m
                LEFT JOIN stock_record01_data d ON m.id = d.record01_id
                WHERE d.product_id IS NOT NULL
                GROUP BY d.product_id, m.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                
                UNION ALL
                
                -- 原材料出库
                SELECT 
                0 - SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                0 AS FhNum, 0 AS Rknum, SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Cknum, 
                m.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_record11 m
                LEFT JOIN stock_record11_data d ON m.id = d.record11_id
                WHERE d.product_id IS NOT NULL
                GROUP BY d.product_id, m.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                
                UNION ALL
                
                SELECT SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                0 AS FhNum, 
                SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Rknum, 
                0 AS Cknum, m.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_record10 m
                LEFT JOIN stock_record10_data d ON m.id = d.record10_id
                WHERE d.product_id IS NOT NULL
                GROUP BY d.product_id, m.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                
                UNION ALL
                
                SELECT SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                0 AS FhNum, 
                SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Rknum, 
                0 AS Cknum, m.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_record08 m
                LEFT JOIN stock_record08_data d ON m.id = d.record08_id
                WHERE d.product_id IS NOT NULL
                GROUP BY d.product_id, m.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                
                UNION ALL
                
                SELECT 
                0 - SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                0 AS FhNum, 0 AS Rknum, SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Cknum, 
                m.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_record09 m
                LEFT JOIN stock_record09_data d ON m.id = d.record09_id
                WHERE d.product_id IS NOT NULL
                GROUP BY d.product_id, m.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                
                UNION ALL
                
                SELECT 
                0 - SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                0 AS FhNum, 0 AS Rknum, 
                SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Cknum,
                m.out_warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.out_poscode poscode, d.out_posname posname
                FROM stock_allocation m
                LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
                GROUP BY d.product_id, m.out_warehouse_id, d.batch_sn, d.batch_date, d.out_poscode, d.out_posname
                
                UNION ALL
                
                SELECT SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, NULL AS FhNum, 
                SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Rknum, 
                0 AS Cknum, m.in_warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.in_poscode poscode, d.in_posname
                FROM stock_allocation m
                LEFT JOIN stock_allocation_data d ON m.id = d.allocation_id
                GROUP BY d.product_id, m.in_warehouse_id, d.batch_sn, d.batch_date, d.in_poscode, d.in_posname
                
                UNION ALL
                
                SELECT 
                0 - SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num,
                SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS FhNum,
                NULL AS Rknum, NULL AS Cknum, d.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_delivery_data d
                JOIN stock_delivery m ON m.id = d.delivery_id
                GROUP BY d.product_id, d.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                
                UNION ALL
                
                SELECT 
                0 - SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS FhNum, 
                0 AS Rknum, 0 AS Cknum, m.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_direct_data d
                JOIN stock_direct m ON m.id = d.direct_id
                GROUP BY d.product_id, m.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                
                UNION ALL
                
                SELECT 
                0 - SUM(isnull(CASE WHEN m.status = 1 THEN d.quantity ELSE 0 END, 0)) AS Num, 
                0 AS FhNum, 
                0 - SUM(isnull(CASE WHEN m.status <> 1 THEN d.quantity ELSE 0 END, 0)) AS Rknum, 
                NULL AS Cknum, d.warehouse_id, d.product_id, d.batch_sn, d.batch_date, d.poscode, d.posname
                FROM stock_cancel_data d
                JOIN stock_cancel m
                ON m.id = d.cancel_id AND m.status = 1
                GROUP BY d.product_id, d.warehouse_id, d.batch_sn, d.batch_date, d.poscode, d.posname
            ) a1
            
            GROUP BY a1.product_id, a1.warehouse_id, a1.batch_sn, a1.batch_date, 
            CASE WHEN a1.poscode = '' THEN NULL ELSE a1.poscode END, 
            CASE WHEN a1.posname = '' THEN NULL ELSE a1.posname END
            
            HAVING SUM(isnull(a1.Num, 0)) <> 0 OR SUM(isnull(a1.FhNum, 0)) <> 0 OR SUM(isnull(a1.Cknum, 0)) <> 0 OR SUM(isnull(a1.Rknum, 0)) <> 0
        ) a

	    LEFT JOIN product p ON a.product_id = p.id
	    LEFT JOIN product_unit u ON p.unit_id = u.id
	    LEFT JOIN warehouse w ON a.warehouse_id = w.id";
    }
}