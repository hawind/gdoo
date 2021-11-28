<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\InfoService;

class WidgetController extends DefaultController
{
    public $permission = ['birthday', 'customerCount', 'customerContactCount'];

    // 客户生日
    public function birthday()
    {
        if (Request::method() == 'POST') {

            $model = DB::table('customer');
            $region = regionCustomer();
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            if ($this->dbType == 'sqlsrv') {
                $model->whereRaw('
                    (datediff(dd, getdate(), dateadd(year, datediff(year, head_birthday, getdate()), head_birthday)) between 0 and 7)
                    OR
                    (datediff(dd, getdate(), dateadd(year, datediff(year, head_birthday, getdate())+1, head_birthday)) between 0 and 7)')
                ->selectRaw('id, code, name, head_name, head_phone, head_birthday')
                ->get();
            } else if($this->dbType == 'mysql') {
                $model->whereRaw("
                    (concat(year(now()), DATE_FORMAT(head_birthday,'-%m-%d')) BETWEEN DATE_FORMAT(now(),'%Y-%m-%d') AND DATE_FORMAT(DATE_ADD(now(), interval 7 day),'%Y-%m-%d'))
                    OR 
                    (concat(year(now()) + 1, DATE_FORMAT(head_birthday,'-%m-%d')) BETWEEN DATE_FORMAT(now(),'%Y-%m-%d') AND DATE_FORMAT(DATE_ADD(now(), interval 7 day),'%Y-%m-%d'))")
                ->selectRaw("id, code, name, head_name, head_phone, concat(year(now()), DATE_FORMAT(head_birthday,'-%m-%d')) as head_birthday");
            }

            $rows = $model->get();

            $json['total'] = sizeof($rows);
            $json['data'] = $rows;
            return $json;
        }
        return $this->render();
    }

    /**
     * 客户(个)
     */
    public function customerCount()
    {
        $config = InfoService::getInfo('customer');

        $model = DB::table('customer')->whereRaw('('.$config['sql'].')');
        $model2 = DB::table('customer')->whereRaw('('.$config['sql2'].')');
        $region = regionCustomer();
        if ($region['authorise']) {
            foreach ($region['whereIn'] as $key => $where) {
                $model->whereIn($key, $where);
                $model2->whereIn($key, $where);
            }
        }

        $count = $model->count();
        $count2 = $model2->count();

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

    /**
     * 客户联系人(个)
     */
    public function customerContactCount()
    {
        $config = InfoService::getInfo('customer_contact');
        $model = DB::table('customer_contact')->whereRaw('('.$config['sql'].')');
        $model2 = DB::table('customer_contact')->whereRaw('('.$config['sql2'].')');
        $count = $model->count();
        $count2 = $model2->count();

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
