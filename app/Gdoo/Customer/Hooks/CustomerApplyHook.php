<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\User\Models\User;
use Gdoo\Customer\Models\CustomerApply;
use Gdoo\Customer\Models\Customer;
use Gdoo\Customer\Models\CustomerTax;
use Gdoo\User\Services\UserService;

class CustomerApplyHook
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
        return $params;
    }

    public function onBeforeAudit($params) 
    {
        $id = $params['id'];

        $apply = DB::table('customer_apply')
        ->where('id', $id)
        ->selectRaw('
            code,
            name,
            type_id,
            department_id,
            remark,
            region_id,
            class_id,
            class2_id,
            province_id,
            city_id,
            county_id,
            address,
            status,
            warehouse_address,
            warehouse_contact,
            warehouse_phone,
            warehouse_tel,
            warehouse_size,
            head_name,
            head_phone,
            manage_name,
            manage_phone,
            manage_weixin,
            finance_name,
            finance_phone,
            cost_name,
            cost_phone,
            tax_number,
            bank_name,
            bank_account,
            bank_address
        ')->first();
        
        $apply['status'] = 1;

        // 新建用户
        $_user = [
            'role_id' => 2,
            'group_id' => 2,
            'username' => $apply['code'],
            'name' => $apply['name'],
            'department_id' => $apply['department_id'],
            'phone' => $apply['head_phone'],
            'password' => '123456',
            'status' => 1,
        ];
        $user = UserService::updateData(0, $_user);
        $apply['user_id'] = $user->id;

        // 新建客户
        $customer = new Customer;
        $customer->fill($apply);
        $customer->save();

        // 新建开票单位
        CustomerTax::insert([
            'code' => $customer->code,
            'name' => $customer->name,
            'customer_id' => $customer->id,
            'class_id' => $customer->class_id,
            'department_id' => $customer->department_id,
            'bank_name' => $apply['bank_name'],
            'tax_number' => $apply['tax_number'],
            'bank_account' => $apply['bank_account'],
            'bank_address' => $apply['bank_address'],
            'status' => 1,
        ]);

        // 客户档案同步接口
        $department = DB::table('department')->where('id', $customer['department_id'])->first();
        $class = DB::table('customer_class')->where('id', $customer['class_id'])->first();
        $customer['class_code'] = $class['code'];
        $customer['department_code'] = $department['code'];
        $ret = plugin_sync_api('postCustomer', $customer);
        if ($ret['error_code'] > 0) {
            abort_error($ret['msg']);
        }
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
}
