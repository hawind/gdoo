<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\System\Models\Region;

use Gdoo\Index\Controllers\DefaultController;

class RegionController extends DefaultController
{
    public $permission = ['dialog', 'category'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'region',
            'referer' => 1,
            'search' => ['by' => '', 'parent_id' => 0],
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

            $model->where('region.parent_id', $query['parent_id']);

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

        $header['cols'] = $cols;
        $header['tabs'] = Region::$tabs;
        $header['bys'] = Region::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'region', 'id' => $id]);
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
            ['text','region.name','名称'],
            ['text','region.id','ID'],
        ]);

        if (Request::method() == 'POST') {
            $model = Region::orderBy('id', 'asc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $rows = $model->get(['*', 'name as text']);
            $rows = array_nest($rows);

            $json = [];
            foreach($rows as $row) {
                $row['text'] = $row['layer_space'].$row['name'];
                $json[] = $row;
            }
            return ['data' => $json];
        }
        return $this->render([
            'search' => $search,
            'query' => $search['query']
        ]);
    }

    public function categoryAction()
    {
        if (Request::method() == 'POST') {
            $model = Region::orderBy('id', 'asc');
            $rows = $model->get(['*', 'name as text']);
            array_nest($rows);
            return ['data' => $rows];
        }
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            $id = array_filter((array)$id);

            if (empty($id)) {
                return $this->json('最少选择一行记录。');
            }

            $types = Region::whereIn('id', $id)->get();
            foreach ($types as $type) {
                // 删除数据
                $type->delete();
            }
            return $this->json('恭喜你，操作成功。', url_referer('index'));
        }
    }
}
