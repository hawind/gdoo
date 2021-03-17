<?php namespace Gdoo\Model\Controllers;

use DB;
use Auth;
use Request;
use Validator;

use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Models\Template;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Model\Services\ModelService;
use Gdoo\Model\Services\ModuleService;

class TemplateController extends DefaultController
{
    public $permission = ['create', 'create2', 'create3'];

    public function index()
    {
        if (Request::method() == 'POST') {
            $sorts = Request::get('sort');
            $i = 0;
            foreach ($sorts as $sort) {
                Template::where('id', $sort)->update(['sort' => $i]);
                $i ++;
            }
            return $this->json('恭喜你，操作成功。', true);
        }

        $bill_id = Request::get('bill_id');
        $rows = Template::where('bill_id', $bill_id)
        ->orderBy('sort', 'asc')
        ->get();

        $models = DB::table('model')->where('parent_id', 0)->orderBy('lft', 'asc')->get();
        $model = Model::find($bill_id);

        return $this->display([
            'rows' => $rows,
            'bill_id' => $bill_id,
            'models' => $models,
            'model' => $model,
        ]);
    }

    public function create()
    {
        $gets = Request::all();

        if (Request::method() == 'POST') {
            $rules = [
                'name' => 'required',
            ];
            $v = Validator::make($gets, $rules);

            if ($v->fails()) {
                return $this->back()->withErrors($v)->withInput();
            }

            $gets['type'] = join(',', (array)$gets['type']);
            $gets['client'] = join(',', (array)$gets['client']);
            $gets['tpl'] = json_encode($gets['columns'], JSON_UNESCAPED_UNICODE);
            unset($gets['columns']);
            
            $model = Template::findOrNew($gets['id']);
            $model->fill($gets);
            $model->save();
            return $this->json('恭喜你，操作成功。', url('create', ['bill_id' => $gets['bill_id'], 'id' => $gets['id']]));
        }

        $bill_id = Request::get('bill_id');
        $bill = DB::table('model_bill')->find($bill_id);

        // 主模型
        $master_model = null;

        // 获取全部模型
        $models = ModelService::getModelAllFields($bill['model_id']);
        $lists = [];
        foreach ($models as $model) {
            if ($model['parent_id'] == 0) {
                $master_model = $model;
            }
            foreach ($model['fields'] as $field) {
                $field['table'] = $model['table'];
                $lists[$field['id']] = $field;
            }
        }

        $template = DB::table('model_template')->find($gets['id']);
        $tpl = $template['tpl'];
        $views = json_decode($tpl, true);

        foreach ((array)$views as $i => $view) {
            $v1 = $view['fields'];
            foreach ($v1 as $j => $sublist) {
                $_fields = $sublist['fields'];
                if ($_fields) {
                    foreach ($_fields as $k => $_field) {
                        if (empty($_field['table'])) {
                            $list = $lists[$_field['id']];
                            $_field['table'] = $list['table'];
                            unset($_field['id']);
                        } else {
                            $list = $models[$_field['table']]['fields'][$_field['field']];
                        }
                        if ($list) {
                            $_field['name'] = $list['name'];
                        }
                        $_fields[$k] = $_field;

                        unset($models[$_field['table']]['fields'][$_field['field']]);
                    }
                    $v1[$j]['fields'] = $_fields;
                } else {
                    if (empty($sublist['table'])) {
                        $list = $lists[$sublist['id']];
                        $sublist['table'] = $list['table'];
                        unset($sublist['id']);
                    } else {
                        $sublist['table'] = $master_model['table'];
                        $list = $models[$master_model['table']]['fields'][$sublist['field']];
                    }
                    if ($list) {
                        $sublist['name'] = $list['name'];
                        $sublist['field'] = $list['field'];
                    }
                    $v1[$j] = $sublist;
                    unset($models[$sublist['table']]['fields'][$sublist['field']]);
                }
            }
            $views[$i]['fields'] = $v1;
        }
        
        $template['type'] = explode(',', $template['type']);
        $template['client'] = explode(',', $template['client']);

        return $this->display([
            'views' => $views,
            'template' => $template,
            'model' => $model,
            'master_model' => $master_model,
            'bill_id' => $bill_id,
            'models' => $models,
        ]);
    }

    public function create2()
    {
        $gets = Request::all();

        if (Request::method() == 'POST') {

            $gets['code'] = $gets['table'].$gets['code'];
            $rules = [
                'name' => 'required',
                'code' => 'required',
            ];
            $v = Validator::make($gets, $rules);

            if ($v->fails()) {
                return $this->back()->withErrors($v)->withInput();
            }

            $gets['type'] = join(',', (array)$gets['type']);
            $gets['client'] = join(',', (array)$gets['client']);
            $gets['tpl'] = json_encode($gets['columns'], JSON_UNESCAPED_UNICODE);
            unset($gets['columns']);

            $model = Template::findOrNew($gets['id']);
            $model->fill($gets);
            $model->save();
            return $this->json('恭喜你，操作成功。', true);
        }

        $bill_id = Request::get('bill_id');
        $bill = DB::table('model_bill')->find($bill_id);
        $template = DB::table('model_template')->find($gets['id']);

        $leftFields = [];
        $rightFields = [];
        $views = (array)json_decode($template['tpl'], true);

        // 获取所有模型
        $models = array_by(ModelService::getModels($bill['model_id']), 'id');
        foreach ($views as $view) {
            if (empty($view['table'])) {
                $view['table'] = $models[$view['model_id']]['table'];
            }
            unset($view['model_id']);
            unset($view['id']);
            $rightFields[$view['table'].'.'.$view['field']] = $view;
        }

        $model = DB::table('model')->find($bill['model_id']);
        $_fields = DB::table('model_field')->where('model_id', $model['id'])->orderBy('sort', 'asc')->get();
        foreach ($_fields as $field) {
            $field_key = $model['table'].'.'.$field['field'];
            $rightField = $rightFields[$field_key];
            if ($rightField) {
                $rightField['name'] = $field['name'];
                $rightField['table'] = $model['table'];
                $rightField['field'] = $field['field'];
                $rightFields[$field_key] = $rightField;
                continue;
            }
            $leftFields[] = ['table' => $model['table'], 'field' => $field['field'], 'name' => $field['name']];
        }

        // 子模型
        $childrens = DB::table('model')->where('parent_id', $model['id'])->get();
        foreach ($childrens as $children) {
            $_fields = DB::table('model_field')->where('model_id', $children['id'])->orderBy('sort', 'asc')->get();
            foreach ($_fields as $field) {
                $field_key = $children['table'].'.'.$field['field'];
                $rightField = $rightFields[$field_key];
                if ($rightField) {
                    $rightField['table'] = $children['table'];
                    $rightField['field'] = $field['field'];
                    $rightField['name'] = '['.$children['name'].']'.$field['name'];
                    $rightFields[$field_key] = $rightField;
                    continue;
                }
                $leftFields[] = ['table' => $children['table'], 'field' => $field['field'], 'name' => '['.$children['name'].']'.$field['name']];
            }
        }

        $rightFields = array_values($rightFields);

        $models = DB::table('model')->where('parent_id', 0)->orderBy('lft', 'asc')->get();
        $template['leftFields'] = $leftFields;
        $template['rightFields'] = $rightFields;
        unset($template['tpl']);
        $template['type'] = explode(',', $template['type']);
        $template['client'] = explode(',', $template['client']);
        $template['code'] = str_replace($model['table'].'_', '', $template['code']);

        return $this->display([
            'template' => $template,
            'model' => $model,
            'bill_id' => $bill_id,
            'models' => $models,
        ]);
    }

    public function delete()
    {
        $id = Request::get('id');
        if ($id > 0) {
            DB::table('model_template')->where('id', $id)->delete();
            return $this->success('index', '恭喜你，操作成功。');
        }
    }
}
