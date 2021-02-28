<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\User\Models\User;
use Gdoo\Customer\Models\Customer;
use Gdoo\Customer\Models\CustomerTax;

class TaxHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        return $params;
    }

    public function onAfterStore($params) {
        $master = $params['master'];
        if (empty($master['code'])) {
            // 自动设置开票编码
            $customer = Customer::find($master['customer_id']);
            $code = $customer['code'];
            $max_id = (int)$customer['tax_max_id'] + 1;

            // 更新开票单位code
            $tax = CustomerTax::find($master['id']);
            $tax->code = $code.$max_id;
            $tax->save();

            $customer->tax_max_id = $max_id;
            $customer->save();

            // 客户档案写入外部接口
            $department = DB::table('department')->where('id', $tax['department_id'])->first();
            $class = DB::table('customer_class')->where('id', $tax['class_id'])->first();
            $tax['class_code'] = $class['code'];
            $tax['department_code'] = $department['code'];
            $tax['headCode'] = $customer->code;
            $ret = plugin_sync_api('CustomerSync', $tax);
            if ($ret['success'] == true) {
                return $params;
            } 
            abort_error($ret['msg']);
        }
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
}
