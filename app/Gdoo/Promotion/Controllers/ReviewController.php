<?php namespace Gdoo\Promotion\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Promotion\Models\PromotionReview;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\WorkflowController;

class ReviewController extends WorkflowController
{
    public $permission = ['dialog', 'reference', 'useCount', 'feeDetail'];

    public function indexAction()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'promotion_review',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
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
            $items = Grid::dataFilters($rows, $header);
            return $items->toJson();
        }

        $header['buttons'] = [
            //['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = PromotionReview::$tabs;
        $header['bys'] = PromotionReview::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    // 新建促销
    public function createAction($action = 'edit')
    {
        $id = (int)Request::get('id');

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['action'] = $action;
        $header['code'] = 'promotion_review';
        $header['id'] = $id;
 
        $header['joint'] = [
            ['name' => '申请单', 'action' => 'apply', 'field' => 'apply_id'],
            ['name' => '兑现明细', 'action' => 'cash_detail', 'field' => 'apply_id'],
        ];
        
        $form = Form::make($header);
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display([
            'form' => $form,
        ], $tpl);
    }

    // 编辑促销
    public function editAction()
    {
        return $this->createAction();
    }

    // 审核促销
    public function auditAction()
    {
        return $this->createAction('audit');
    }

    // 显示促销
    public function showAction()
    {
        return $this->createAction('show');
    }

    // 显示促销
    public function printAction()
    {
        $this->layout = 'layouts.print2';
        print_prince($this->createAction('print'));
    }

    // 批量编辑
    public function batchEditAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('promotion_review')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'promotion_review',
            'columns' => ['customer_id', 'tax_id'],
        ]);
        return view('batchEdit', [
            'gets' => $gets,
            'header' => $header
        ]);
    }

    // 兑现明细
    public function feeDetailAction()
    {
        $query = Request::all();
        if (Request::method() == 'POST') {
            $rows = DB::table('promotion_review')->where('apply_id', $query['id'])->orderBy('id', 'desc')->get();
            return $this->json($rows, true);
        }
        return $this->render(['query' => $query]);
    }

    // 删除促销
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'promotion_review', 'ids' => $ids]);
        }
    }
}
