<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerOrderData extends BaseModel
{
    protected $table = 'customer_order_data';

    public function product()
    {
        return $this->belongsTo('Gdoo\Product\Models\Product');
    }
}
