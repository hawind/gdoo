<?php namespace Gdoo\Promotion\Hooks;

class PromotionHook
{
    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        return $params;
    }

    public function onBeforeAudit($params) {
        // 流程结束写入生效日期
        $master = $params['master'];
        $master['actived_dt'] = date('Y-m-d');
        $params['master'] = $master;
        return $params;
    }

    public function onFormFieldFilter($params) {
        return $params;
    }
    
    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
}
