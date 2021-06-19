<?php namespace Gdoo\System\Hooks;

use DB;
use Gdoo\System\Models\Region;

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

        $master['layer'] = 1;
        if ($master['parent_id'] > 0) {
            $parent = Db::table('region')->where('id', $master['parent_id'])->first();
            $master['layer'] = $parent['layer'] + 1;
        }

        $params['master'] = $master;
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}
