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

        // 开票单位同步外部接口
        $department = DB::table('department')->where('id', $master['department_id'])->first();
        $class = DB::table('customer_class')->where('id', $master['class_id'])->first();
        $master['class_code'] = $class['code'];
        $master['department_code'] = $department['code'];
        $ret = plugin_sync_api('postTax', $master);
        if ($ret['error_code'] > 0) {
            abort_error($ret['msg']);
        } 
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
}
