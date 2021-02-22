<?php namespace Gdoo\Approach\Hooks;

use DB;
use Gdoo\User\Models\User;

class ApproachHook
{
    public function onBeforeForm($params) {
        $views = $params['views'];
        $view = $views[3];

        $view['fields'][] = [
            'field' => 'apply_percentage',
            'hidden' => 1,
            'width' => 40,
            'readonly' => 0,
            'hide_title' => 1,
            'type' => 0,
            'name' => '按回款核销(%)',
        ];
        $view['fields'][] = [
            'field' => 'order_payment_scale',
            'hidden' => 1,
            'width' => 40,
            'readonly' => 0,
            'hide_title' => 1,
            'type' => 0,
            'name' => '按订单进行兑付(%)',
        ];
        $views[3] = $view;
        $params['views'] = $views;
        return $params;
    }

    public function onFieldFilter($params) {
        $values = $params['values'];
        $field = $params['field'];
        $f = $field['field'];
        $value = $values[$f];
        if ($f == 'market_name') {
            if (strpos($value, 'draft_') === 0) {
                $name = str_replace('draft_', '', $value);
                $market = [
                    'customer_id' => $values['customer_id'],
                    'name' => $name,
                    'market_count' => $values['market_totol'],
                    'type_id' => $values['type_id'],
                    'single_cast' => $values['single_cast'],
                    'total_cast' => $values['totol_cast'],
                    'fax' => $values['fax'],
                    'market_address' => $values['market_address'],
                    'market_area' => $values['market_size'],
                    'market_person_name' => $values['market_contact'],
                    'market_person_phone' => $values['market_contact_phone'],
                ];
                $values['market_name'] = $name;
                $values['market_id'] = DB::table('approach_market')->insertGetId($market);  
            }
        }
        $params['values'] = $values;
        return $params;
    }

    public function onFormFieldFilter($params) {
        $_replace = $params['_replace'];

        $verification_info = $_replace['{verification_info}'];
        if ($verification_info) {
            $verification_info = $verification_info.'
                <div class="m-xs">
                    贵司出具发票：按回款(回款以我司批复之日起算) '.$_replace['{apply_percentage}'].'% 核销；
                    贵司未出具发票：按订单(订单以提交审核资料核销后) '.$_replace['{order_payment_scale}'].'% 进行兑付。直到核完我司支持费用为止。
                </div>
                <div class="red">
                    客户进场后必须在2个月内提交资料核销，否则不予受理。开始核销后，超过一年未核完的，将不再核销。
                </div>
            ';
            $_replace['{verification_info}'] = $verification_info;
            unset($_replace['{apply_percentage}']);
            unset($_replace['{order_payment_scale}']);
        }
    
        $params['_replace'] = $_replace;

        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeAudit($params) {
        // 流程结束写入生效日期
        $master = $params['master'];
        $master['actived_dt'] = date('Y-m-d');
        $params['master'] = $master;
        return $params;
    }

    public function onBeforeStore($params) {
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }

    public function onBeforeImport($params) {
    }
}
