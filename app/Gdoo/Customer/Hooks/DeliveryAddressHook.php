<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\Customer\Models\Customer;
use Gdoo\Customer\Models\DeliveryAddress;

class DeliveryAddressHook
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
        if ($master['is_default'] == 1) {
            $customer = Customer::find($master['customer_id']);
            if ($customer) {
                $customer->warehouse_contact = $master['name'];
                $customer->warehouse_tel = $master['tel'];
                $customer->warehouse_phone = $master['phone'];
                $customer->warehouse_address = $master['address'];
                $customer->save();
            }
        }
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
}
