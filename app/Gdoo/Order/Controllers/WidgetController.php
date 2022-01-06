<?php namespace Gdoo\Order\Controllers;

use DB;
use Request;
use Auth;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\InfoService;

class WidgetController extends DefaultController
{
    public $permission = ['index', 'goods', 'orderCount'];

    public function index()
    {
        if (Request::method() == 'POST') {

            $region = regionCustomer();

            $ym = date('Y-m');
            $ymd = date('Y-m-d');

            // 本日收到 []个客户[]张订单，[]件货。
            $model = DB::table('customer_order')
            ->leftJoin('customer_order_data', 'customer_order_data.order_id', '=', 'customer_order.id')
            ->leftJoin('product', 'product.id', '=', 'customer_order_data.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'customer_order.customer_id')
            ->whereRaw('product.id <> 20226 and isnull(product.product_type, 0) = 1');

            $model->whereRaw(sql_year_month_day('customer_order.created_at', 'ts').'=?', [$ymd]);
            
            $model->selectRaw('
                COUNT(DISTINCT customer_order.id) AS count,
                COUNT(DISTINCT customer_order.customer_id) AS customer_count,
                SUM(customer_order_data.delivery_quantity) AS quantity,
                sum(isnull(customer_order_data.money, 0) - isnull(customer_order_data.other_money, 0)) money
            ');
            
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $res = $model->first();
            $rows[] = ['id' => 2, 'title' => '本日收到 <span class="red">'.number_format($res['customer_count']).'</span> 个客户 <span class="red">'.number_format($res['count']).'</span> 张订单，<span class="red">'.number_format($res['quantity']).'</span> 件，<span class="red">'.number_format($res['money']).'</span> 元'];

            // 本月收到 []个客户[]张订单，[]件货。
            $model = DB::table('customer_order')
            ->leftJoin('customer_order_data', 'customer_order_data.order_id', '=', 'customer_order.id')
            ->leftJoin('product', 'product.id', '=', 'customer_order_data.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'customer_order.customer_id');
            
            $model->whereRaw('product.id <> 20226 and isnull(product.product_type, 0) = 1');
            
            $model->whereRaw(sql_year_month('customer_order.created_at', 'ts').'=?', [$ym]);
 
            $model->selectRaw('
                COUNT(DISTINCT customer_order.id) AS count, 
                COUNT(DISTINCT customer_order.customer_id) AS customer_count, 
                SUM(customer_order_data.delivery_quantity) AS quantity,
                sum(isnull(customer_order_data.money, 0) - isnull(customer_order_data.other_money, 0)) money
            ');
            
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }
            $res = $model->first();
            $rows[] = ['id' => 2, 'title' => '本月收到 <span class="red">'.number_format($res['customer_count']).'</span> 个客户 <span class="red">'.number_format($res['count']).'</span> 张订单，<span class="red">'.number_format($res['quantity']).'</span> 件，<span class="red">'.number_format($res['money']).'</span> 元'];
            
            // 本月收到的订单中已发出[]张订单，[]件货
            $model = DB::table('customer_order')
            ->leftJoin('customer_order_data', 'customer_order_data.order_id', '=', 'customer_order.id')
            ->leftJoin('product', 'product.id', '=', 'customer_order_data.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'customer_order.customer_id');
            
            $model->whereRaw('product.id <> 20226 and isnull(product.product_type, 0) = 1');
            $model->whereRaw(sql_year_month('customer_order.created_at', 'ts').'=?', [$ym]);
            
            $model->leftJoin(DB::raw("(
                select 
                    SUM(ISNULL(d.quantity, 0)) yf_num,
                    sum(isnull(d.money, 0) - isnull(d.other_money, 0)) yf_money,
                    d.sale_data_id,
                    d.sale_id
                FROM stock_delivery_data as d
                left join stock_delivery as m on m.id = d.delivery_id
                GROUP BY d.sale_id, d.sale_data_id
                ) sdd
            "), 'customer_order_data.id', '=', 'sdd.sale_data_id');
            $model->where('sdd.yf_num', '>', 0);

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }
            $model->selectRaw('
                COUNT(DISTINCT customer_order.id) AS count, 
                COUNT(DISTINCT sdd.sale_id) AS count, 
                SUM(sdd.yf_num) AS quantity, 
                sum(sdd.yf_money) as money
            ');
            $res = $model->first();
            $rows[] = ['title' => '本月收到的订单中已发出 <span class="red">'.number_format($res['count']).'</span> 张订单，<span class="red">'.number_format($res['quantity']).'</span> 件，<span class="red">'.number_format($res['money']).'</span> 元'];

            // 上月订单本月发出[]张，[]件货。
            $model = DB::table('customer_order')
            ->leftJoin('customer_order_data', 'customer_order_data.order_id', '=', 'customer_order.id')
            ->leftJoin('product', 'product.id', '=', 'customer_order_data.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'customer_order.customer_id')
            ->whereRaw('isnull(product.product_type, 0) = 1');

            $model->whereRaw(sql_year_month('customer_order.created_at', 'ts').'=?', [date("Y-m", strtotime("-1 month"))]);
            
            $model->leftJoin(DB::raw("(
                select 
                    SUM(ISNULL(d.quantity, 0)) yf_num,
                    sum(isnull(d.money, 0) - isnull(d.other_money, 0)) yf_money,
                    d.sale_data_id,
                    d.sale_id
                FROM stock_delivery_data as d
                left join stock_delivery as m on m.id = d.delivery_id
                where ".sql_year_month('m.invoice_dt')." = '$ym'
                GROUP BY d.sale_id, d.sale_data_id
                ) sdd
            "), 'customer_order_data.id', '=', 'sdd.sale_data_id');
            $model->where('sdd.yf_num', '>', 0)
            ->selectRaw('COUNT(DISTINCT sdd.sale_id) AS count, SUM(sdd.yf_num) AS quantity, sum(sdd.yf_money) as money');

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }
            $res = $model->first();
            $rows[] = ['title' => '上月订单本月发出 <span class="red">'.number_format($res['count']).'</span> 张订单，<span class="red">'.number_format($res['quantity']).' </span>件，<span class="red">'.number_format($res['money']).'</span> 元'];

            // 本月共发出件和金额
            $delivery = DB::table('stock_delivery')
            ->leftJoin('stock_delivery_data', 'stock_delivery_data.delivery_id', '=', 'stock_delivery.id')
            ->leftJoin('product', 'product.id', '=', 'stock_delivery_data.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'stock_delivery.customer_id');
            $delivery->whereRaw('stock_delivery_data.product_id <> 20226 and isnull(product.product_type, 0) = 1');
            $delivery->whereRaw(sql_year_month('stock_delivery.invoice_dt').'=?', [$ym]);
            $delivery->groupBy('product.category_id', 'stock_delivery.invoice_dt')
            ->selectRaw('
                stock_delivery.invoice_dt,
                product.category_id,
                COUNT(DISTINCT stock_delivery.id) AS count, 
                SUM(stock_delivery_data.quantity) AS quantity, 
                sum(isnull(stock_delivery_data.money, 0) - isnull(stock_delivery_data.other_money, 0)) money
            ');
            // 本月退货件和金额
            $cancel = DB::table('stock_cancel')
            ->leftJoin('stock_cancel_data', 'stock_cancel_data.cancel_id', '=', 'stock_cancel.id')
            ->leftJoin('product', 'product.id', '=', 'stock_cancel_data.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'stock_cancel.customer_id');
            $cancel->whereRaw('stock_cancel_data.product_id <> 20226 and isnull(product.product_type, 0) = 1');
            $cancel->whereRaw(sql_year_month('stock_cancel.invoice_dt').'=?', [$ym]);
            $cancel->groupBy('product.category_id', 'stock_cancel.invoice_dt')
            ->selectRaw('
                stock_cancel.invoice_dt,
                product.category_id,
                COUNT(DISTINCT stock_cancel.id) AS count, 
                SUM(stock_cancel_data.quantity) AS quantity,
                sum(isnull(stock_cancel_data.money, 0) - isnull(stock_cancel_data.other_money, 0)) money
            ');
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $delivery->whereIn($key, $where);
                    $cancel->whereIn($key, $where);
                }
            }
            $res = $delivery->unionAll($cancel)->get();
            $rows[] = ['title' => '本月共发出 <span class="red">'.number_format($res->sum('count')).'</span> 张发货单，<span class="red">'.number_format($res->sum('quantity')).' </span>件，<span class="red">'.number_format($res->sum('money')).'</span> 元'];
            
            // 本月直营共发出件和金额
            $direct = DB::table('stock_direct')
            ->leftJoin('stock_direct_data', 'stock_direct_data.direct_id', '=', 'stock_direct.id')
            ->leftJoin('product', 'product.id', '=', 'stock_direct_data.product_id')
            ->leftJoin('customer', 'customer.id', '=', 'stock_direct.customer_id');
            $direct->whereRaw('stock_direct_data.product_id <> 20226 and isnull(product.product_type, 0) = 1');
            $direct->whereRaw(sql_year_month('stock_direct.invoice_dt').'=?', [$ym]);
            $direct->selectRaw('
                COUNT(DISTINCT stock_direct.id) AS count, 
                SUM(stock_direct_data.quantity) AS quantity, 
                sum(isnull(stock_direct_data.money, 0) - isnull(stock_direct_data.other_money, 0)) money
            ');
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $direct->whereIn($key, $where);
                }
            }
            $res = $direct->get();
            $rows[] = ['title' => '本月直营共发出 <span class="red">'.number_format($res->sum('count')).'</span> 张发货单，<span class="red">'.number_format($res->sum('quantity')).' </span>件，<span class="red">'.number_format($res->sum('money')).'</span> 元'];

            $json['total'] = sizeof($rows);
            $json['data'] = $rows;
            return $json;
        }
        return $this->render();
    }

    /**
     * 明日预计到货列表
     */
    public function goods()
    {
        if (Request::method() == 'POST') {

            $region = regionCustomer();
            // 昨天
            $lastDay = date("Y-m-d", strtotime("+1 day"));
            $model = DB::table('stock_delivery')
            ->leftJoin('customer', 'customer.id', '=', 'stock_delivery.customer_id')
            ->whereRaw('stock_delivery.freight_arrival_date = ? and stock_delivery.freight_arrival_date IS NOT NULL', [$lastDay]);
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }
            $rows = $model->selectRaw('stock_delivery.id,stock_delivery.sn,customer.name,stock_delivery.freight_arrival_date')
            ->get();
            $json['total'] = $rows->count();
            $json['data'] = $rows;
            return $json;
        }
        return $this->render();
    }

    /**
     * 订单数量
     */
    public function orderCount()
    {
        $config = InfoService::getInfo('customer_order');

        $model = DB::table('customer_order')
        ->leftJoin('customer','customer.id', '=', 'customer_order.customer_id')
        ->leftJoin('customer_order_data','customer_order_data.order_id', '=', 'customer_order.id')
        ->whereRaw('('.$config['sql'].')');

        $model2 = DB::table('customer_order')
        ->leftJoin('customer','customer.id', '=', 'customer_order.customer_id')
        ->leftJoin('customer_order_data','customer_order_data.order_id', '=', 'customer_order.id')
        ->whereRaw('('.$config['sql2'].')');

        $region = regionCustomer();
        if ($region['authorise']) {
            foreach ($region['whereIn'] as $key => $where) {
                $model->whereIn($key, $where);
                $model2->whereIn($key, $where);
            }
        }

        $count = $model->sum('customer_order_data.money');
        $count2 = $model2->sum('customer_order_data.money');

        $rate = 0;
        if ($count2 > 0) {
            $rate = ($count - $count2) / $count2 * 100;
            $rate = number_format($rate, 2);
        }
        $res = [
            'count' => $count,
            'count2' => $count2,
            'rate' => $rate,
        ];
        return $this->json($res, true);
    }
}