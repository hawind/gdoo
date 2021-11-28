<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\User\Models\User;
use Gdoo\Customer\Models\Customer;
use Gdoo\Customer\Models\CustomerTax;
use Gdoo\User\Services\UserService;

class CustomerHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        $master = $params['master'];

        $_user = [
            'role_id' => 2,
            'group_id' => 2,
            'name' => $master['name'],
            'username' => $master['code'],
            'password' => $master['password'],
            'department_id' => $master['department_id'],
            'phone' => $master['head_phone'],
            'status' => $master['status'],
        ];
        $user = UserService::updateData($master['user_id'], $_user);
        $master['user_id'] = $user->id;

        $params['master'] = $master;

        return $params;
    }

    public function onAfterStore($params) 
    {
        $master = $params['master'];

        // 客户开票单位为空
        $count = CustomerTax::where('customer_id', $master['id'])->count();
        if ($count == 0) {
            // 新建开票单位
            CustomerTax::insert([
                'code' => $master['code'],
                'name' => $master['name'],
                'customer_id' => $master['id'],
                'class_id' => $master['class_id'],
                'department_id' => $master['department_id'],
                'status' => 1,
            ]);
        }

        // 客户档案同步外部接口
        $department = DB::table('department')->where('id', $master['department_id'])->first();
        $class = DB::table('customer_class')->where('id', $master['class_id'])->first();
        $master['class_code'] = $class['code'];
        $master['department_code'] = $department['code'];
        $ret = plugin_sync_api('postCustomer', $master);
        if ($ret['error_code'] > 0) {
            abort_error($ret['msg']);
        }

        return $params;
    }

    public function onBeforeDelete($params) {
        $ids = $params['ids'];
        $userIds = Customer::whereIn('id', $ids)->pluck('user_id');
        User::whereIn('id', $userIds)->delete();
        return $params;
    }

    public function onBeforeImport($params) 
    {
        $row = $params['row'];
        $ret = $params['ret'];
        abort_error('客户档案暂时无法导入');
    }
}
