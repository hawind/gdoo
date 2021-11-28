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
    public $permission = ['planProduct', 'planTotal'];

    // 用料计划
    public function plan()
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
    public function planProduct()
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
                dc.name as category_name,
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
                left join product d on a.material_id = d.id
                left join product_category dc on dc.id = d.category_id
                where a.date = '".$date."' and a.dept_id = $department_id and a.product_id = $product_id";
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
    public function planTotal()
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
                dc.name as category_name,
                d.name as material_name,
                sum(a.material_num) material_num,
                sum(a.total_num) total_num
                from material_plan_day a
                left join product b on a.product_id = b.id
                left join product_unit AS c ON b.unit_id = c.id
                left join product d on a.material_id = d.id
                left join product_category dc on dc.id = d.category_id
                where a.date = '$date' and a.dept_id = ".$department_id."
                group by a.date, a.dept_id, a.material_id, dc.name, d.name";
                $rows = DB::select($sql);
            }
            return $this->json($rows, true);
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }
}
