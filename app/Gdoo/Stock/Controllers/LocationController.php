<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Stock\Models\WarehouseLocation;

use Gdoo\Index\Controllers\DefaultController;

class LocationController extends DefaultController
{
    public $permission = ['dialog', 'dialog2', 'serviceWarehouse'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'warehouse_location',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
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
            $model->orderBy($header['sort'], $header['order'])
            ->orderBy('id', 'desc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        $header['buttons'] = [
            //['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];
        $header['cols'] = $cols;
        $header['tabs'] = WarehouseLocation::$tabs;
        $header['bys'] = WarehouseLocation::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction($action = 'edit')
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'warehouse_location', 'id' => $id, 'action' => $action]);
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
            'code' => 'warehouse_location',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table']);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            $model->where('warehouse_location.warehouse_id', $query['warehouse_id'])
            ->orderBy('warehouse_location.sort', 'asc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $model->select($header['select']);

            $rows = $model->paginate($query['limit']);
            $items = Grid::dataFilters($rows, $header, function($item) {
                return $item;
            });
            return response()->json($items);
        }

        return $this->render([
            'search' => $search,
        ], 'dialog');
    }

    /**
     * 获取现存量
     */
    public function dialog2Action()
    {
        $header = Grid::header([
            'code' => 'warehouse_location',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table']);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->where('warehouse_location.status', 1)
            ->orderBy('warehouse_location.sort', 'asc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            
            $model->select($header['select']);

            $rows = $model->paginate($query['limit']);
            $items = Grid::dataFilters($rows, $header, function($item) {
                return $item;
            });
            return response()->json($items);
        }

        return $this->render([
            'search' => $search,
        ], 'dialog2');
    }

    /**
     * 获取仓库货位
     */
    public function serviceWarehouseAction()
    {
        $warehouse_id = Request::get('warehouse_id');
        if (Request::method() == 'POST') {
            $model = DB::table('warehouse_location')
            ->where('warehouse_location.warehouse_id', $warehouse_id)
            ->where('warehouse_location.status', 1)
            ->orderBy('warehouse_location.sort', 'asc');
            return response()->json($model->get());
        }
    }
    
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'warehouse_location', 'ids' => $ids]);
        }
    }
}
