<?php namespace Gdoo\StiReport\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Flow\Form;

class StiReportController extends DefaultController
{
    public $permission = ['viewer', 'designer', 'saveReport', 'license', 'render'];

    public function designerAction()
    {
        $template_id = (int)Request::get('template_id');
        $template = DB::table('model_template')->where('id', $template_id)->first();
        // 报表名称
        $report_name = "{$template['code']}";
        $report_file = '';
        if (is_file(public_path().'/reports/'.$report_name.'.mrt')) {
            $report_file = $report_name;
        }
        return $this->render(['report_name' => $report_name, 'report_file' => $report_file]);
    }

    public function viewerAction()
    {
        return $this->render();
    }

    public function renderAction()
    {
        return $this->render();
    }

    public function saveReportAction()
    {
        $gets = Request::all();
        $fileName = $gets['fileName'];
        file_put_contents(public_path().'/reports/'.$fileName.".mrt", $gets['data']);
        $success = ['success' => true, 'msg' => "保存成功:".$fileName];
        return $success;
    }
}
