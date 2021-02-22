<?php namespace Gdoo\Workflow\Controllers;

use Request;
use DB;

use Gdoo\Workflow\Models\Workflow;

use Gdoo\Index\Controllers\DefaultController;

class FormController extends DefaultController
{
    public $permission = ['view'];

    // 表单设计
    public function indexAction()
    {
        if ($post = $this->post()) {
            unset($post['count_item']);

            $work_id = $post['work_id'];

            unset($post['work_id']);

            $post['template'] = $_POST['template'];
            DB::table('work')->where('id', $work_id)->update($post);
            Workflow::cacheForm($work_id);
            return $this->json('流程节点添加成功', true);
        }

        $workId = Request::get('work_id');
        $row = DB::table('work')->where('id', $workId)->first();

        return $this->display([
            'row' => $row,
        ]);
    }

    // 查看步骤信息
    public function viewAction()
    {
        $review = Request::get('review');
        $workId = Request::get('id');

        $work = DB::table('work as w')
        ->LeftJoin('work_step as s', 'w.id', '=', 's.work_id')
        ->where('s.number', 1)
        ->where('w.id', $workId)
        ->first(['w.template_short','w.title as work_title','w.id','s.work_id','w.type as work_type','s.field as field_write','s.field_check','s.field_secret','s.field_auto']);

        $work['opflag'] = 1;
        // $work['printflag'] = 1;
        $work['step_id'] = $stepId;
        $work['id'] = $work['work_id'];

        $workFlow = array(
            'workId'     => (int)$work['work_id'],
            'stepNumber' => 1,
            'stepId'     => $work['step_id'],
            'workType'   => $work['work_type']
        );

        $form = Workflow::parseForm($work['template_short'], $work);

        $work['js'] = json_encode($workFlow);

        $views = [
            'work'     => $work,
            'template' => $form['template'],
            'jsonload' => $form['jsonload'],
            'js'       => $form['js'],
        ];

        if ($review == true) {
            return $this->render($views, 'review');
        } else {
            return $this->display($views, 'view');
        }
    }

    // 表单计数
    public function countAction()
    {
        $workId = Request::get('work_id');
        if ($workId > 0) {
            DB::table('work')->where('id', $workId)->increment('count');
            $row = DB::table('work')->where('id', $workId)->first();
            return $row['count'];
        }
    }
}
