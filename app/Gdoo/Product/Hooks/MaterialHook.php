<?php namespace Gdoo\Product\Hooks;

use DB;
use Gdoo\User\Models\User;
use Gdoo\Product\Models\ProductMaterial;

class MaterialHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        $gets = $params['gets'];
        $material = $gets['product_material'];
        $material_data = $gets['product_material_data'];
        
        $id = 0;

        // 新增或者修改
        foreach((array)$material_data['rows'] as $row) {
            $row['product_id'] = $material['product_id'];
            $_bom = ProductMaterial::findOrNew($row['id']);
            $_bom->fill($row)->save();
            $id = $_bom->id;
        }

        // 删除记录
        foreach((array)$material_data['deleteds'] as $row) {
            if ($row['id'] > 0) {
                ProductMaterial::where('id', $row['id'])->delete();
            }
        }

        $master['id'] = $id;
        $params['master'] = $master;

        // 终止执行的进程后
        $params['terminate'] = false;
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}