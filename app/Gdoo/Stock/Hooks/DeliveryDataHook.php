<?php namespace Gdoo\Stock\Hooks;

class DeliveryDataHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onQueryForm($params) {
        $q = $params['q'];
        $q->leftJoin('customer_order_type as cot', 'cot.id', '=', 'stock_delivery_data.type_id')
        ->orderBy('cot.sort', 'asc')
        ->orderBy('product_id_product.code', 'asc')
        ->orderBy('stock_delivery_data.id', 'asc');

        $params['q'] = $q;
        return $params;
    }

    public function onAfterForm($arguments) {
        return $arguments;
    }
}
