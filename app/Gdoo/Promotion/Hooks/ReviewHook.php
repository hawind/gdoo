<?php namespace Gdoo\Promotion\Hooks;

use DB;
use Exception;

class ReviewHook
{
    public function onBeforeForm($params) {
        $row = $params['row'];
        $promotion = DB::table('promotion')->where('id', $row['promotion_id'])->first();
        $row['field021'] = $promotion['field021'];
        $row['field022'] = $promotion['field022'];
        $row['field023'] = $promotion['field023'];
        $row['field024'] = $promotion['field024'];
        $row['field025'] = $promotion['field025'];
        $row['field026'] = $promotion['field026'];
        $row['field027'] = $promotion['field027'];
        $row['field028'] = $promotion['field028'];
        $row['field029'] = $promotion['field029'];

        $params['row'] = $row;
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) {
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeAudit($params) {
        $id = $params['id'];
        // 生效费用
        $row = DB::table('promotion_review')->where('id', $id)->first();
        if ($row['use_order'] == 1) {
            // 生成费用类型
            $categorys = [1 => 4, 3 => 5];
            $master = [
                'sn' => $row['sn'],
                'date' => $row['date'],
                'category_id' => $categorys[$row['pay_type']],
                'type_id' => 55,
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
                'src_type_id' => 55,
                'status' => 1,
            ]);
        }
        return $params;
    }

    public function onBeforeAbort($params) {
        $id = $params['id'];
        $review = DB::table('promotion_review')->where('id', $id)->first();
        $cost_count = DB::table('customer_cost')->where('sn', $review['sn'])->count();
        if ($cost_count > 0) {
            abort_error('客户费用单号['.$review['sn'].']已经存在无法弃审。');
        }
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}
