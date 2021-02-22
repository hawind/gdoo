<?php namespace Gdoo\Order\Hooks;

class OrderDataHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onQueryForm($params) {
        $q = $params['q'];
        $q->leftJoin('customer_order_type as cot', 'cot.id', '=', 'customer_order_data.type_id')
        ->orderBy('cot.sort', 'asc')
        ->orderBy('product_id_product.code', 'asc')
        ->orderBy('customer_order_data.id', 'asc');

        $params['q'] = $q;
        return $params;
    }

    public function onAfterForm($arguments) {
        return $arguments;
    }
}
