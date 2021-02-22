<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\User\Models\User;
use Gdoo\Customer\Models\CustomerApply;
use Gdoo\Customer\Models\Customer;
use Gdoo\Customer\Models\CustomerTax;

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

    public function onBeforeAudit($params) {
        $id = $params['id'];
        $apply = DB::table('customer_apply')->where('id', $id)
        ->selectRaw('
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
            name,
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

        // 自动处理区域
        if ($apply['region_id']) {
            $regions = DB::table('customer_region')->get()->keyBy('id');
            $region1 = $regions[$apply['region_id']];
            $region2 = $regions[$region1['parent_id']];
            $region3 = $regions[$region2['parent_id']];
            $apply['region2_id'] = $region2['id'];
            $apply['region3_id'] = $region3['id'];
        }

        // 新建客户
        $customer = new Customer;
        $customer->fill($apply);
        $customer->save();

        // 新建用户
        $_user = [
            'role_id' => 2,
            'group_id' => 2,
            'username' => $customer['id'],
            'name' => $customer['name'],
            'department_id' => $customer['department_id'],
            'phone' => $customer['head_phone'],
            'password' => bcrypt('123456'),
            'status' => 1,
        ];
        $user = new User;
        $user->fill($_user)->save();
        $customer['user_id'] = $user->id;

        // 重新更新客户数据
        $customer->code = $customer['id'];
        $customer->save();

        // 自动新建开票单位
        CustomerTax::insert([
            'customer_id' => $customer->id,
            'class_id' => $customer->class_id,
            'department_id' => $customer->department_id,
            'code' => $customer->code,
            'name' => $customer->name,
            'bank_name' => $apply['bank_name'],
            'tax_number' => $apply['tax_number'],
            'bank_account' => $apply['bank_account'],
            'bank_address' => $apply['bank_address'],
            'status' => 1,
        ]);

        // 回写申请的客户编码
        $_apply = CustomerApply::find($id);
        $_apply->code = $customer['code'];
        $_apply->save();

        // 客户档案写入用友
        $department = DB::table('department')->where('id', $customer['department_id'])->first();
        $class = DB::table('customer_class')->where('id', $customer['class_id'])->first();
        $customer['class_code'] = $class['code'];
        $customer['department_code'] = $department['code'];
        $customer['headCode'] = $customer['code'];

        $ret = plugin_sync_api('CustomerSync', $customer);
        if ($ret['success'] == true) {
            return $params;
        } 
        abort_error($ret['msg']);
    }

    public function onBeforeDelete($params) {
        return $params;
    }
}
