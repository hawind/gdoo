<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class CancelOrderData extends BaseModel
{
    protected $table = 'cancel_order_data';

    public function datas()
    {
        return $this->hasMany('Gdoo\Order\Models\CancelOrderData');
    }
}
