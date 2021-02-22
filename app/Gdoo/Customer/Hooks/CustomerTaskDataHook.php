<?php namespace Gdoo\Customer\Hooks;

class CustomerTaskDataHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onQueryForm($params) {
        $q = $params['q'];
        $q->orderByRaw('cast(customer_task_data.code as int) asc');

        $params['q'] = $q;
        return $params;
    }

    public function onAfterForm($arguments) {
        return $arguments;
    }
}
