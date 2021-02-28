<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\User\Models\User;
use Gdoo\Customer\Models\Customer;
use Gdoo\Customer\Models\CustomerTax;

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
            'username' => $master['code'],
            'name' => $master['name'],
            'department_id' => $master['department_id'],
            'phone' => $master['head_phone'],
            'status' => $master['status'],
        ];

        // 更新用户表
        $user = User::findOrNew($master['user_id']);
        // 密码处理
        if (empty($master['password'])) {
            unset($master['password']);
        } else {
            $user['password'] = bcrypt($master['password']);
            $master['password'] = $user['password'];
        }
        $user->fill($_user)->save();
        $master['user_id'] = $user->id;
        $params['master'] = $master;

        return $params;
    }

    public function onAfterStore($params) {
        $master = $params['master'];
        if (empty($master['code'])) {
            // 自动设置客户编码
            $customer = Customer::find($master['id']);
            $customer->code = $customer['id'];
            $customer->save();

            // 自动设置用户名
            $user = User::find($customer['user_id']);
            $user->username = $customer['id'];
            $user->save();

            // 自动新建开票单位
            CustomerTax::insert([
                'customer_id' => $customer->id,
                'class_id' => $customer->class_id,
                'department_id' => $customer->department_id,
                'code' => $customer->code,
                'name' => $customer->name,
                'status' => 1,
            ]);

            // 客户档案写入外部接口
            $department = DB::table('department')->where('id', $master['department_id'])->first();
            $class = DB::table('customer_class')->where('id', $customer['class_id'])->first();
            $customer['class_code'] = $class['code'];
            $customer['department_code'] = $department['code'];
            $customer['headCode'] = $customer->code;
            $ret = plugin_sync_api('CustomerSync', $customer);
            if ($ret['success'] == true) {
                return $params;
            } 
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
