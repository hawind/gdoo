<?php namespace Gdoo\User\Controllers;

use DB;
use Auth;
use Request;
use Validator;
use Collection;

use App\Support\Module;

use Gdoo\User\Models\User;
use Gdoo\User\Models\Role;
use Gdoo\User\Models\UserAsset;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use App\Support\License;
use Arr;
use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Model\Services\ModuleService;
use Gdoo\User\Services\UserAssetService;

class RoleController extends DefaultController
{
    public $permission = ['dialog', 'permission'];

    public function indexAction()
    {
        $display = $this->access;

        $header = Grid::header([
            'code' => 'role',
            'referer' => 1,
            'search' => ['by' => '', 'tab' => 'role'],
        ]);

        $cols = $header['cols'];
        $cols = Grid::addColumns($cols, 'code', [[
            'headerName' => '用户数',
            'field' => 'user_count',
            'footerRenderer' => 'sum',
            'width' => 60,
            'cellStyle' => ['text-align' => 'center'],
        ]]);
        $cols['actions']['options'] = [[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $display['edit'],
        ]];
        unset($cols['checkbox']);

        $cols['actions']['options'] = [[
            'name' => '权限',
            'action' => 'config',
            'display' => $this->access['config'],
        ],[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy('role.sort', 'asc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select'])
            ->addSelect(DB::raw('(select count(id) from [user] where role_id = role.id) as user_count'));

            $rows = $model->paginate($query['limit'])->appends($query);
            $items = Grid::dataFilters($rows, $header);
            return $items->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
        ];
        $header['cols'] = $cols;
        $header['tabs'] = User::$tabs;
        $header['bys'] = Role::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function configAction()
    {
        $gets = Request::all();

        $query = [
            'role_id' => 0,
            'clone_id' => 0,
            'key' => '',
        ];

        foreach ($query as $key => $value) {
            $query[$key] = Request::get($key, $value);
        }

        if (Request::method() == 'POST') {
            $assets = DB::table('user_asset')->get();
            $assets = array_by($assets, 'name');
            $id = $gets['role_id'];

            foreach ($gets['assets'] as $asset => $controllers) {

                $rules = json_decode($assets[$asset]['rules'], true);

                // 清除旧权限
                foreach ((array)$rules as $key => $rule) {
                    if (empty($controllers[$key])) {
                        unset($rules[$key]);
                    }
                }

                foreach ($controllers as $key => $actions) {
                    unset($rules[$key][$id]);
                    if ($actions['action']) {
                        $rules[$key][$id] = $actions['action'];
                    }
                }

                $_asset = DB::table('user_asset')->where('name', $asset)->first();
                
                $data = [
                    'name' => $asset,
                    'rules' => json_encode($rules),
                ];

                if (empty($_asset)) {
                    DB::table('user_asset')->insert($data);
                } else {
                    DB::table('user_asset')->where('id', $_asset['id'])->update($data);
                }
            }
            return $this->json('恭喜您，操作成功。', true);
        }

        if ($gets['clone_id']) {
            $clone_id = $gets['clone_id'];
        } else {
            $clone_id = $gets['role_id'];
        }

        $assets = UserAssetService::getRoleAssets($clone_id);
        $modules = ModuleService::allWithDetails();

        $roles = Role::orderBy('lft', 'asc')->get()->toNested();

        return $this->display([
            'assets' => $assets,
            'modules' => $modules,
            'query' => $query,
            'roles' => $roles,
        ]);
    }

    public function createAction()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'role', 'id' => $id]);
        return $this->render([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function dialogAction()
    {
        $search = search_form([], [
            ['text','role.name','名称'],
            ['text','role.id','ID'],
        ]);
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = Role::orderBy('lft', 'asc');
            
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            
            $rows = $model->get()->toNested('name');
            $data = [];
            foreach ($rows as $row) {
                $row['sid'] = 'r'.$row['id'];
                $data[] = $row;
            }
            return response()->json(['data' => $data]);
        }
        return $this->render([
            'search' => $search,
        ]);
    }

    /**
     * 角色设置
     */
    public function permissionAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $user_id = $gets['user_id'];
            $rows = $gets['rows'];
            $users = DB::table('user_role')
            ->where('user_id', $user_id)
            ->pluck('id', 'role_id');
            foreach($rows as $row) {
                if (empty($users[$row['id']])) {
                    DB::table('user_role')->insert([
                        'user_id' => $user_id, 
                        'role_id' => $row['id']
                    ]);
                } else {
                    unset($users[$row['id']]);
                }
            }
            foreach($users as $warehouse_id) {
                DB::table('user_role')->where('id', $warehouse_id)->delete();
            }
            return $this->json('角色权限设置成功。', true);
        }
        $rows = DB::table('role')->orderBy('id', 'asc')->get(['id', 'code', 'name']);
        $users = DB::table('user_role')->where('user_id', $gets['user_id'])->pluck('id', 'role_id');
        return $this->render([
            'rows' => $rows,
            'users' => $users,
        ]);
    }

    // 删除角色
    public function deleteAction()
    {
        if (Request::method() == 'POST') {

            $id = Request::get('id');
            $id = array_filter((array)$id);

            if (empty($id)) {
                return $this->json('最少选择一行记录。');
            }

            $has = Role::whereIn('parent_id', $id)->count();
            if ($has) {
                return $this->json('存在子节点不允许删除。');
            }

            // 删除角色
            Role::whereIn('id', $id)->delete();

            // 重构树形结构
            Role::treeRebuild();

            return $this->json('删除成功。', true);
        }
    }
}
