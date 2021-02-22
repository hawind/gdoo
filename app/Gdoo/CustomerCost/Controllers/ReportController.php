<?php namespace Gdoo\CustomerCost\Controllers;

use DB;
use View;
use Request;

use Gdoo\Customer\Models\CustomerType;
use Gdoo\Index\Controllers\DefaultController;

class ReportController extends DefaultController
{
    /**
     * 费用使用统计
     */
    public function saleOrderDetailAction()
    {
        $sdate = date('Y-m-01');
        $edate = date('Y-m-d');
        $search = search_form([],  
            [['form_type' => 'date2', 'name' => '日期', 'field' => 'date', 'value' => [$sdate, $edate], 'options' => []],
            ['form_type' => 'text', 'name' => '订单编号', 'field' => 'm.sn', 'options' => []],
            ['form_type' => 'text', 'name' => '单据编号', 'field' => 'd.fee_src_sn', 'options' => []],
            [
                'form_type' =>'dialog', 
                'field' => 'm.customer_id',
                'name' => '所属客户', 
                'options' => ['url' => 'customer/customer/dialog', 'query' => []]
            ]/*,[
                'form_type' =>'dialog', 
                'field' => 'region_id',
                'name' => '销售团队', 
                'options' => ['url' => 'customer/region/dialog', 'query' => ['layer' => 3]]
            ],*/
        ], 'model');

        if (Request::method() == 'POST') {
            $fields = [];
            foreach($search['forms']['field'] as $i => $field) {
                $fields[$field] = $search['forms']['search'][$i];
            }

            $model = DB::table('customer_order_data as d')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('customer_order as m', 'm.id', '=', 'd.order_id')
            ->leftJoin('customer as c', 'c.id', '=', 'm.customer_id')
            ->leftJoin('customer_cost_category as ccc', 'ccc.id', '=', 'd.fee_category_id')
            ->selectRaw("
                m.sn,
                d.fee_src_sn,
                d.fee_src_type_id,
                Sum(d.money) as money,
                c.code as customer_code,
                c.name as customer_name,
                ccc.name as category_name,
                ".sql_year_month('l.audit_date', 'ts')." as [ym]
            ")
            ->whereRaw('d.fee_src_sn is not null')
            ->groupBy(DB::raw('c.code,c.name,ccc.name,m.sn,d.fee_src_sn,d.fee_src_type_id,'.sql_year_month('l.audit_date', 'ts')));

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    if ($where['field'] == 'date') {
                        continue;
                    }
                    $model->search($where);
                }
            }

            // 销售会计审核日期
            $rows = $model->leftJoin(DB::raw("(select max(d.updated_at) as audit_date, m.data_id
                FROM model_run_log as d left join model_run as m on d.run_id = m.id where m.bill_id = 23 and d.run_name = '销售会计'
                GROUP BY m.data_id
            ) as l
            "), 'l.data_id', '=', 'm.id')
            ->whereRaw("(".sql_year_month('l.audit_date', 'ts')." BETWEEN ? AND ?)", $fields['date'])
            ->orderBy(DB::raw(sql_year_month('l.audit_date', 'ts')), 'asc')
            ->get()->toArray();

            foreach($rows as &$row) {
                $money = abs($row['money']);
                $row['money'] = $money;
            }
            
            return $this->json($rows, true);
        }

        $header = [
            'table' => 'customer_cost',
            'master_table' => 'customer_cost',
            'buttons' => [],
            'search_form' => $search,
            'simple_search_form' => 0,
        ];

        $header['left_buttons'] = [
            ['name' => '导出', 'color' => 'default', 'icon' => 'fa-mail-forward', 'action' => 'export', 'display' => 1],
        ];

        return $this->display([
            'search' => $search,
            'header' => $header,
        ]);
    }
}
