<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Auth;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\User\Models\User;
use Gdoo\Customer\Services\CustomerService;

use Gdoo\Index\Controllers\DefaultController;

class ReportController extends DefaultController
{
    public $permission = [];

    // 客户对账单
    public function accountStatementAction()
    {
        $sdate = date('Y-01-01');
        $edate = date('Y-m-d');
        $search = search_form([
            'advanced' => 0,
        ], [
            ['form_type' => 'date2', 'name' => '日期', 'field' => 'date', 'value' => [$sdate, $edate], 'options' => []],
            ['form_type' => 'dialog', 'name' => '客户', 'field' => 'customer_id', 'options' => [
                'url' => 'customer/customer/dialog', 'query' => ['multi'=>0]
            ]],
            ['form_type' => 'dialog', 'name' => '开票单位', 'field' => 'tax_id', 'options' => [
                'url' => 'customer/tax/dialog', 'query' => ['multi'=>0]
            ]],
        ], 'model');
        
        $query = $search['query'];

        if (Request::method() == 'POST') {

            $fields = [];
            foreach($search['forms']['field'] as $i => $field) {
                $fields[$field] = $search['forms']['search'][$i];
            }

            $start_dt = $fields['date'][0];
            $end_dt = $fields['date'][1];
            
            $customer_id = (int)$fields['customer_id'];

            $tax_id = $fields['tax_id'];
            if ($tax_id > 0) {
                $tax = DB::table('customer_tax')->find($tax_id);
                $customer_id = $tax['customer_id'];
            }
            $taxs = DB::table('customer_tax')->where('customer_id', $customer_id)->get();
            $tax_names = [];
            $tax_names2 = [];
            foreach($taxs as $tax) {
                $tax_names[$tax['code']] = $tax['name'];
                $tax_names2[$tax['id']] = $tax['name'];
            }

            $ret = [];
            if ($start_dt && $end_dt && $customer_id) {

                $json = collect();
                $one = [];

                // 获取外部接口数据
                $res = plugin_sync_api('acclist/code/'.$taxs->implode('code', ',').'/start_dt/'.$start_dt.'/end_dt/'.$end_dt);
                if (count($res['data'])) {
                    // 获取初期余额
                    $one = array_shift($res['data']);
                    foreach($res['data'] as $row) {
                        if ($row['dgst'] == '合计') {
                            continue;
                        }
                        $row['tax_name'] = $tax_names[$row['cdwcode']];
                        $json->push($row);
                    }     
                }

                $rows = CustomerService::getAccList($customer_id, $start_dt, $end_dt);
                $ye = (float)$one['ye'];
                foreach($rows as $row) {
                    if ($row['dgst'] == '合计') {
                        continue;
                    }
                    if ($row['dgst'] == '期初余额') {
                        $row['ye'] = $row['ye'] + $ye;
                    }
                    $row['tax_name'] = $tax_names2[$row['tax_id']];
                    $json->push($row);
                }

                // 多字段排序
                $json = $json->multiSortBy(['orderNum' => 'asc', 'dDate' => 'asc', 'orderD' => 'asc']);

                $ye = 0;
                $json->transform(function($row) use (&$ye) {
                    if ($row['dgst'] == '期初余额') {
                        $ye = $row['ye'];
                    } else {
                        $ye = $ye + $row['df'] - $row['jf'] - $row['bcsyfy'];
                    }
                    $row['ye'] = $ye;
                    return $row;
                });

                $json->push([
                    'dgst' => '合计',
                    'orderNum' => 3,
                    'qtfy' => $json->sum('qtfy'),
                    'xzfy' => $json->sum('xzfy'),
                    'bcsyfy' => $json->sum('bcsyfy'),
                    'jf' => $json->sum('jf'),
                    'df' => $json->sum('df'),
                    'sl' => $json->sum('sl'),
                    'ye' => $ye,
                ]);

                $bills = DB::table('model_bill')->get()->keyBy('id');

                foreach($json as $row) {

                    $bill = $bills[$row['srcMasterBillType']];
                    $row['bill_name'] = $bill['name'];
                    $row['url'] = $bill['uri'].'/show';
                    $ret[] = $row;
                }

            }
            return $this->json($ret, true);
        }
        $search['table'] = 'material_plan';
        return $this->display([
            'search' => $search, 
            'query' => $query,
        ]);
    }
}
