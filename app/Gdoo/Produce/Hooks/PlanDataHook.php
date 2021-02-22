<?php namespace Gdoo\Produce\Hooks;

use DB;

class PlanDataHook
{
    public function onQueryForm($params) {
        $q = $params['q'];
        $q->orderBy('product_id_product.code', 'asc');
        $params['q'] = $q;
        return $params;
    }
}
