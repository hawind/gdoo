<?php namespace Gdoo\Customer\Hooks;

use DB;
use Gdoo\Customer\Models\CustomerRegion;

class RegionHook
{
    static $linkOptions = [];

    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) {
        $master = $params['master'];
        $master['parent_id'] = (int)$master['parent_id'];
        $params['master'] = $master;
        return $params;
    }

    public function onAfterStore($params) {
        CustomerRegion::treeRebuild();
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}
