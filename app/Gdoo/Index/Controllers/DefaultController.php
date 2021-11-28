<?php namespace Gdoo\Index\Controllers;

use Gdoo\User\Services\UserAssetService;
use Gdoo\Index\Models\Menu;
use View;

use DB;
use Validator;
use Request;

use App\Support\AES;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Form;

use Gdoo\Model\Services\ModelService;
use Gdoo\Index\Services\MenuService;

class DefaultController extends Controller
{
    protected $user = [];

    public function __construct()
    {
        $this->permission[] = 'store';

        parent::__construct();

        UserAssetService::setPermissions($this->permission);

        // 登录认证和RBAC检查
        $this->middleware('auth');

        // 获取登录认证数据
        $this->middleware(function ($request, $next) {
            $this->user = $request->user();

            $this->access = UserAssetService::getNowRoleAssets();
            $menus = MenuService::getItems();
            View::share([
                'menus' => $menus,
                'access' => $this->access,
            ]);
            return $next($request);
        });
    }

    /**
     * 保存表单
     */
    public function store()
    {
        $gets = Request::all();
        $master = $gets['master'];

        $keys = AES::decrypt($master['key'], config('app.key'));
        list($bill_id, $id) = explode('.', $keys);
        $bill = Bill::find($bill_id);
        $models = ModelService::getModels($bill->model_id);
        $model = $models[0];

        if (Request::method() == 'POST') {
            // 检查表单
            $valid = Form::flowRules($models, $gets);

            if ($valid['rules']) {
                $v = Validator::make($gets, $valid['rules'], $valid['messages'], $valid['attributes']);
                if ($v->fails()) {
                    $errors = $v->errors()->all();
                    return $this->json(join('<br>', $errors));
                }
            }
            // 保存数据
            $id = Form::store($bill, $models, $gets, $id, 'store');
            
            // 保存草稿跳转到编辑界面
            $url = url($master['uri'].'/show', ['id' => $id, 'client' => $master['client']]);
            return $this->json($bill['name'].'保存成功', $url);
        }
    }

    /**
     * 关闭行数据
     */
    public function closeRow()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $id = $gets['id'];
            if (strpos($id, 'draft_') === 0) {
                return $this->json('关闭行数据成功', true);
            }
            $row = DB::table($gets['table'])->where('id', $id)->first();
            if ($row['use_close'] == 1) {
                $use_close = 0;
            } else {
                $use_close = 1;
            }
            DB::table($gets['table'])->where('id', $id)->update(['use_close' => $use_close]);
            return $this->json('关闭行数据成功', true);
        }
    }

    /**
     * 关闭所有行数据
     */
    public function closeAllRow()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = [];
            foreach($gets['ids'] as $id) {
                if (strpos($id, 'draft_') === 0) {
                    continue;
                }
                $ids[] = $id;
            }
            $rows = DB::table($gets['table'])->whereIn('id', $ids)->get();
            if ($rows[0]['use_close'] == 1) {
                $use_close = 0;
            } else {
                $use_close = 1;
            }
            DB::table($gets['table'])->whereIn('id', $ids)->update(['use_close' => $use_close]);
            return $this->json('关闭所有行数据成功', true);
        }
    }

}
