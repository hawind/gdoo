<?php namespace Gdoo\Promotion\Models;

use DB;
use Gdoo\Index\Models\BaseModel;
use Gdoo\Promotion\Models\Promotion;
use Gdoo\Promotion\Models\PromotionCashData;

class PromotionCash extends BaseModel
{
    protected $table = 'promotion_cash';

    public function datas()
    {
        return $this->hasMany(PromotionCashData::class, 'cash_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    // 促销兑现单生成客户订单
    public function makeCustomerOrder($cashId)
    {
        // 客户订单数
        $cash = PromotionCash::with('datas')->find($cashId);

        $total_num = DB::table('order')->count('id');

        $master['customer_id']    = $cash->promotion->customer_id;
        $master['flow_step_id'] = 1;
        $master['number']       = 'CXDX-'.date('Y-m').'-'.($total_num + 1);
        $master['add_time']     = time();
        $orderId = DB::table('order')->insertGetId($master);
        
        foreach ($cash->datas as $data) {
            $row = [
                'order_id'   => $orderId,
                'customer_id'  => $cash->promotion->customer_id,
                'product_id' => $data['product_id'],
                'price'      => $data['price'],
                'amount'     => $data['quantity'],
                'type'       => $data['type_id'],
                'content'    => $data['remark'],
            ];
            DB::table('order_data')->insert($row);
        }
        return true;
    }
}
