<?php namespace Gdoo\Customer\Services;

use DB;

class CustomerService
{
    /**
     * 获取开票单位锁定金额
     * 
     * @tax_id 开票单位id
     */
    public static function getLockMoney($tax_id = 0)
    {
        return DB::select("select sum(money) money from (
        select a.order_id, {$tax_id} as tax_id, isnull(a.money, 0) - isnull(b.money, 0) as money
        from (
            SELECT sd.order_id, SUM(ISNULL(sd.money, 0)) money 
            FROM customer_order_data sd
            LEFT JOIN customer_order sm ON sm.id = sd.order_id 
            where isnull(sd.use_close, 0) = 0 and tax_id = {$tax_id}
            GROUP BY sd.order_id 
            ) as a
            left join ( 
                SELECT d.sale_id, ISNULL(sum(d.money), 0) money
                FROM stock_delivery_data d
                LEFT JOIN stock_delivery m ON d.delivery_id = m.id
                WHERE ISNULL(m.status, 0) = 1
                    and d.sale_id in(select distinct a.id from customer_order a where tax_id = {$tax_id})
                group by d.sale_id
            ) as b on a.order_id = b.sale_id
        where isnull(a.money, 0) - isnull(b.money, 0) > 0
        ) a");
    }

    /**
     * 获取开票单位锁定金额
     * 
     * @tax_id 开票单位id
     */
    public static function getAccList($customer_id, $from_dt, $to_dt)
    {
        $start_dt = '2018-01-01';

        $sql = "SUM(isnull(df, 0)) - SUM(isnull(jf, 0)) - SUM(isnull(bcsyfy, 0)) as qcye
        from (  
        --费用部分
        select 
        sum(case when b.is_cal=0 then d.money else null end) qtfy,--其他费用
        sum(case when b.is_cal=1 and ISNULL(a.adjust_type, 0)=0 and ISNULL(d.use_close,0)=0 then d.money else null end) xzfy,--本期新增费用
        sum(case when b.is_cal=1 and ISNULL(a.adjust_type, 0)=1 then d.money else null end) bcsyfy,
        0 as jf,
        0 as df, 0 as sl
        From customer_cost a --with (nolock)
        left join customer_cost_category b on a.category_id = b.id
        left join customer_cost_data d on a.id = d.cost_id
        left join customer c on c.id = d.customer_id
        left join model_bill t on t.id = d.src_type_id
        where a.category_id <> 1
        and a.status = 1
        and a.date < '$from_dt'
        and a.date >= '$start_dt'
        and c.id = $customer_id

        union all
        
        --发货单(不含费用使用部分)
        select null qtfy,null xzfy,null bcsyfy,sum(isnull(d.money,0)) as jf,0 as df,SUM(d.quantity) sl 
        from stock_delivery m 
        left join stock_delivery_data d on m.id = d.delivery_id
        left join product p on p.id = d.product_id
        left join customer_cost_category ccc on ccc.id = d.fee_category_id
        left join model_bill t on t.id= d.fee_src_type_id
        left join customer c on c.id = m.customer_id
        where m.invoice_dt < '$from_dt'
        and m.invoice_dt >= '$start_dt'
        and left(p.code, 2) <> '99'
        and m.status = 1 and c.id = $customer_id

        union all
            
        select 0 qtfy, 0 xzfy, 0 bcsyfy,sum(isnull(d.money,0)) as jf,0 as df, SUM(d.quantity) sl 
        from stock_direct m 
        left join stock_direct_data d on m.id = d.direct_id 
        left join product p on p.id = d.product_id
        left join customer c on c.id = m.customer_id
        where m.invoice_dt < '$from_dt'
        and m.invoice_dt >= '$start_dt'
        --and isnull(d.CustFreesrcBillCode,'')=''
        --and isnull(d.ApplyProCode,'')=''
        and left(p.code,2) <> '99'
        and m.status = 1 and c.id = $customer_id
            
        union all
            
        --发货单(使用客户费用部分)
        select null qtfy,null xzfy,SUM(d.money) bcsyfy,null as jf,0 as df,null as sl 
        from stock_delivery m 
        left join stock_delivery_data d on m.id=d.delivery_id
        left join customer_cost_category ccc on ccc.id = d.fee_category_id
        left join model_bill t on t.id = d.fee_src_type_id 
        left join customer c on c.id = m.customer_id
        where m.invoice_dt < '$from_dt'
        and m.invoice_dt >= '$start_dt'
        and (isnull(d.fee_src_sn, '') <> '') and isnull(d.money, 0) < 0
        -- and (d.fee_category_id <> 6 or (d.fee_category_id =6 and d.fee_src_type_id =46))
        and m.status = 1 and c.id = $customer_id

        union all

        --发货单(使用促销申请赠品部分)
        select 0 qtfy,0 xzfy, SUM(0-d.money) bcsyfy, 0 as jf,0 as df,0 as sl 
        from stock_delivery m 
        left join stock_delivery_data d on m.id=d.delivery_id
        left join model_bill t on t.id = 17 
        left join promotion p on p.sn = d.promotion_sn
        left join customer c on c.id = m.customer_id
        where m.invoice_dt < '$from_dt'
        and m.invoice_dt >= '$start_dt'
        and isnull(d.promotion_sn,'') <> '' 
        and m.status = 1 and c.id = $customer_id

        union all    
        
        --退货申请单 
        select 0 qtfy,0 xzfy,0 bcsyfy,sum(d.money) as jf,0 as df, SUM(d.quantity) sl 
        from stock_cancel m 
        left join stock_cancel_data d on m.id=d.cancel_id
        left join customer c on c.id = m.customer_id
        where m.invoice_dt < '$from_dt'
        and m.invoice_dt >= '$start_dt'
        and c.id = $customer_id
        ) as a";
        $qcye = DB::query()->selectRaw($sql)->value('qcye');

        // 发生额
        $sql = "2 orderNum, orderD,dDate,cdwcode,cdwname,cdwabbname,cdlcode,dgst,vtype,qtfy,xzfy,bcsyfy,jf,df,sl,0 ye,srcMasterBillType,srcMasterBID, tax_id
        from (
        --费用部分
        select 1 as orderD,
        a.date as dDate,
        c.code as cdwcode,c.name cdwname, c.name as cdwabbname, a.sn as cdlcode,
        case when isnull(d.src_sn,'') = '' then a.remark else concat(t.name, isnull(d.src_sn,'')) end as dgst,
        b.name as vtype,
        case when b.is_cal=0 then d.money else null end qtfy,--其他费用
        case when b.is_cal=1 and ISNULL(a.adjust_type,0)=0 and ISNULL(d.use_close,0)=0 then d.money else null end xzfy,--本期新增费用
        case when b.is_cal=1 and ISNULL(a.adjust_type,0)=1 then d.money else null end bcsyfy,
        0 as jf,
        null as df, null as sl,
        d.src_type_id as srcMasterBillType,
        d.src_id as srcMasterBID,
        null as tax_id
        from customer_cost a
        left join customer_cost_category b on a.category_id = b.id
        left join customer_cost_data d on a.id=d.cost_id 
        left join customer c on c.id = d.customer_id
        left join model_bill t on t.id=d.src_type_id
        where a.category_id <> 1
        and a.status=1
        and a.date >= '$from_dt'
        and a.date >= '$start_dt'
        and a.date <= '$to_dt'
        and c.id = $customer_id
 
        --促销申请部分(物资、赠品)
        union all 
        select 1 as orderD,
        ".sql_year_month_day('a.created_at', 'ts')." as dDate,
        c.code as cdwcode,c.name cdwname,c.name as cdwabbname, a.sn as cdlcode,
        concat('促销申请', case when a.type_id = 1 then '物资' else '赠品' end, a.sn) as dgst,
        '促销费' as vtype,
        0,--其他费用
        undertake_money as xzfy,--本期新增费用
        0 bcsyfy,
        0 as jf,
        0 as df, 0 as sl,
        17 as srcMasterBillType,
        a.id as srcMasterBID, a.tax_id
        from promotion a
        left join customer c on c.id = a.customer_id
        where a.status = 1 and a.type_id < 3 -- AND ISNULL(a.use_close, 0) = 0
        and ".sql_year_month_day('a.created_at', 'ts')." >= '$from_dt'
        and ".sql_year_month_day('a.created_at', 'ts')." >= '$start_dt'
        and ".sql_year_month_day('a.created_at', 'ts')." <= '$to_dt'
        and c.id = $customer_id
 
        union all  
            
        --发货单(不含费用使用部分)
        select 3 as orderD,
        m.invoice_dt as dDate,
        c.code as cdwcode,c.name as cdwname,c.name as cdwabbname,m.sn as cdlcode,
        '发货' as dgst,
        '发货单' as vtype,
        0 qtfy,
        0 xzfy,
        0 bcsyfy,
        sum(isnull(d.money,0)) as jf,0 as df,
        SUM(d.quantity) sl,
        43 as srcMasterBillType, 
        m.id as srcMasterBID,
        m.tax_id
        from stock_delivery m
        left join stock_delivery_data d on m.id=d.delivery_id 
        left join product p on p.id=d.product_id 
        left join customer_cost_category ccc on ccc.id = d.fee_category_id
        left join customer c on c.id=m.customer_id
        left join model_bill t on t.id=d.fee_src_type_id 
        where m.invoice_dt >= '$from_dt' and 
        m.invoice_dt <= '$to_dt' and
        m.invoice_dt >= '$start_dt' and
        left(p.code,2)<>'99' and
        m.status >=1 and
        c.id = $customer_id
        group by m.invoice_dt,c.code,c.name,m.sn,m.id,m.tax_id

        UNION ALL
                
        --发货单(不含费用使用部分-直营)
        select 3 as orderD,
        m.invoice_dt as dDate,
        c.code as cdwcode,c.name as cdwname,c.name as cdwabbname,m.sn as cdlcode,
        '发货' as dgst,
        '直营发货单' as vtype,
        0 qtfy,
        0 xzfy,
        0 bcsyfy,
        sum(isnull(d.money,0)) as jf,0 as df,
        SUM(d.quantity) sl,
        65 as srcMasterBillType,
        m.id as srcMasterBID,
        m.tax_id
        from stock_direct m 
        left join stock_direct_data d on m.id = d.direct_id 
        left join product p on p.id=d.product_id 
        left join customer c on c.id=m.customer_id
        where m.invoice_dt >= '$from_dt' and 
        m.invoice_dt <= '$to_dt' and
        m.invoice_dt >= '$start_dt' and
        left(p.code,2)<>'99' and
        m.status = 1 and
        c.id = $customer_id
        group by m.invoice_dt,c.code,c.name,m.sn,m.id,m.tax_id

        union all  
            
        --发货单(使用客户费用部分)
        select 4 as orderD,
        m.invoice_dt as dDate,
        c.code as cdwcode,c.name as cdwname,c.name as cdwabbname,m.sn as cdlcode,
        concat('发货使用', t.name, isnull(d.fee_src_sn,'')) as dgst,
        '发货单' as vtype,
        0 qtfy,
        0 xzfy,
        SUM(d.money) bcsyfy,
        0 as jf,0 as df,0 as sl,
        d.fee_src_type_id as srcMasterBillType, 
        d.fee_src_id as srcMasterBID,
        m.tax_id
        from stock_delivery m 
        left join stock_delivery_data d on m.id = d.delivery_id
        left join customer_cost_category ccc on ccc.ID = d.fee_category_id
        left join model_bill t on t.id = d.fee_src_type_id
        left join customer c on c.id=m.customer_id
        where m.invoice_dt >= '$from_dt' and 
        m.invoice_dt <= '$to_dt' and
        m.invoice_dt >= '$start_dt' and
        (isnull(d.fee_src_sn,'') <> '') and isnull(d.money, 0) < 0 and
        m.status>=1 and
        c.id = $customer_id
        group by m.invoice_dt,c.code,c.name,m.sn,d.fee_src_type_id,d.fee_src_id,d.fee_src_sn,t.name,c.name,m.tax_id

        union all

        --发货单(使用促销申请赠品部分)
        select 4 as orderD,
        m.invoice_dt as dDate,
        c.code as cdwcode,c.name as cdwname,c.name as cdwabbname,m.sn as cdlcode,
        concat('发货使用', t.name, '赠品', isnull(d.fee_src_sn,'')) as dgst,
        '发货单' as vtype,
        0 qtfy,
        0 xzfy,
        SUM(0 - d.money) bcsyfy,
        0 as jf,0 as df,0 as sl,
        t.id as srcMasterBillType,
        p.id as srcMasterBID,
        m.tax_id
        from stock_delivery m 
        left join stock_delivery_data d on m.id=d.delivery_id 
        left join model_bill t on t.id = 17
        left join promotion p on p.sn=d.promotion_sn
        left join customer c on c.id=m.customer_id
        where m.invoice_dt >= '$from_dt' and 
        m.invoice_dt <= '$to_dt' and
        m.invoice_dt >= '$start_dt' and
        isnull(d.promotion_sn,'')<>'' and 
        m.status>=1 and
        c.id = $customer_id
        group by m.invoice_dt,c.code,c.name,m.sn,t.id,p.id,d.promotion_sn,t.name,d.fee_src_sn,m.tax_id
            
        union all    
        
        --退货申请单 
        select 4 as orderD,
        m.invoice_dt as dDate,
        c.code as cdwcode,c.name as cdwname,c.name as cdwabbname,m.sn as cdlcode,
        '退货' as dgst,
        '退货申请单' as vtype,
        0 qtfy,
        0 xzfy,
        0 bcsyfy,
        sum(d.money) as jf,
        0 as df,
        SUM(d.quantity) sl,
        47 as srcMasterBillType,
        m.id as srcMasterBID,
        m.tax_id
        from stock_cancel m 
        left join stock_cancel_data d on m.id=d.cancel_id
        left join customer c on c.id=m.customer_id
        where m.invoice_dt >= '$from_dt' and 
        m.invoice_dt <= '$to_dt' and
        m.invoice_dt >= '$start_dt' and 
        c.id = $customer_id
        group by m.invoice_dt,c.code,c.name,m.sn,m.id,m.tax_id
        ) as a";
        $items = DB::query()->selectRaw($sql)->get();

        $rows = [[
            'orderNum' => 1,
            'orderD' => '',
            'dDate' => '', // 日期
            'cdwcode' => '', // 客户编码
            'cdwname' => '',  // 客户
            'cdwabbname' => '',
            'cdlcode' => '', // 单据号
            'dgst' => '期初余额', // 摘要
            'srcMasterBillType' => '', // 单据类型ID  
            'srcMasterBID' => '', // 单据ID
            'vtype' => '',  // 单据
            'qtfy' => 0, // 其他费用
            'xzfy' => 0, // 本次新增费用
            'bcsyfy' => 0, // 其中使用费用金额
            'jf' => 0, // 发货总金额
            'sl' => 0, // 发货数量
            'df' => 0, // 收款金额
            'ye' => $qcye, // 余额
            'tax_id' => ''
        ]];

        $ye = $qcye;

        foreach($items as $item) {
            
            $item['qtfy'] = (float)$item['qtfy'];
            $item['xzfy'] = (float)$item['xzfy'];
            $item['bcsyfy'] = (float)$item['bcsyfy'];
            $item['jf'] = (float)$item['jf'];
            $item['df'] = (float)$item['df'];
            $item['sl'] = (float)$item['sl'];

            $ye = $ye + ($item['df'] - $item['jf'] - $item['bcsyfy']);

            $rows[] = [
                'orderNum' => 2,
                'orderD' => $item['orderD'],
                'dDate' => $item['dDate'],
                'cdwcode' => $item['cdwcode'],
                'cdwname' => $item['cdwname'],
                'cdwabbname' => $item['cdwabbname'],
                'cdlcode' => $item['cdlcode'],
                'dgst' => $item['dgst'],
                'srcMasterBillType' => $item['srcMasterBillType'],
                'srcMasterBID' => $item['srcMasterBID'],
                'vtype' => $item['vtype'],
                'qtfy' => $item['qtfy'],
                'xzfy' => $item['xzfy'],
                'bcsyfy' => $item['bcsyfy'],
                'jf' => $item['jf'],
                'sl' => $item['sl'],
                'df' => $item['df'],
                'ye' => $ye,
                'tax_id' => $item['tax_id']
            ];
        }
        return $rows;
    }
}