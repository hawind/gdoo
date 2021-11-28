<?php namespace Gdoo\Order\Controllers;

use DB;
use View;
use Request;

use Gdoo\Index\Services\NotificationService;

use Gdoo\Product\Models\ProductCategory;

use Gdoo\Index\Controllers\DefaultController;

class ReportController extends DefaultController
{
    public $permission = [
        'clientdata'
    ];

    public $promotion = [
        'promotions_category' => [
            1 => '消费促销',
            2 => '渠道促销',
            3 => '经销促销'
        ],
        'promotion_category' => [
            1 => '消费',
            2 => '渠道',
            3 => '经销'
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        // 客户类型
        $customer_type = DB::table('customer_type')->get();
        $customer_type = array_by($customer_type);
        View::share('customer_type', $customer_type);
    }

    // 销售曲线
    public function index()
    {
        // 本年时间
        $now_year = date('Y');

        // 客户权限
        $selects = regionCustomer('customer');

        // 获得GET数据
        $category_id = Request::get('category_id', 0);
        $customer_type = Request::get('customer_type', 0);
        $selects['query']['category_id'] = $category_id;
        $selects['query']['customer_type'] = $customer_type;

        // 获取品类
        $_categorys = ProductCategory::orderBy('lft', 'asc')
        ->where('status', 1)
        ->where('type', 1)
        ->get()->toNested();

        if ($category_id) {
            $category = $_categorys[$category_id];
            $category = DB::table('product_category')
            ->where('lft', '>=', $category['lft'])
            ->where('rgt', '<=', $category['rgt'])
            ->pluck('id');
        }
        // 年度月份曲线图
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->groupBy('m.invoice_dt')
        ->groupBy('product.category_id')
        ->selectRaw('
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            m.invoice_dt
        ');
        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->groupBy('m.invoice_dt')
        ->groupBy('product.category_id')
        ->selectRaw('
            product.category_id, 
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            m.invoice_dt
        ');
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->groupBy('m.invoice_dt')
        ->groupBy('product.category_id')
        ->selectRaw('
            product.category_id, 
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            m.invoice_dt
        ');
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $k => $v) {
                $delivery->whereIn($k, $v);
                $cancel->whereIn($k, $v);
                $direct->whereIn($k, $v);
            }
        }
        if ($category_id) {
            $delivery->whereIn('product.category_id', $category);
            $cancel->whereIn('product.category_id', $category);
            $direct->whereIn('product.category_id', $category);
        }
        $rows = $delivery->unionAll($cancel)->unionAll($direct)->orderBy('invoice_dt', 'asc')->get();

        $years = $categorys = [];
        foreach ($rows as $row) {
            list($year, $month, $day) = explode('-', $row['invoice_dt']);
            if($year) {
                $years[$year][$month]['money'] += floatval($row['money']);
            }
            // 取得产品类别的定级类别编号
            if ($year == $now_year) {
                $category_id = $_categorys[$row['category_id']]['parent'][1];
                if ($category_id) {
                    $categorys[$category_id] += floatval($row['money']);
                }
            }
        }

        unset($rows);

        // bd 预估费用， bm 兑现费用
        $model = DB::table('promotion')
        ->leftJoin('customer', 'customer.id', '=', 'promotion.customer_id')
        ->whereRaw(sql_year('promotion.created_at', 'ts')."=?", [date('Y')])
        ->groupBy('promotion.type_id')
        ->selectRaw('
            promotion.type_id,
            SUM(isnull(promotion.area_money, 0)) as bd,
            SUM(isnull(promotion.undertake_money, 0)) as bm
        ');
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $key => $whereIn) {
                $model->whereIn($key, $whereIn);
            }
        }
        $ps = $model->get();

        $promotion = [];
        if ($ps->count()) {
            foreach ($ps as $key => $value) {
                // 本年读已经兑现的促销金额
                $promotion_honor += $value['bm'];
                //本年促销分类金额
                $promotion['cat'][$value['type_id']] = $value['bd'];
                //本年所有促销金额
                $promotion['all'] += $value['bd'];
            }
        }
        unset($ps);

        $data_all = array_sum($categorys);

        // 本年促销费比(金额)计算
        if ($data_all > 0) {
            $promotions_all = ($promotion['all']/$data_all) * 100;
        }
        $assess = number_format($promotions_all, 2).'%';

        // 多产品年度颜色定义
        $color = array('FF9900','339900','3399FF','FF66CC');

        $json = array();

        for ($i=1; $i <= 12; $i++) {
            $i = sprintf("%02d", $i);
            $json['categories'][] = $i.'月';
        }

        if ($years) {
            $key = 0;
            $json['total'] = [];
            foreach ($years as $year => $months) {
                if ($year > 0) {
                    $j['name'] = $year;
                    $j['type'] = 'line';
                    $j['data'] = [];
                    for ($i=1; $i <= 12; $i++) {
                        $i = sprintf("%02d", $i);
                        $j['data'][] = (int)$months[$i]['money'];
                    }
                    $json['total'][$year] = number_format(array_sum($j['data']), 2);
                }
                $json['series'][] = $j;
            }
        }

        $query = url().'?'.http_build_query($selects['query']);
        return $this->display([
            'product_categorys' => $_categorys,
            'categorys' => $categorys,
            'promotion' => $promotion,
            'promotion_honor' => $promotion_honor,
            'select' => $selects,
            'query' => $query,
            'assess' => $assess,
            'json' => json_encode($json, JSON_UNESCAPED_UNICODE),
        ]);
    }

    // 商品分类
    public function category()
    {
        $customer_type = Request::get('customer_type', 0);

        // 当前年月日
        $start_date = Request::get('date1', date('Y-01-01'));
        $end_date = Request::get('date2', date("Y-m-d"));

        // 减一年时间戳
        $last_start_date = date('Y-m-d', strtotime($start_date.'-1 year'));
        $last_end_date = date('Y-m-d', strtotime($end_date.'-1 year'));

        $start_year = date('Y', strtotime($start_date.'-1 year'));
        $end_year = date('Y', strtotime($end_date));

        // 客户权限
        $selects = regionCustomer('customer');
        $selects['query']['customer_type'] = $customer_type;
        $selects['query']['date1'] = $start_date;
        $selects['query']['date2'] = $end_date;

        // 获取产品类别
        $product_categorys = DB::table('product_category')
        ->where('node.status', 1)
        ->where('node.type', 1)
        ->toTreeChildren();
        
        $one = $two = [];
        foreach ($product_categorys as $category) {
            if ($category['level'] == 2) {
                foreach ($category['children'] as $children) {
                    $one[$children] = $category['id'];
                }
            }
            if ($category['level'] == 3) {
                foreach ($category['children'] as $children) {
                    $two[$children] = $category['id'];
                }
            }
        }
        $percentData = $pieData = array();

        /** 品类累计到今天 **/
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw("m.invoice_dt BETWEEN '$last_start_date' AND '$end_date'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy(DB::raw(sql_month('m.invoice_dt')))
        ->groupBy('product.category_id')
         ->selectRaw('
            product.category_id, 
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' as [year], 
            '.sql_month('m.invoice_dt').' as [month]
        ');

        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw("m.invoice_dt BETWEEN '$last_start_date' AND '$end_date'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy(DB::raw(sql_month('m.invoice_dt')))
        ->groupBy('product.category_id')
         ->selectRaw('
            product.category_id, 
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' as [year], 
            '.sql_month('m.invoice_dt').' as [month]
        ');
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw("m.invoice_dt BETWEEN '$last_start_date' AND '$end_date'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy(DB::raw(sql_month('m.invoice_dt')))
        ->groupBy('product.category_id')
         ->selectRaw('
            product.category_id, 
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' as [year], 
            '.sql_month('m.invoice_dt').' as [month]
        ');
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $k => $v) {
                $delivery->whereIn($k, $v);
                $cancel->whereIn($k, $v);
                $direct->whereIn($k, $v);
            }
        }
        $rows = $delivery->unionAll($cancel)->unionAll($direct)->get();

        foreach ($rows as $row) {
            $category_id = (int)$one[$row['category_id']];
            if ($category_id > 0) {
                $pieData[$row['year']][$category_id] += $row['money'];
                $row['month'] = sprintf("%02d", $row['month']);
                $columnData[$row['year']][$category_id][$row['month']] += $row['money'];
            }
        }

        // 年度月份曲线图
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_month_day('m.invoice_dt')." BETWEEN '".date('m-d', strtotime($start_date))."' AND '".date('m-d', strtotime($end_date))."'")
         ->groupBy(DB::raw(sql_year('m.invoice_dt')))
         ->groupBy('product.category_id')
         ->selectRaw('
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' as [year]
        ');
        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_month_day('m.invoice_dt')." BETWEEN '".date('m-d', strtotime($start_date))."' AND '".date('m-d', strtotime($end_date))."'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
         ->groupBy('product.category_id')
         ->selectRaw('
            product.category_id, 
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' as year
        ');
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_month_day('m.invoice_dt')." BETWEEN '".date('m-d', strtotime($start_date))."' AND '".date('m-d', strtotime($end_date))."'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
         ->groupBy('product.category_id')
         ->selectRaw('
            product.category_id, 
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' as year
        ');
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $k => $v) {
                $delivery->whereIn($k, $v);
                $cancel->whereIn($k, $v);
                $direct->whereIn($k, $v);
            }
        }
        $rows = $cancel->unionAll($delivery)->unionAll($direct)->get();

        foreach ($rows as $row) {
            $category_id = (int)$one[$row['category_id']];
            $_category_id = (int)$two[$row['category_id']];
            $percentData[$row['year']][$category_id] += $row['money'];
            $percentData[$row['year']][$_category_id] += $row['money'];
            
            // 需要单独排除否则计算不准确
            $_percentData[$row['year']][$_category_id] += $row['money'];
        }

        //}
        unset($rows);

        // 去年区域销售额和今年金额占比
        if (is_array($percentData[$end_year])) {
            $percentage = array();
            foreach ($percentData[$end_year] as $key => $value) {
                $per = $value - $percentData[$start_year][$key];
                if ($percentData[$start_year][$key] > 0) {
                    $p = number_format(($per/$percentData[$start_year][$key])*100, 2);
                } else {
                    $p = '0.00';
                }
                $percentage[$key] = $p;
            }
        }

        // 本年同期去年占比
        $now_year_sum = is_array($percentData[$end_year]) ? array_sum((array)$percentData[$end_year]) : 0;
        $now_year_sum = $now_year_sum - array_sum((array)$_percentData[$end_year]);
        
        $last_year_sum = is_array($percentData[$start_year]) ? array_sum((array)$percentData[$start_year]) : 0;
        $last_year_sum = $last_year_sum - array_sum((array)$_percentData[$start_year]);

        if ($now_year_sum > 0) {
            $temp = $now_year_sum - $last_year_sum;
            $total = number_format(($temp/$now_year_sum)*100, 2);
        }
        $percentage['total'] = $total;
        $percentData['sum'][$end_year]  = $now_year_sum;
        $percentData['sum'][$start_year] = $last_year_sum;

        // 饼图数据
        $json = [];
        if ($pieData) {
            foreach ($pieData as $year => $category) {
                $pie = [];
                foreach ($category as $category_id => $v) {
                    $title = $product_categorys[$category_id]['name'];
                    $pie[] = array($title, $v);
                }
                $json['pie'][$year] = $pie;
            }
        }

        if ($json['pie']) {
            asort($json['pie']);
        }

        if ($columnData) {
            asort($columnData);
        }

        if ($columnData) {
            foreach ($columnData as $year => $category) {

                if (empty($json['column']['categories'])) {
                    for ($i=1; $i <= 12; $i++) {
                        $m = sprintf("%02d", $i);
                        $json['column']['categories'][] = $m.'月';
                    }
                }

                foreach ($category as $category_id => $months) {
                    $series = [];
                    $title = $product_categorys[$category_id]['name'];
                    $series['name'] = $title;
                    $series['type'] = 'line';
                    for ($i=1; $i <= 12; $i++) {
                        $m = sprintf("%02d", $i);
                        $series['data'][] = (int)$months[$m];
                    }
                    $json['column']['series'][$year][] = $series;
                }
            }
        }

        $query = url().'?'.http_build_query($selects['query']);

        $startTime = date('Y', strtotime($this->setting['setup_dt']));
        $years = range($startTime, date('Y'));
        $months = range(1, 12);
        $selects['query']['years'] = $years;
        $selects['query']['months'] = $months;

        return $this->display(array(
            'percentage' => $percentage,
            'percentData' => $percentData,
            'product_categorys' => $product_categorys,
            'select' => $selects,
            'now_year' => $end_year,
            'last_year' => $start_year,
            'query' => $query,
            'json' => json_encode($json),
        ));
    }

    // 客户销售排名
    public function ranking()
    {
        // 客户名称
        $customer_name = Request::get('customer_name');
        // 当前年月日
        $now_sdate = Request::get('date1', date("Y").'-01-01');
        $now_date = Request::get('date2', date("Y-m-d"));
        // 当前选中日期的时间戳
        $now_year_time = strtotime($now_sdate);
        // 减一年时间戳
        $last_year_time = strtotime('-1 year', $now_year_time);
        // 当前年
        $now_year = date('Y', $now_year_time);
        // 减一年
        $last_year = $now_year - 1;

        $stime = date('m-d', strtotime($now_sdate));
        $etime = date('m-d', strtotime($now_date));

        // 当前年开始时间
        $now_year_start_time = strtotime($now_year.'-01-01');
        // 减一年开始时间
        $last_year_start_time = strtotime($last_year.'-01-01');

        // 客户权限
        $selects = regionCustomer('customer');
        $selects['query']['customer_name'] = $customer_name;
        $selects['query']['date1'] = $now_sdate;
        $selects['query']['date2'] = $now_date;

        $tag = Request::get('tag', 'customer_id');
        $selects['query']['tag'] = $tag;

        if ($tag == 'city_id') {
            $sql = 'customer.city_id';
        }
        if ($tag == 'customer_id') {
            $sql = 'customer.id';
        }

        // 年度月份曲线图
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->leftJoin('region as r', 'r.id', '=', 'customer.city_id')
        ->leftJoin('region as p2', 'p2.id', '=', 'r.parent_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_month_day('m.invoice_dt')." BETWEEN '$stime' AND '$etime'")
        ->groupBy(
            'product.category_id', 
            'customer.name',
            'customer.code',
            'p2.name',
            'r.name', 
            'm.customer_id',
            'customer.region_id',
            'customer.city_id'
        )
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy($sql)
        ->selectRaw('
            product.category_id,
            customer.name customer_name,
            customer.code customer_code,
            p2.name province_name,
            r.name city_name,
            m.customer_id,
            customer.region_id,
            customer.city_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' [year]
        ');
        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->leftJoin('region as r', 'r.id', '=', 'customer.city_id')
        ->leftJoin('region as p2', 'p2.id', '=', 'r.parent_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_month_day('m.invoice_dt')." BETWEEN '$stime' AND '$etime'")
        ->groupBy(
            'product.category_id',
            'customer.name', 
            'customer.code', 
            'p2.name', 
            'r.name', 
            'm.customer_id',
            'customer.region_id',
            'customer.city_id'
        )
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy($sql)
        ->selectRaw('
            product.category_id,
            customer.name customer_name,
            customer.code customer_code,
            p2.name province_name,
            r.name city_name,
            m.customer_id,
            customer.region_id,
            customer.city_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' [year]
        ');
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'stock_cancel.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->leftJoin('region as r', 'r.id', '=', 'customer.city_id')
        ->leftJoin('region as p2', 'p2.id', '=', 'r.parent_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_month_day('m.invoice_dt')." BETWEEN '$stime' AND '$etime'")
        ->groupBy(
            'product.category_id',
            'customer.name', 
            'customer.code', 
            'p2.name', 
            'r.name', 
            'm.customer_id',
            'customer.region_id',
            'customer.city_id'
        )
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy($sql)
        ->selectRaw('
            product.category_id,
            customer.name customer_name,
            customer.code customer_code,
            p2.name province_name,
            r.name city_name,
            m.customer_id,
            customer.region_id,
            customer.city_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' [year]
        ');
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $k => $v) {
                $delivery->whereIn($k, $v);
                $cancel->whereIn($k, $v);
                $direct->whereIn($k, $v);
            }
        }
        if ($customer_name) {
            $delivery->where('customer.name', 'like', '%'.$customer_name.'%');
            $cancel->where('customer.name', 'like', '%'.$customer_name.'%');
            $direct->where('customer.name', 'like', '%'.$customer_name.'%');
        }
        $rows = $delivery->unionAll($cancel)->orderBy('money', 'desc')->get();

        // 获取品类
        $product_categorys = ProductCategory::orderBy('lft', 'asc')
        ->where('status', 1)
        ->where('type', 1)
        ->get()->toNested();

        $categorys = $single = $info = array();
        foreach ($rows as $row) {
            $single['info'][$row[$tag]] = $row;
            $single[$row['year']][$row[$tag]] += $row['money'];
            $category_id = $product_categorys[$row['category_id']]['parent'][1];
            if ($category_id) {
                $categorys['name'][$category_id] = $product_categorys[$category_id];
                $categorys['money'][$row['year']][$row[$tag]][$category_id] += $row['money'];
            }
        }

        unset($rows);
        $query = url().'?'.http_build_query($selects['query']);
        // 年数组
        $month = date('m', strtotime($now_date));
        $regions = DB::table('customer_region')->get()->keyBy('id');
        $days = (new \Carbon\Carbon(date('Y-01-01')))->diffInDays();

        return $this->display(array(
            'info'              => $info,
            'regions'           => $regions,
            'single'            => $single,
            'categorys'         => $categorys,
            'product_categorys' => $product_categorys,
            'tag'               => $tag,
            'month'             => $month,
            'now_year'          => $now_year,
            'last_year'         => $last_year,
            'select'            => $selects,
            'query'             => $query,
            'days'              => $days,
        ));
    }

    // 客户单品销售
    public function single()
    {
        // 客户权限
        $selects = regionCustomer('customer');

        // 获得GET数据
        $category_id = Request::get('category_id', 226);
        $now_year = Request::get('year', date("Y", time()));
        $selects['query']['category_id'] = $category_id;
        $selects['query']['year'] = $now_year;

        $categorys = ProductCategory::orderBy('lft', 'asc')
        ->where('status', 1)
        ->where('type', 1)
        ->get()->toNested();

        if ($category_id) {
            $_category = $categorys[$category_id];
            $category = DB::table('product_category')
            ->where('lft', '>=', $_category['lft'])
            ->where('rgt', '<=', $_category['rgt'])
            ->pluck('id');
        }
        
        if ($category->count()) {
            // 年度月份曲线图
            $delivery = DB::table('stock_delivery_data as d')
            ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereIn('product.category_id', $category)
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$now_year])
            ->groupBy(DB::raw('
                '.sql_year('m.invoice_dt').',
                '.sql_month('m.invoice_dt').',
                product.category_id,
                d.product_id,
                product.name,
                product.spec
            '))
            ->selectRaw('
                product.name product_name,
                product.spec product_spec,
                d.product_id,
                product.category_id,
                sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
                SUM(d.quantity) amount,
                '.sql_year('m.invoice_dt').' [year],
                '.sql_month('m.invoice_dt').' [month]
            ');
            $cancel = DB::table('stock_cancel_data as d')
            ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereIn('product.category_id', $category)
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$now_year])
            ->groupBy(DB::raw('
                '.sql_year('m.invoice_dt').',
                '.sql_month('m.invoice_dt').',
                product.category_id,
                d.product_id,
                product.name,
                product.spec
            '))
            ->selectRaw('
                product.name product_name,
                product.spec product_spec,
                d.product_id,
                product.category_id,
                sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
                SUM(d.quantity) amount,
                '.sql_year('m.invoice_dt').' [year],
                '.sql_month('m.invoice_dt').' [month]
            ');
            $direct = DB::table('stock_direct_data as d')
            ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereIn('product.category_id', $category)
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$now_year])
            ->groupBy(DB::raw('
                '.sql_year('m.invoice_dt').',
                '.sql_month('m.invoice_dt').',
                product.category_id,
                d.product_id,
                product.name,
                product.spec
            '))
            ->selectRaw('
                product.name product_name,
                product.spec product_spec,
                d.product_id,
                product.category_id,
                sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
                SUM(d.quantity) amount,
                '.sql_year('m.invoice_dt').' [year],
                '.sql_month('m.invoice_dt').' [month]
            ');
            // 客户圈权限
            if ($selects['authorise']) {
                foreach ($selects['whereIn'] as $k => $v) {
                    $delivery->whereIn($k, $v);
                    $cancel->whereIn($k, $v);
                    $direct->whereIn($k, $v);
                }
            }
            $rows = $cancel->unionAll($delivery)->unionAll($direct)->orderBy('month', 'asc')->get();

            $single = array();
            if ($rows->count()) {
                foreach ($rows as $row) {
                    if ($row['year'] > 0 && $row['month'] > 0) {
                        //金额大于0
                        $single['money'][$row['year']][$row['product_id']][$row['month']] += $row['money'];
                        $single['money2'][$row['year']] += $row['money'];
                        $single['amount'][$row['year']][$row['product_id']][$row['month']] += $row['amount'];
                        $single['name'][$row['product_id']] = $row['product_name'];
                        $single['spec'][$row['product_id']] = $row['product_spec'];

                        $single['year'][$row['year']]['money'][$row['product_id']] += $row['money'];
                        $single['year'][$row['year']]['amount'][$row['product_id']] += $row['amount'];
                    }
                }
            }
        }
        
        if (is_array($single['year'])) {
            asort($single['year']);
        }

        $query = url().'?'.http_build_query($selects['query']);

        return $this->display(array(
            'single' => $single,
            'categorys' => $categorys,
            'select' => $selects,
            'query' => $query,
        ));
    }

    // 区域销售品类
    public function city()
    {
        // 当前年月日
        $now_sdate = Request::get('date1', date("Y").'-01-01');
        $now_edate = Request::get('date2', date("Y-m-d"));

        // 减一年年月日
        $last_sdate = date('Y-m-d', strtotime('-1 year', strtotime($now_sdate)));
        $last_edate = date('Y-m-d', strtotime('-1 year', strtotime($now_edate)));

        // 当前年
        $now_year = date('Y', strtotime($now_sdate));
        $last_year = date('Y', strtotime($last_sdate));

        //筛选专用函数
        $selects = regionCustomer('customer');

        $selects['query']['date1'] = $now_sdate;
        $selects['query']['date2'] = $now_edate;

        // 读取产品类别
        $categorys = ProductCategory::orderBy('lft', 'asc')
        ->where('status', 1)
        ->where('type', 1)
        ->get()->toNested();

        // 发货
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->leftJoin('region', 'region.id', '=', 'customer.city_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw("(m.invoice_dt BETWEEN '$last_sdate' AND '$last_edate' or m.invoice_dt BETWEEN '$now_sdate' AND '$now_edate')")
        ->groupBy(DB::raw('
            '.sql_year('m.invoice_dt').',
            customer.city_id,
            customer.region_id,
            m.customer_id,
            customer.name,
            region.name,
            product.category_id
        '))
        ->selectRaw('
            customer.city_id,
            customer.region_id,
            m.customer_id,
            customer.name customer_name,
            region.name city_name,
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' [year]
        ');
        // 退货
        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->leftJoin('region', 'region.id', '=', 'customer.city_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw("(m.invoice_dt BETWEEN '$last_sdate' AND '$last_edate' or m.invoice_dt BETWEEN '$now_sdate' AND '$now_edate')")
        ->groupBy(DB::raw('
            '.sql_year('m.invoice_dt').',
            customer.city_id,
            customer.region_id,
            m.customer_id,
            customer.name,
            region.name,
            product.category_id
        '))
        ->selectRaw('
            customer.city_id,
            customer.region_id,
            m.customer_id,
            customer.name customer_name,
            region.name city_name,
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' [year]
        ');
        // 直营
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->leftJoin('region', 'region.id', '=', 'customer.city_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw("(m.invoice_dt BETWEEN '$last_sdate' AND '$last_edate' or m.invoice_dt BETWEEN '$now_sdate' AND '$now_edate')")
        ->groupBy(DB::raw('
            '.sql_year('m.invoice_dt').',
            customer.city_id,
            customer.region_id,
            m.customer_id,
            customer.name,
            region.name,
            product.category_id
        '))
        ->selectRaw('
            customer.city_id,
            customer.region_id,
            m.customer_id,
            customer.name customer_name,
            region.name city_name,
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            '.sql_year('m.invoice_dt').' [year]
        ');

        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $k => $v) {
                $delivery->whereIn($k, $v);
                $cancel->whereIn($k, $v);
                $direct->whereIn($k, $v);
            }
        }

        $model = $cancel->unionAll($delivery)->unionAll($direct)
        ->orderBy('region_id', 'asc');
        $rows = $model->get();

        $single = $info = [];

        if ($rows->count()) {
            foreach ($rows as $v) {
                if ($v['region_id'] > 0) {
                    $group = $v['region_id'];
                    $year = $v['year'];
                    $category_id = $categorys[$v['category_id']]['parent'][1];
                    if ($category_id) {
                        $single[$year]['money'][$group][$category_id] += $v['money'];
                        $single[$year]['cat'][$category_id] += $v['money'];
                        $single[$year]['totalcost'][$group] += $v['money'];
                    }
                }
            }
        }
        unset($rows);

        $now_year_single = $single[$now_year];
        $old_year_single = $single[$last_year];

        //去年区域销售额和今年金额占比
        if (is_array($now_year_single['totalcost'])) {
            $percentage = array();
            foreach ($now_year_single['totalcost'] as $key => $value) {
                if ($value) {
                    $per = $value - $old_year_single['totalcost'][$key];

                    if ($old_year_single['totalcost'][$key] > 0) {
                        $per = $per/$old_year_single['totalcost'][$key];
                    }
                    $per = number_format($per*100, 2);
                    $percentage[$key] = $per;
                } else {
                    $percentage[$key] = '0.00';
                }
            }
        }

        // 去年同期和今年算占比
        $oldscale = [];

        if ($categorys) {
            foreach ($categorys as $cat) {
                $categoryCode = $cat['id'];
                // 循环去年的区域品类金额
                if (is_array($old_year_single['money'])) {
                    foreach ($old_year_single['money'] as $key => $value) {

                        // 客户代码$key
                        $a = $now_year_single['money'][$key][$categoryCode] - $value[$categoryCode];

                        if ($value[$categoryCode]) {
                            $a = ($a/$value[$categoryCode]);
                        } else {
                            $a = 0;
                        }
                        $oldscale[$key][$categoryCode] = $a;
                    }
                }
            }
        }

        // 促销计算
        $model = DB::table('promotion')
        ->leftJoin('customer', 'customer.id', '=', 'promotion.customer_id')
        ->leftJoin('region', 'region.id', '=', 'customer.city_id')
        ->whereRaw(sql_year('promotion.created_at', 'ts')."=?", [date('Y')])
        ->groupBy('customer.region_id')
        ->groupBy('promotion.customer_id')
        ->groupBy('promotion.type_id')
        ->selectRaw('
            customer.region_id, 
            promotion.customer_id,
            SUM(promotion.undertake_money) bd_sum, 
            promotion.type_id
        ');
        
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $key => $whereIn) {
                $model->whereIn($key, $whereIn);
            }
        }

        $_promotions = $model->get();

        $promotions = [];

        if ($_promotions->count()) {
            foreach ($_promotions as $v) {
                if ($v['region_id'] > 0) {
                    // 促销分类金额
                    $group = $v['region_id'];
                    $promotions[$group][$v['type_id']] += $v['bd_sum'];
                    $promotions['all'][$group] += $v['bd_sum'];
                }
            }
        }
        unset($_promotions);

        $query = url().'?'.http_build_query($selects['query']);

        $region_id = $selects['whereIn']['customer.region_id'];
        $regions = DB::table('customer_region')->whereIn('id', (array)$region_id)->pluck('name', 'id');

        return $this->display(array(
            'percentage' => $percentage,
            'oldscale' => $oldscale,
            'info' => $info,
            'old_year_single' => $old_year_single,
            'now_year_single' => $now_year_single,
            'categorys' => $categorys,
            'last_year' => $last_year,
            'now_year' => $now_year,
            'select' => $selects,
            'query' => $query,
            'promotions' => $promotions,
            'regions' => $regions,
        ));
    }

    // 单品客户销售
    public function client()
    {
        $now_year = Request::get('year', date('Y'));
        // 获得前一年的年份
        $last_year = $now_year - 1;

        // 筛选专用函数
        $selects = regionCustomer('customer');

        // 筛选专用函数
        $selects['query']['year'] = $now_year;

        // 获得GET数据
        $category_id = Request::get('category_id', 0);
        $selects['query']['category_id'] = $category_id;
        
        // 获取品类
        $categorys = ProductCategory::orderBy('lft', 'asc')
        ->where('status', 1)
        ->where('type', 1)
        ->get()->toNested();

        if ($category_id) {
            $category = $categorys[$category_id];
            $category = DB::table('product_category')
            ->where('lft', '>=', $category['lft'])
            ->where('rgt', '<=', $category['rgt'])
            ->pluck('id');
        }
        // 发货
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('product_category', 'product_category.id', '=', 'product.category_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_year('m.invoice_dt').'=?', [$now_year])
        ->groupBy(DB::raw('
            customer.name,
            product.name,
            product.spec,
            product.category_id,
            '.sql_year('m.invoice_dt').',
            '.sql_month('m.invoice_dt').',
            m.customer_id,
            d.product_id,
            product_category.code,
            product.code
        '))
        ->selectRaw('
            m.customer_id,
            product_category.code category_code,
            product.code product_code,
            customer.name customer_name,
            product.name product_name,
            product.spec product_spec,
            d.product_id,
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            SUM(d.quantity) quantity,
            '.sql_year('m.invoice_dt').' [year],
            '.sql_month('m.invoice_dt').' [month]
        ');
        // 退货
        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('product_category', 'product_category.id', '=', 'product.category_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_year('m.invoice_dt').'=?', [$now_year])
        ->groupBy(DB::raw('
            customer.name,
            product.name,
            product.spec,
            product.category_id,
            '.sql_year('m.invoice_dt').',
            '.sql_month('m.invoice_dt').',
            m.customer_id,
            d.product_id,
            product_category.code,
            product.code
        '))
        ->selectRaw('
            m.customer_id,
            product_category.code category_code,
            product.code product_code,
            customer.name customer_name,
            product.name product_name,
            product.spec product_spec,
            d.product_id,
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            SUM(d.quantity) quantity,
            '.sql_year('m.invoice_dt').' [year],
            '.sql_month('m.invoice_dt').' [month]
        ');
        // 直营
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('product_category', 'product_category.id', '=', 'product.category_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_year('m.invoice_dt').'=?', [$now_year])
        ->groupBy(DB::raw('
            customer.name,
            product.name,
            product.spec,
            product.category_id,
            '.sql_year('m.invoice_dt').',
            '.sql_month('m.invoice_dt').',
            m.customer_id,
            d.product_id,
            product_category.code,
            product.code
        '))
        ->selectRaw('
            m.customer_id,
            customer.name customer_name,
            product_category.code category_code,
            product.code product_code,
            product.name product_name,
            product.spec product_spec,
            d.product_id,
            product.category_id,
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
            SUM(d.quantity) quantity,
            '.sql_year('m.invoice_dt').' [year],
            '.sql_month('m.invoice_dt').' [month]
        ');
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $k => $v) {
                $direct->whereIn($k, $v);
                $cancel->whereIn($k, $v);
                $delivery->whereIn($k, $v);
            }
        }
        if ($category_id) {
            $direct->whereIn('product.category_id', $category);
            $delivery->whereIn('product.category_id', $category);
            $cancel->whereIn('product.category_id', $category);
        }
        $rows = $cancel->unionAll($delivery)->unionAll($direct)
        ->orderBy('category_code', 'ASC')
        ->get();
        
        $single = [];
        foreach ($rows as $v) {
            if ($v['product_id'] > 0) {
                $month = $v['month'];
                $product_id = $v['product_id'];

                $single['product'][$product_id] = $v;
                $single['category'][$product_id] = $v['category_id'];
                $single['customer'][$v['customer_id']] = $v['customer_id'];
                $single['all'][$product_id][$v['customer_id']] = $v['customer_id'];
                $single['sum'][$product_id][$month][$v['customer_id']] = $v['customer_id'];
                $single['sum_money'][$product_id][$month] += $v['money'];
            }
        }

        unset($rows);
        $query = url().'?'.http_build_query($selects['query']);

        $startTime = date('Y', strtotime($this->setting['setup_dt']));
        $years = range(date('Y'), $startTime);
        $months = range(1, 12);

        return $this->display(array(
            'single' => $single,
            'year' => $now_year,
            'years' => $years,
            'months' => $months,
            'select' => $selects,
            'query' => $query,
            'categorys' => $categorys,
        ));
    }

    // 单品客户销售详情
    public function clientdata()
    {
        $year = Request::get('year');
        $product_id = Request::get('product_id');

        // 筛选专用函数
        $selects = regionCustomer('customer');

        $n = date("n", time());

        if ($product_id > 0) {
            // 发货
            $delivery = DB::table('stock_delivery_data as d')
            ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('product_category', 'product_category.id', '=', 'product.category_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw('year(m.invoice_dt)=?', [$year])
            ->groupBy(DB::raw('
                customer.name,
                product.name,
                product.spec,
                product.category_id,
                year(m.invoice_dt),
                month(m.invoice_dt),
                m.customer_id,
                d.product_id,
                product_category.code,
                product.code,
                customer.region_id
            '))
            ->selectRaw('
                product_category.code category_code,
                product.code,
                m.customer_id,
                customer.name customer_name,
                product.name product_name,
                product.spec product_spec,
                d.product_id,
                product.category_id,
                sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
                SUM(d.quantity) quantity,
                year(m.invoice_dt) [year],
                month(m.invoice_dt) [month],
                customer.region_id
            ');
            // 退货
            $cancel = DB::table('stock_cancel_data as d')
            ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('product_category', 'product_category.id', '=', 'product.category_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw('year(m.invoice_dt)=?', [$year])
            ->groupBy(DB::raw('
                customer.name,
                product.name,
                product.spec,
                product.category_id,
                year(m.invoice_dt),
                month(m.invoice_dt),
                m.customer_id,
                d.product_id,
                product_category.code,
                product.code,
                customer.region_id
            '))
            ->selectRaw('
                product_category.code category_code,
                product.code,
                m.customer_id,
                customer.name customer_name,
                product.name product_name,
                product.spec product_spec,
                d.product_id,
                product.category_id,
                sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
                SUM(d.quantity) quantity,
                year(m.invoice_dt) [year],
                month(m.invoice_dt) [month],
                customer.region_id
            ');
            // 直营
            $direct = DB::table('stock_direct_data as d')
            ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('product_category', 'product_category.id', '=', 'product.category_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw('year(m.invoice_dt)=?', [$year])
            ->groupBy(DB::raw('
                customer.name,
                product.name,
                product.spec,
                product.category_id,
                year(m.invoice_dt),
                month(m.invoice_dt),
                m.customer_id,
                d.product_id,
                product_category.code,
                product.code,
                customer.region_id
            '))
            ->selectRaw('
                product_category.code category_code,
                product.code,
                m.customer_id,
                customer.name customer_name,
                product.name product_name,
                product.spec product_spec,
                d.product_id,
                product.category_id,
                sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money,
                SUM(d.quantity) quantity,
                year(m.invoice_dt) [year],
                month(m.invoice_dt) [month],
                customer.region_id
            ');
            // 客户圈权限
            if ($selects['authorise']) {
                foreach ($selects['whereIn'] as $k => $v) {
                    $delivery->whereIn($k, $v);
                    $direct->whereIn($k, $v);
                    $cancel->whereIn($k, $v);
                }
            }

            $delivery->where('product.id', $product_id);
            $cancel->where('product.id', $product_id);
            $direct->where('product.id', $product_id);
            
            $rows = $cancel->unionAll($delivery)->unionAll($direct)
            ->orderBy('category_code', 'ASC')
            ->get();

            $regions = DB::table('customer_region')->get()->pluck('name', 'id');
            $customers = [];

            $single = [];
            foreach ($rows as $row) {
                //如何当前月存在数据
                $customer_id = $row['customer_id'];
                //客户编号公司名称
                $customers[$customer_id] = [
                    'customer_id' => $row['customer_name'],
                    'region_name' => $regions[$row['region_id']],
                ];

                if ($row['money'] > 0) {
                    $single['all'][$customer_id] += $row['money'];
                    $single['product_code'] = $row['product_code'];
                    $single['product_name'] = $row['product_name'];
                    $single['product_spec'] = $row['product_spec'];
                }
                if ($row['month'] == $n) {
                    //筛选本月没有数量的客户
                    $notpurchase[$customer_id] = $row;
                }
            }
        }

        arsort($single['all']);

        return $this->display([
            'single' => $single,
            'year' => $year,
            'month' => $n,
            'customers' => $customers,
            'notpurchase'=> $notpurchase,
        ]);
    }

    /**
     * 新客户
     * 计算本年度有订单去年无订单为新客户
     */
    public function newclient()
    {
        // 筛选专用函数
        $selects = regionCustomer('customer');
  
        $lastYear = date("Y", strtotime("-1 year"));
        $nowYear = date("Y");
        
        // 发货数据
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_year('m.invoice_dt')." in ('$lastYear', '$nowYear')")
        ->groupBy(DB::raw('
            '.sql_year('m.invoice_dt').',
            customer.name,
            customer.code,
            customer.grade_id,
            customer.city_id,
            customer.province_id,
            customer.region_id,
            m.customer_id
        '))
        ->selectRaw('
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money_sum,
            '.sql_year('m.invoice_dt').' AS [year],
            customer.name customer_name,
            customer.code customer_code,
            customer.grade_id,
            customer.city_id,
            customer.province_id,
            customer.region_id,
            m.customer_id
        ');
        
         // 退货数据
         $cancel = DB::table('stock_cancel_data as d')
         ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
         ->leftJoin('product', 'product.id', '=', 'd.product_id')
         ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
         ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
         ->whereRaw(sql_year('m.invoice_dt')." in ('$lastYear', '$nowYear')")
         ->groupBy(DB::raw('
            '.sql_year('m.invoice_dt').',
            customer.name,
            customer.code,
            customer.grade_id,
            customer.city_id,
            customer.province_id,
            customer.region_id,
            m.customer_id
        '))
        ->selectRaw('
            sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money_sum,
            '.sql_year('m.invoice_dt').' AS [year],
            customer.name customer_name,
            customer.code customer_code,
            customer.grade_id,
            customer.city_id,
            customer.province_id,
            customer.region_id,
            m.customer_id
        ');
        // 直营数据
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereRaw(sql_year('m.invoice_dt')." in ('$lastYear', '$nowYear')")
        ->groupBy(DB::raw('
            '.sql_year('m.invoice_dt').',
            customer.name,
            customer.code,
            customer.grade_id,
            customer.city_id,
            customer.province_id,
            customer.region_id,
            m.customer_id
        '))
       ->selectRaw('
           sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money_sum,
           '.sql_year('m.invoice_dt').' AS [year],
           customer.name customer_name,
           customer.code customer_code,
           customer.grade_id,
           customer.city_id,
           customer.province_id,
           customer.region_id,
           m.customer_id
       ');
         // 客户圈权限
         if ($selects['whereIn']) {
             foreach ($selects['whereIn'] as $k => $v) {
                $delivery->whereIn($k, $v);
                $cancel->whereIn($k, $v);
                $direct->whereIn($k, $v);
             }
         }
        $rows = $delivery->unionAll($direct)->unionAll($cancel)->orderBy('money_sum', 'desc')->get();

        $list = [];
        $customers = [];
        foreach ($rows as $k => $v) {
            $list[$v['year']][$v['customer_id']] += $v['money_sum'];
            $customers[$v['customer_id']] = $v;
        }
        unset($rows);

        $query = url().'?'.http_build_query($selects['query']);

        return $this->display(array(
            'select' => $selects,
            'customers' => $customers,
            'list' => $list,
            'lastYear' => $lastYear,
            'nowYear' => $nowYear,
            'query' => $query,
        ));
    }

    /**
     * 连续3个月未发货的客户
     */
    public function stockmonth()
    {
        // 筛选专用函数
        $selects = regionCustomer('customer');

        // 去年
        $year1 = date('Y', strtotime('-1 year'));
        // 今年
        $year2 = date('Y');

        // 退后三个月
        $start_at = date('Y-m-d', strtotime("-3 month"));
        // 今天时间戳
        $end_at = date('Y-m-d');

        $model = DB::table('stock_delivery')
        ->leftJoin('customer', 'customer.id', '=', 'stock_delivery.customer_id')
        ->whereRaw("stock_delivery.invoice_dt BETWEEN '$start_at' AND '$end_at'")
        ->groupBy('customer.id', 'customer.name');
        // 客户圈权限
        if ($selects['whereIn']) {
            foreach ($selects['whereIn'] as $key => $whereIn) {
                if ($whereIn) {
                    $model->whereIn($key, $whereIn);
                }
            }
        }
        $rows = $model->get(['customer.id', 'customer.name']);
        $rows = array_by($rows);

        $model = DB::table('customer')->where('status', 1);
        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $key => $whereIn) {
                $model->whereIn($key, $whereIn);
            }
        }
        $customers = $model->get(['customer.id', 'customer.code', 'customer.name', 'customer.grade_id']);
        $customers = array_by($customers);
        foreach ($rows as $key => $row) {
            unset($customers[$key]);
        }

        $customer_ids = [];
        foreach ($customers as $customer) {
            $customer_ids[] = $customer['id'];
        }

        // 发货数据
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereIn('customer.id', $customer_ids)
        ->whereRaw(sql_year('m.invoice_dt')." BETWEEN '$year1' AND '$year2'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy('m.customer_id')
        ->selectRaw('
            m.customer_id, sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money, 
            '.sql_year('m.invoice_dt').' as [year]
        ');
        // 退货数据
        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereIn('customer.id', $customer_ids)
        ->whereRaw(sql_year('m.invoice_dt')." BETWEEN '$year1' AND '$year2'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy('m.customer_id')
        ->selectRaw('
            m.customer_id, sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money, 
            '.sql_year('m.invoice_dt').' as [year]
        ');
        // 直营数据
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
        ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
        ->whereIn('customer.id', $customer_ids)
        ->whereRaw(sql_year('m.invoice_dt')." BETWEEN '$year1' AND '$year2'")
        ->groupBy(DB::raw(sql_year('m.invoice_dt')))
        ->groupBy('m.customer_id')
        ->selectRaw('
            m.customer_id, sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money, 
            '.sql_year('m.invoice_dt').' as [year]
        ');

        // 客户圈权限
        if ($selects['authorise']) {
            foreach ($selects['whereIn'] as $k => $v) {
                $cancel->whereIn($k, $v);
                $delivery->whereIn($k, $v);
                $direct->whereIn($k, $v);
            }
        }
        $rows = $cancel->unionAll($delivery)->unionAll($direct)->get();
        
        $data = [];
        foreach ($rows as $row) {
            $data[$row['year']][$row['customer_id']] += $row['money'];
        }

        $query = url().'?'.http_build_query($selects['query']);
        return $this->display(array(
            'year1'  => $year1,
            'year2'  => $year2,
            'data'   => $data,
            'rows'   => $customers,
            'select' => $selects,
            'query'  => $query,
        ));
    }
}
