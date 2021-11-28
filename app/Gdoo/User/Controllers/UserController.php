<?php namespace Gdoo\User\Controllers;

use DB;
use Auth;
use Hash;
use Request;
use Validator;

use App\Support\Totp;
use App\Support\Pinyin;
use App\Support\License;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\User\Models\User;

use Gdoo\Index\Controllers\DefaultController;

class UserController extends DefaultController
{
    public $permission = ['dialog'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'user',
            'referer' => 1,
            'search' => ['by' => 'enabled', 'tab' => 'user'],
        ]);

        $cols = $header['cols'];

        if (auth()->id() == 1) {
            $cols = Grid::addColumns($cols, 'id', [[
                'headerName' => '密码',
                'field' => 'password_text',
                'width' => 100,
                'cellStyle' => ['text-align' => 'center'],
            ]]);
        }

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ],[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '角色权限', 'color' => 'default', 'action' => 'user_role', 'display' => 1],
            ['name' => '仓库权限', 'color' => 'default', 'action' => 'user_warehouse', 'display' => 1],
        ];

        $header['right_buttons'] = [
            ['name' => '导入', 'color' => 'default', 'icon' => 'fa-mail-reply', 'action' => 'import', 'display' => $this->access['import']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = User::$tabs;
        $header['bys'] = User::$bys;

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order'])
            ->where('user.group_id', 1);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $header['select'][] = 'user.password_text';
            $model->select($header['select']);

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function show()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'user', 'id' => $id, 'action' => 'show']);
        $user = $form['row'];

        $secret_qrcode = Totp::getURL($user['username'], Request::server('HTTP_HOST'), $user['auth_secret'], $user['name']);
        return $this->display([
            'secret_qrcode' => $secret_qrcode,
            'form' => $form,
        ], 'create');
    }

    public function create()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'user', 'id' => $id]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function edit()
    {
        return $this->create();
    }

    public function dialog()
    {
        $group_id = Request::get('group_id', 1);
        $header = Grid::header([
            'code' => 'user',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];
        if (Request::method() == 'POST') {
            $model = DB::table('user');
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->where('user.status', 1)
            ->where('user.group_id', (int)$group_id);

            // 排序方式
            if ($query['sort'] && $query['order']) {
                $model->orderBy($query['sort'], $query['order']);
            }

            // 搜索条件
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $model->selectRaw("
                [user].id, 
                concat('u', [user].id) as sid,
                [user].role_id,
                [user].status,
                [user].username,
                [user].name,
                [user].name as text,
                [user].email,
                [user].phone
            ");
            return $model->paginate($query['limit']);
        }
        return $this->render(array(
            'search' => $search,
            'query' => $query,
        ));
    }

    // 数据导入
    public function import()
    {
        if (Request::method() == 'POST') {
            return Form::import([
                'table' => 'user', 
                'keys' => ['username'],
                'defaults' => ['group_id' => 1],
            ]);
        }
        $tips = '注意：表格里必须包含[用户名]列。';
        return $this->render(['tips' => $tips], 'layouts.import');
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'user', 'ids' => $ids]);
        }
    }
}
