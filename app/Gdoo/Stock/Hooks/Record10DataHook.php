<?php namespace Gdoo\Stock\Hooks;

class Record10DataHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onQueryForm($params) {
        $q = $params['q'];
        $q->orderBy('stock_record10_data.batch_sn', 'asc')
        ->orderBy('product_id_product.code', 'asc');

        $params['q'] = $q;
        return $params;
    }

    public function onAfterForm($arguments) {
        return $arguments;
    }
}
