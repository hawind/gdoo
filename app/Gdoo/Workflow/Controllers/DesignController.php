<?php namespace Gdoo\Workflow\Controllers;

use DB;
use Request;

use Gdoo\Workflow\Models\Workflow;
use Gdoo\Workflow\Models\WorkflowCategory;

use Gdoo\Index\Controllers\DefaultController;

class DesignController extends DefaultController
{
    public function indexAction()
    {
        $search = search_form([
            'referer' => 1
        ], [
            ['text','work.title','流程名称'],
            ['text','work.id','流程编号'],
            ['category','work.category_id','流程类别'],
        ]);
        $query  = $search['query'];

        $model = Workflow::query();

        foreach ($search['where'] as $where) {
            if ($where['active']) {
                $model->search($where);
            }
        }

        $rows = $model->paginate()->appends($query);

        $counts = DB::table('work_process')
        ->selectRaw('work_id,count(id) as count')
        ->groupBy('work_id')
        ->pluck('count', 'work_id');

        $categorys = WorkflowCategory::get()->keyBy('id');
        return $this->display(array(
            'rows'      => $rows,
            'counts'    => $counts,
            'categorys' => $categorys,
            'search'    => $search,
        ));
    }

    public function addAction()
    {
        $gets = Request::all();
        $model = Workflow::findOrNew($gets['id']);

        if (Request::method() == 'POST') {
            if (empty($gets['title'])) {
                return $this->error('流程名称必须填写。');
            }
            $model->fill($gets)->save();
            return $this->success('index', '工作流程保存成功。');
        }

        $row = Workflow::find($gets['id']);
        $category = WorkflowCategory::get();

        return $this->display(array(
            'row'      => $row,
            'category' => $category,
        ));
    }

    public function processAction()
    {
        $this->view->set(array(
            'row' => $row,
        ));
        return $this->display();
    }

    // 删除流程
    public function deleteAction()
    {
        $id = Request::get('id', 0);
        if ($id > 0) {
            // 此处应该删除所有的有关的文件
            Workflow::where('id', $id)->delete();
            return $this->success('index', '工作流删除成功。');
        }
    }
}
