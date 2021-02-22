<?php namespace App\Support;

use DB;
use Request;

trait LogRecord
{
    // 注意,必须以 boot 开头
    public static function bootLogRecord()
    {
        foreach(static::getModelEvents() as $event) {
            static::$event(function ($model) use($event) {
                $model->setRemind($model, $event);
            });
        }
    }
 
    public static function getModelEvents()
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }
        return ['created', 'updated', 'deleted'];
    }
 
    public function setRemind($model, $event)
    {
        $data = [
            'node' => Request::module(),
            'uri' => Request::module().'.'.Request::controller().'.'.Request::action(),
            'table' => $model->getTable(),
            'table_id' => (int)$model['id'],
            'action' => $event,
        ];

        if($event == 'created') {
            $data['original'] = json_encode($model, JSON_UNESCAPED_UNICODE);
        }

        if($event == 'updated') {

            $original = $model->getOriginal();
            $dirty = $model->getDirty();

            // 软删除动作
            if(isset($dirty['deleted_by'])) {
                $data['action'] = 'trashed';
            } else {
                // 更新动作
                $_original = [];
                foreach ($dirty as $k => $v) {
                    $_original[$k] = $original[$k];
                }
                $data['original'] = json_encode($_original, JSON_UNESCAPED_UNICODE);
                $data['dirty'] = json_encode($dirty, JSON_UNESCAPED_UNICODE);
            }
        }
        // DB::table('action_log')->insert($data);
    }
}