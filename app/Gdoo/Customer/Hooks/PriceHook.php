<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\User\Models\User;
use Gdoo\Customer\Models\CustomerPrice;

class PriceHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        $gets = $params['gets'];
        $_price = $gets['customer_price'];
        $data = $gets['customer_price_data'];

        $id = 0;

        // 新增或者修改
        foreach((array)$data['rows'] as $row) {
            $row['customer_id'] = $_price['customer_id'];
            $price = CustomerPrice::findOrNew($row['id']);
            $price->fill($row)->save();
            $id = $price->id;
        }

        // 删除记录
        foreach((array)$data['deleteds'] as $row) {
            if ($row['id'] > 0) {
                CustomerPrice::where('id', $row['id'])->delete();
            }
        }

        $master['id'] = $id;
        $params['master'] = $master;

        // 终止执行的进程后
        $params['terminate'] = false;
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}
