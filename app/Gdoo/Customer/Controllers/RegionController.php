<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\CustomerRegion;

use Gdoo\Index\Controllers\DefaultController;

class RegionController extends DefaultController
{
    public $permission = ['dialog'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'customer_region',
            'referer' => 1,
            'search' => ['by' => ''],
            'trash_btn' => 0,
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

            $model->orderBy('lft', 'asc')
            ->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select'])
            ->addSelect(DB::raw('parent_id'));
            $rows = $model->get()->toNested('name');

            $users = DB::table('user')->get()->keyBy('id');
            $items = Grid::dataFilters($rows, $header, function($item) use($users) {
                $owner_assist = explode(',', $item['owner_assist']);
                $_rows = [];
                foreach ($owner_assist as $user_id) {
                    $_rows[] = $users[$user_id]['name'];
                }
                $item['owner_assist'] = join(',', $_rows);
                return $item;
            });
            return $this->json($items, true);
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];
        $header['cols'] = $cols;
        $header['tabs'] = CustomerRegion::$tabs;
        $header['bys'] = CustomerRegion::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    // 新建客户联系人
    public function createAction()
    {
        $id = (int)Request::get('id');

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];
        $header['code'] = 'customer_region';
        $header['id'] = $id;
        
        $form = Form::make($header);

        return $this->render([
            'form' => $form,
        ], 'create');
    }

    // 创建客户联系人
    public function editAction()
    {
        return $this->createAction();
    }

    public function dialogAction()
    {
        $search = search_form([], [
            ['text','customer_region.name','名称'],
            ['text','customer_region.id','ID'],
        ]);

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = CustomerRegion::leftJoin('user', 'user.id', '=', 'customer_region.owner_user_id')
            ->orderBy('customer_region.lft', 'asc');

            // 客户圈权限
            $region = regionCustomer();
            if ($region['authorise']) {
                $model->whereIn('customer_region.id', $region['regionIn']);
            }

            if (isset($query['layer'])) {
                $model->where('customer_region.layer', $query['layer']);
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $rows = $model->get([
                'customer_region.*', 
                'customer_region.name as text', 
                'user.name as owner_user_id_name'
            ]);
            $rows = array_nest($rows);

            $json = [];
            foreach($rows as $row) {
                $json[] = $row;
            }
            return response()->json(['data' => $json]);
        }
        return $this->render([
            'search' => $search
        ]);
    }

    // 删除
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_region', 'ids' => $ids]);
        }
    }
}
