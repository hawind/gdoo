<?php namespace Gdoo\Produce\Controllers;

use DB;
use Request;
use Auth;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\User\Models\User;
use Gdoo\Produce\Models\Material;
use Gdoo\Produce\Models\Formula;

use Gdoo\Produce\Services\ProduceService;

use Gdoo\Index\Controllers\DefaultController;

class MaterialController extends DefaultController
{
    public $permission = ['dialog', 'config', 'configSave', 'planProduct', 'planTotal'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'product_material',
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

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '配料', 'color' => 'default', 'icon' => 'fa-file-text-o', 'action' => 'config', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Material::$tabs;
        $header['bys'] = Material::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction($action = 'edit')
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'product_material', 'id' => $id, 'action' => $action]);
        return $this->render([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction('edit');
    }

    // 配方
    public function configAction()
    {
        $id = (int)Request::get('id');
        if (Request::method() == 'POST') {
            $rows = DB::table('product_formula')->where('material_id', $id)
            ->leftJoin('product', 'product.id', '=', 'product_formula.product_id')
            ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id')
            ->get([
                'product.name as product_name',
                'product.code as product_code', 
                'product.spec as product_spec', 
                'product_unit.name as product_unit',
                'product_formula.*'
            ]);
            return $this->json($rows, true);
        }
        return $this->display(['id' => $id]);
    }

    // 用料计划
    public function planAction()
    {
        $search = search_form([
            'advanced' => 0,
        ], [
            ['form_type' => 'date', 'name' => '计划时间', 'field' => 'date', 'options' => []],
            ['form_type' => 'dialog', 'name' => '生产车间', 'field' => 'department_id', 'options' => [
                'url' => 'user/department/dialog',
            ]],
            ['form_type' => 'select', 'name' => '重算计算', 'field' => 'is_recalc', 'options' => [
                ['id' => 1, 'name' => '是'],
                ['id' => 0, 'name' => '否']
            ]],
        ], 'model');
        
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $date = $query['search_0'];
            $department_id = $query['search_1'];
            $is_recalc = $query['search_2'];
            $rows = [];
            if ($date && $department_id) {
                $rows = ProduceService::getMaterialPlanDay($date, $department_id, $is_recalc);
                $rowspan = $rowspan2 = [];
                foreach($rows as $row) {
                    $rowspan[$row['product_id']] ++;
                    $rowspan2[$row['product_id'].'_'.$row['category_name']] ++;
                }
                foreach($rows as &$row) {
                    $a = $rowspan[$row['product_id']];
                    $b = $rowspan2[$row['product_id'].'_'.$row['category_name']];
                    if ($a) {
                        $row['rowspan'] = $rowspan[$row['product_id']];
                        $row['rowspan_end'] = count($rowspan) == 1 ? 1 : 0;
                        unset($rowspan[$row['product_id']]);
                    }
                    if ($b) {
                        $row['rowspan2_end'] = count($rowspan2) == 1 ? 0 : 0;
                        $row['rowspan2'] = $rowspan2[$row['product_id'].'_'.$row['category_name']];
                        unset($rowspan2[$row['product_id'].'_'.$row['category_name']]);
                    }

                }
            }
            return $this->json($rows, true);
        }

        $search['table'] = 'material_plan';
        return $this->display([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 用料计划产品
    public function planProductAction()
    {
        $search = search_form([], [], 'model');
        $query = $search['query'];
        if (Request::method() == 'POST') {
            $date = $query['date'];
            $department_id = (int)$query['department_id'];
            $product_id = (int)$query['product_id'];

            $rows = [];
            if ($date) {
                $sql = "select a.date,
                a.code,
                a.dept_id,
                a.product_id,
                b.name as product_name,
                b.spec as product_spec,
                c.name as product_unit,
                a.product_num,
                a.material_id,
                d.category as category_name,
                d.name as material_name,
                a.material_num,
                a.total_num
                ,a.creator_id,
                a.creator_name,
                a.create_date,
                a.remark
                from material_plan_day a 
                left join product b on a.product_id = b.id
                left join product_unit AS c ON b.unit_id = c.id
                left join product_material d on a.Material_Id = d.Id
                where a.date = '".$date."' and a.Dept_Id = $department_id and a.Product_Id = $product_id";
                $rows = DB::select($sql);
            }
            return $this->json($rows, true);
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 用料计划总量
    public function planTotalAction()
    {
        $search = search_form([], [], 'model');
        $query = $search['query'];
        if (Request::method() == 'POST') {

            $date = $query['date'];
            $department_id = (int)$query['department_id'];

            $rows = [];
            if ($date) {
                $sql = "select a.date,
                a.dept_id,
                a.material_id,
                d.category as category_name,
                d.name as material_name,
                sum(a.material_num) material_num,
                sum(a.total_num)total_num
                from material_plan_day a
                left join product b on a.product_id = b.id
                left join product_unit AS c ON b.unit_id = c.id
                left join product_material d on a.Material_Id = d.Id
                where a.date = '$date' and a.Dept_Id = ".$department_id."
                group by a.Date, a.Dept_Id, a.Material_Id, d.category, d.Name";
                $rows = DB::select($sql);
            }
            return $this->json($rows, true);
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 配方保存
    public function configSaveAction()
    {
        $gets = Request::all();
        $id = $gets['id'];
        if (empty($id)) {
            return $this->json('原辅料编号不能为空');
        }

        $data = $gets['product_formula'];
  
        // 新增或者修改
        foreach((array)$data['rows'] as $_row) {
            $_row['material_id'] = $id;
            $row = Formula::findOrNew($_row['id']);
            $row->fill($_row)->save();
        }

        // 删除记录
        foreach((array)$data['deleteds'] as $row) {
            if ($row['id'] > 0) {
                Formula::where('id', $row['id'])->delete();
            }
        }
        return $this->json('配方保存成功。', true);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'product_material', 'ids' => $ids]);
        }
    }

    public function dialogAction()
    {
        $search = search_form([
            'advanced' => '',
            'prefix' => '',
            'offset' => '',
            'sort' => '',
            'order' => '',
            'limit' => '',
        ], [
            ['text','logistics.name','名称'],
        ]);
        $query  = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table('logistics');
            // 排序方式
            if ($query['sort'] && $query['order']) {
                $model->orderBy('logistics.'.$query['sort'], $query['order']);
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->selectRaw("logistics.*");

            if ($query['limit']) {
                $rows = $model->paginate($query['limit']);
            } else {
                $rows['total'] = $model->count();
                $rows['data']  = $model->get();
            }
            return response()->json($rows);
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }
}
