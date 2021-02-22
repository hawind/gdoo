<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class OrderTransport extends BaseModel
{
    protected $table = 'order_transport';
    
    public function datas()
    {
        return $this->hasMany('Gdoo\Order\Models\OrderData', 'order_id', 'order_id');
    }
}
