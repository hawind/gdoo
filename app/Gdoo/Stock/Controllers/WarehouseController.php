<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Stock\Models\Warehouse;

use Gdoo\Index\Controllers\DefaultController;

class WarehouseController extends DefaultController
{
    public $permission = ['dialog', 'permission'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'warehouse',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name'    => '编辑',
            'action'  => 'edit',
            'display' => $this->access['edit'],
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order'])
            ->orderBy('id', 'desc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            $items = Grid::dataFilters($rows, $header);
            return $items->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];
        $header['cols'] = $cols;
        $header['tabs'] = Warehouse::$tabs;
        $header['bys'] = Warehouse::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'warehouse', 'id' => $id]);
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
        $header = Grid::header([
            'code' => 'warehouse',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table']);
            $model->leftJoin('user_warehouse', 'user_warehouse.warehouse_id', '=', 'warehouse.id')
            ->where('user_warehouse.user_id', auth()->id());

            $model->where('warehouse.status', 1)
            ->orderBy('warehouse.id', 'asc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            
            $model->select($header['select']);

            $rows = $model->paginate($query['limit']);

            $_locations = DB::table('warehouse_location')->get();
            $locations = [];
            foreach ($_locations as $_location) {
                $locations[$_location['warehouse_id']][] = $_location;
            }

            $items = Grid::dataFilters($rows, $header, function($item) use($locations) {
                $item['pos'] = (array)$locations[$item['id']];
                return $item;
            });
            return response()->json($items);
        }

        return $this->render([
            'search' => $search,
        ], 'dialog');
    }

    /**
     * 权限设置
     */
    public function permissionAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $user_id = $gets['user_id'];
            $rows = $gets['rows'];
            $users = DB::table('user_warehouse')
            ->where('user_id', $user_id)
            ->pluck('id', 'warehouse_id');
            foreach($rows as $row) {
                if (empty($users[$row['id']])) {
                    DB::table('user_warehouse')->insert([
                        'user_id' => $user_id, 
                        'warehouse_id' => $row['id']
                    ]);
                } else {
                    unset($users[$row['id']]);
                }
            }
            foreach($users as $warehouse_id) {
                DB::table('user_warehouse')->where('id', $warehouse_id)->delete();
            }
            return $this->json('仓库权限设置成功。', true);
        }
        $rows = DB::table('warehouse')->orderBy('id', 'asc')->get(['id', 'code', 'name']);
        $users = DB::table('user_warehouse')->where('user_id', $gets['user_id'])->pluck('id', 'warehouse_id');
        return $this->render([
            'rows' => $rows,
            'users' => $users,
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'warehouse', 'ids' => $ids]);
        }
    }
}
