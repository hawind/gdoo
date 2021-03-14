<?php namespace Gdoo\Customer\Controllers;

use Auth;
use Request;
use Validator;
use DB;

use Gdoo\Customer\Models\Business;
use Gdoo\User\Models\User;
use Gdoo\Index\Models\Attachment;

use Gdoo\Index\Services\NotificationService;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\AttachmentService;

class BusinessController extends DefaultController
{
    public $permission = ['index','salesman','store'];

    // 商机列表
    public function indexAction()
    {
        // 筛选客户
        $filter = select::customer();
        $columns = [
            ['text','customer_business.name','客户名称'],
        ];
        if ($filter['role_type'] == 'salesman') {
            $columns[] = ['text','customer_business.address','客户地区'];
            $columns[] = ['text','customer_business.type','客户类型'];
        }

        if ($filter['role_type'] == 'all') {
            $columns[] = ['text','user.name','创建者'];
            $columns[] = ['owner','customer_business.user_id','负责人'];
            $columns[] = ['text','customer_business.address','客户地区'];
            $columns[] = ['text','customer_business.type','客户类型'];
        }

        $search = search_form([
            'status' => 1,
            'referer' => 1
        ], $columns);

        $query = $search['query'];
        
        $model = Business::leftJoin('user', 'user.id', '=', 'customer_business.created_id')
        ->select(['customer_business.*']);

        $level = authorise();
        if ($level < 4) {
            $model->where('customer_business.user_id', Auth::id());
        }

        foreach ($search['where'] as $where) {
            if ($where['active']) {
                $model->search($where);
            }
        }

        if ($query['order'] && $query['srot']) {
            $model->orderBy($query['srot'], $query['order']);
        } else {
            $model->orderBy('customer_business.id', 'desc');
        }

        $rows = $model->paginate($query['limit']);
 
        if (Request::wantsJson()) {
            return $rows->toJson();
        }

        $rows = $rows->appends($query);

        return $this->display(array(
            'rows' => $rows,
            'search' => $search,
        ));
    }

    // 客户资料查看
    public function showAction()
    {
        $id = (int)Request::get('id');
        $row = DB::table('customer_business')
        ->leftJoin('user', 'user.id', '=', 'customer_business.user_id')
        ->where('customer_business.id', $id)
        ->first(['customer_business.*','user.name']);

        // 返回json
        $row['address'] = str_replace("\n", " ", $row['address']);
        $attachments = AttachmentService::show($row['attachment']);
        $row['attachments'] = $attachments['main'];
        return $row;
    }

    // 负责人列表
    public function salesmanAction()
    {
        if (Request::wantsJson()) {
            $users = User::leftJoin('role', 'role.id', '=', 'user.role_id')
            ->where('role.name', 'salesman')
            ->where('user.status', 1)
            ->get(['user.id', 'user.username', 'user.name']);
            return $this->json($users);
        }
    }

    // 储存商机
    public function storeAction()
    {
        if (Request::isJson()) {
            $gets = json_decode(Request::getContent(), true);
        } else {
            $gets = Request::all();
        }

        $row = new Business;

        $rules = [
            'source'  => 'required',
            'user_id' => 'required',
            'name'    => 'required',
            // 'attachment' => 'min:1|array|required',
        ];
        
        $v = Validator::make($gets, $rules, Business::$_messages);
        if ($v->fails()) {
            return $this->json($v->errors());
        }

        // 地区
        if (is_array($gets['address'])) {
            $gets['address'] = join("\n", $gets['address']);
        }

        // 保存base64图片数据
        // $gets['attachment'] = Attachment::base64($gets['attachment'], 'customer');

        if (is_array($gets['attachment'])) {
            $gets['attachment'] = AttachmentService::base64($gets['attachment'], 'customer');
        } else {
            $gets['attachment'] = AttachmentService::files('image', 'customer');
        }

        $row->fill($gets)->save();

        $user = User::find($gets['user_id']);

        return $this->json('恭喜你，操作成功。', true);
    }

    // 删除商机
    public function destroyAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            $rows = Business::whereIn('id', $id)->get();
            if ($rows) {
                foreach ($rows as $row) {
                    AttachmentService::remove($row->attachment);
                    $row->delete();
                }
            }
            return $this->success('index', '恭喜你，删除成功。');
        }
    }
}
