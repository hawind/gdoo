<?php namespace Gdoo\Model\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;

use Gdoo\Index\Controllers\DefaultController;

class TodoController extends DefaultController
{
    public $permission = ['widget'];

    /**
     * 流程待办
     */
    public function widget(Request $request)
    {
        if ($request->method() == 'POST') {
            $bill_id = $request->get('bill_id');
            $model = DB::table('model_run_log')
            ->leftJoin('model_run', 'model_run.id', '=', 'model_run_log.run_id')
            ->leftJoin('model_bill', 'model_bill.id', '=', 'model_run.bill_id')
            ->leftJoin('user as run_log_user', 'run_log_user.id', '=', 'model_run_log.created_id')
            ->leftJoin('customer', 'customer.id', '=', DB::raw("model_run.partner_id and model_run.partner_type = 'customer'"))
            ->leftJoin('supplier', 'supplier.id', '=', DB::raw("model_run.partner_id and model_run.partner_type = 'supplier'"))
            ->where('model_run_log.updated_id', 0)
            ->where('model_run_log.user_id', auth()->id())
            ->orderBy('model_run.id', 'desc')
            ->selectRaw("
                model_run_log.*,
                model_run.name,
                model_run.sn,
                model_run.remark,
                model_run.bill_id,
                model_run_log.[option],
                run_log_user.name as user_name,
                model_run_log.created_at,
                model_run.data_id,
                model_bill.uri as bill_uri,
                CASE WHEN partner_type = 'customer' THEN customer.name ELSE supplier.name END AS partner_name
            ");

            if ($bill_id > 0) {
                $model->where('model_run.bill_id', $bill_id);
            }
            
            $rows = $model->get();

            $rows->transform(function ($row) {
                $row['url'] = $row['bill_uri'].'/show';
                return $row;
            });
            return $this->json($rows, true);
        }

        $selects = DB::table('model_bill')->where('audit_type', 1)->get();
        return $this->render([
            'selects' => $selects,
        ]);
    }
}
