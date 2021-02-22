<?php namespace Gdoo\Product\Hooks;

use DB;
use Gdoo\Product\Models\ProductCategory;

class CategoryHook
{
    public function onBeforeForm($params)
    {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        $master = $params['master'];
        $master['type'] = 1;
        $params['master'] = $master;
        return $params;
    }

    public function onAfterStore($params) {
        ProductCategory::treeRebuild();
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}
