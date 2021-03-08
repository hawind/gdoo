<?php namespace Gdoo\Approach\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Approach\Models\ApproachMarket;
use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\DefaultController;

class MarketController extends DefaultController
{
    public $permission = ['dialog'];

    public function indexAction()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'approach_market',
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
            $model->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = ApproachMarket::$tabs;
        $header['bys'] = ApproachMarket::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'approach_market';
        $header['id'] = $id;

        $form = Form::make($header);
        return $this->render([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function auditAction()
    {
        return $this->createAction('audit');
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'approach_market', 'ids' => $ids]);
        }
    }

    // 对话框
    public function dialogAction()
    {
        $header = Grid::header([
            'code' => 'approach_market',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table('approach_market');
            
            if ($query['sort'] && $query['order']) {
                $model->orderBy($query['sort'], $query['order']);
            }

            if (isset($query['customer_id'])) {
                $model->where('approach_market.customer_id', $query['customer_id']);
            }

            if ($query['q']) {
                $model->where('approach_market.name', 'like', '%'.$query['q'].'%');
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $sql = 'name as text,code,name,customer_id,market_count,type_id,single_cast,total_cast,fax,market_address,market_area,market_person_name,market_person_phone,status';

            if ($query['related'] == '0') {
                $sql = $sql.',name as id';
            } else {
                $sql = $sql.',id as id';
            }
            $model->selectRaw($sql);
            $rows = $model->paginate();

            $items = Grid::dataFilters($rows, $header);

            return response()->json($items);
        }
    }
}