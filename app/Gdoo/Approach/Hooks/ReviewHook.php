<?php namespace Gdoo\Approach\Hooks;

use DB;
use Gdoo\User\Models\User;

class ReviewHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onFieldFilter($params) {
        return $params;
    }

    public function onFormFieldFilter($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }
    
    public function onBeforeStore($params)
    {
        $gets = $params['gets'];
        $approach_review = $gets['approach_review'];
        $count = count($gets['approach_review_data']['rows']);
        $apply_id = $approach_review['apply_id'];
        $count2 = DB::table('approach_data')->where('approach_id', $apply_id)->count();
        if ($count <> $count2) {
            abort_error('进店申请条码数量和核销条码数量不一致。');
        }
        return $params;
    }

    public function onBeforeAudit($params) {
        $id = $params['id'];
        // 生效费用
        $row = DB::table('approach_review')->where('id', $id)->first();
        if ($row['use_order'] == 1) {
            // 生成费用类型
            $categorys = [1 => 4, 3 => 5];
            $master = [
                'sn' => $row['sn'],
                'date' => $row['date'],
                'category_id' => $categorys[$row['pay_type']],
                'type_id' => 57,
                'remark' => $row['remark'],
                'status' => 1,
            ];
            $cost_id = DB::table('customer_cost')->insertGetId($master);
            DB::table('customer_cost_data')->insert([
                'cost_id' => $cost_id,
                'customer_id' => $row['customer_id'],
                'money' => $row['fact_verification_cost'],
                'remain_money' => $row['fact_verification_cost'],
                'src_id' => $row['id'],
                'src_sn' => $row['sn'],
                'src_type_id' => 57,
                'status' => 1,
            ]);
        }
        return $params;
    }

    public function onBeforeAbort($params) {
        $id = $params['id'];
        $review = DB::table('approach_review')->where('id', $id)->first();
        $cost_count = DB::table('customer_cost')->where('sn', $review['sn'])->count();
        if ($cost_count > 0) {
            abort_error('客户费用单号['.$review['sn'].']已经存在无法弃审。');
        }
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        // 删除生成的费用
        return $params;
    }

    public function onBeforeImport($params) {
    }
}
