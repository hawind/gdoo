<?php namespace Gdoo\Promotion\Models;

use Gdoo\Index\Models\BaseModel;
use Gdoo\Promotion\Models\PromotionCash;

class PromotionCashData extends BaseModel
{
    protected $table = 'promotion_cash_data';

    public function cash()
    {
        return $this->belongsTo(PromotionCash::class);
    }
}
