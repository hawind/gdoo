<?php namespace Gdoo\User\Controllers;

use DB;
use Request;

use Gdoo\Index\Controllers\DefaultController;

class RegionController extends DefaultController
{
    public $permission = ['index', 'json'];

    /**
     * 市列表
     */
    public function index()
    {
        $id = (int) Request::get('id', '0');
        $parent_id = (int) Request::get('parent_id', '0');
        $type = Request::get('type', 'province');
        $layer = 1;
        if ($type == 'city') {
            $layer = 2;
        } elseif ($type == 'county') {
            $layer = 3;
        }
        $model = DB::table('region');
        $model = $model->where('layer', $layer);
        if ($parent_id > 0) {
            $model->where('parent_id', $parent_id);
        }
        $_data = $model->get();
        if ($_data) {
            $data = array();
            foreach ($_data as $k => $v) {
                $selected = ($id == $v['id']) ? ' selected' : '';
                $data[] = '<option value="'.$v['id'].'"'.$selected.'>'.$v['name'].'</option>';
            }
        }
        echo join("\n", $data);
    }
}
