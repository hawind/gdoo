<?php namespace Gdoo\Index\Controllers;

use Gdoo\User\Models\UserAsset;
use Gdoo\Index\Models\Menu;
use View;

use Validator;
use DB;
use Request;

use App\Support\AES;
use App\Support\Hook;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Model;

class AuditController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 审核
     */
    public function auditAction()
    {
        if (Request::method() == 'POST') {
            $key = Request::get('key');
            $keys = AES::decrypt($key, config('app.key'));
            list($bill_id, $data_id) = explode('.', $keys);
            $bill = Bill::find($bill_id);
            $model = Model::find($bill->model_id);
            DB::beginTransaction();
            try {
                Hook::fire($model->table.'.onBeforeAudit', ['table' => $model->table, 'id' => $data_id]);
                DB::table($model->table)->where('id', $data_id)->update(['status' => 1]);
                DB::commit();
                return $this->json($bill->name.'审核成功', true);
            } catch(\Exception $e) {
                DB::rollBack();
                return $this->json($e->getMessage());
            }
        }
    }

    /**
     * 弃审
     */
    public function abortAction()
    {
        if (Request::method() == 'POST') {
            $key = Request::get('key');
            $keys = AES::decrypt($key, config('app.key'));
            list($bill_id, $data_id) = explode('.', $keys);
            $bill = Bill::find($bill_id);
            $model = Model::find($bill->model_id);
            DB::beginTransaction();
            try {
                Hook::fire($model->table.'.onBeforeAbort', ['table' => $model->table, 'id' => $data_id]);
                DB::table($model->table)->where('id', $data_id)->update(['status' => 0]);
                DB::commit();
                return $this->json($bill->name.'弃审成功', true);
            } catch(\Exception $e) {
                DB::rollBack();
                return $this->json($e->getMessage());
            }
        }
    }
}
