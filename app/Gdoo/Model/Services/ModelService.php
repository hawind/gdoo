<?php namespace Gdoo\Model\Services;

use DB;
use Gdoo\Model\Models\Model;

class ModelService
{
    public static function getModelAllFields($model_id) {
        $res = [];
        $model = DB::table('model')->find($model_id);
        $fields = DB::table('model_field')
        ->where('model_id', $model['id'])
        ->orderBy('sort', 'asc')
        ->get()->keyBy('field')->toArray();
        $model['fields'] = $fields;
        $res[$model['table']] = $model;

        // 子模型
        $childrens = DB::table('model')->where('parent_id', $model['id'])->get();
        foreach ($childrens as $children) {
            $fields = DB::table('model_field')
            ->where('model_id', $children['id'])
            ->orderBy('sort', 'asc')
            ->get()->keyBy('field')->toArray();
            $children['fields'] = $fields;
            $res[$children['table']] = $children;
        }
        return $res;
    }

    public static function getModels($model_id) {
        $master = static::getModel($model_id);
        $models = Model::with(['fields' => function ($q) {
            $q->orderBy('sort', 'asc')->orderBy('id', 'asc');
        }])->where('parent_id', $master->id)
        ->get();

        $res = [$master];
        foreach($models as $model) {
            $res[] = $model;
        }
        return $res;
    }

    public static function getModel($model_id) {
        $master = Model::with(['fields' => function ($q) {
            $q->orderBy('sort', 'asc')
            ->orderBy('id', 'asc');
        }])->find($model_id);
        
        return $master;
    }
}