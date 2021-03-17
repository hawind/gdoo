<?php namespace Gdoo\Model\Controllers;

use DB;
use Auth;
use Request;
use Validator;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Models\Permission;

use Gdoo\Model\Services\FlowService;

use Gdoo\Index\Controllers\DefaultController;

class PermissionController extends DefaultController
{
    public $permission = ['index','create','delete'];

    public function index()
    {
        if (Request::method() == 'POST') {
            $sorts = Request::get('sort');
            $i = 0;
            foreach ($sorts as $sort) {
                Permission::where('id', $sort)->update(['sort' => $i]);
                $i ++;
            }
            return $this->json('恭喜你，操作成功。', true);
        }

        $bill_id = Request::get('bill_id');
        $rows = Permission::where('bill_id', $bill_id)
        ->orderBy('sort', 'asc')
        ->get();

        return $this->display([
            'bill_id' => $bill_id,
            'rows' => $rows,
        ]);
    }

    public function create()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();

            $rules = [
                'name' => 'required',
            ];
            $v = Validator::make($gets, $rules);

            $gets['data'] = json_encode($gets['data']);
            $gets['type'] = join(',', (array)$gets['type']);

            if ($v->fails()) {
                return $this->back()->withErrors($v)->withInput();
            }

            if ($gets['id']) {
                Permission::where('id', $gets['id'])->update($gets);
            } else {
                Permission::insert($gets);
            }

            return $this->success('index', ['bill_id' => $gets['bill_id']], '恭喜你，操作成功。');
        }

        $id = Request::get('id');
        $bill_id = Request::get('bill_id');
        $bill = Bill::find($bill_id);
        $permission = Permission::find($id);
        
        $permission['data'] = json_decode($permission['data'], true);

        $master = Model::with([
            'fields' => function ($q) {
                $q->orderBy('sort', 'asc')
                ->orderBy('id', 'asc');
            }])
        ->where('id', $bill->model_id)
        ->first();

        $sublist = Model::with(['fields' => function ($q) {
            $q->orderBy('sort', 'asc')
            ->orderBy('id', 'asc');
        }])->where('parent_id', $master->id)->get();

        $permission['bill_id'] = $bill->id;
        $permission['type'] = explode(',', $permission['type']);

        $models = DB::table('model')->where('parent_id', 0)->orderBy('lft', 'asc')->get();
        $regulars = FlowService::regulars();

        return $this->display([
            'permission' => $permission,
            'regulars' => $regulars,
            'bill_id' => $bill->id,
            'models' => $models,
            'master' => $master,
            'sublist' => $sublist,
        ]);
    }

    public function delete()
    {
        $id = Request::get('id');
        if ($id > 0) {
            Permission::where('id', $id)->delete();
            return $this->success('index', '恭喜你，操作成功。');
        }
    }
}
