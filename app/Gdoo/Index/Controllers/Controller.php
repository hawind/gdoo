<?php namespace Gdoo\Index\Controllers;

use Session;
use View;
use URL;
use Request;

use Gdoo\Index\Services\RetService;
use Gdoo\System\Models\Setting;

use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var 程序版本
     */
    public $version = '2.5.2';

    /**
     * @var 配置参数
     */
    public $setting = [];

    /**
     * @var 跳过acl检查的方法
     */
    public $permission = [];

    /**
     * @var 当前控制下的方法权限
     */
    public $access = [];
   
    /**
     * @var layout 布局视图模板
     */
    protected $layout = 'layouts.default';

    /**
     * @var 数据库类型
     */
    public $dbType = 'sqlsrv';

    /**
     * 初始化ret数据
     */
    public $ret = null;

    /**
     * @var 执行初始化工作
     */
    public function __construct()
    {
        // 不丢失表单返回数据
        header('Cache-control:private, must-revalidate');

        // 获取配置数据
        $this->setting = Setting::where('type', 'system')->pluck('value', 'key');

        $this->dbType = env('DB_CONNECTION');

        $this->ret = RetService::make();

        View::share([
            'version' => $this->version,
            'setting' => $this->setting,
            'public_url' => URL::to('/'),
            'upload_url' => URL::to('/uploads'),
            'static_url' => URL::to('/static'),
            'asset_url' => URL::to('/assets'),
        ]);
    }

    /**
     * Ajax调用返回
     *
     * 返回json数据, 供前台ajax调用
     * @param array   $data     返回数组,支持数组
     * @param boolean $status   执行状态, 1为true, 0为false
     * @param string  $type     返回信息类型, 默认为primary
     * @return string
     */
    public function json($data, $status = false)
    {
        return response_json($data, $status);
    }

    /**
     * 返回页面
     */
    public function back($message = null, $type = 'message')
    {
        $args = func_num_args();
        if ($args == 0) {
            return redirect()->back();
        }
        return redirect()->back()->with($type, $message);
    }
    
    // 操作错误返回
    public function error($error = null, $type = 'error')
    {
        $args = func_num_args();
        if ($args == 0) {
            return redirect()->back();
        }
        return redirect()->back()->with($type, $error);
    }

    // 操作成功跳转
    public function success($path, $params = [], $message = null, $referer = 1)
    {
        $args = func_num_args();
        if ($args > 2) {
            return $this->to($path, $params, $referer)->with('message', $message);
        } else {
            return $this->to($path, [], $referer)->with('message', $params);
        }
    }

    /**
     * 刷新页面附带 referer
     */
    public function to($path = null, $params = [], $referer = 1)
    {
        return redirect(url_referer($path, $params, $referer));
    }
    
    /**
     * 模板文件名
     */
    public function viewFile($file)
    {
        if ($file === null) {
            $file = Request::controller().'.'.Request::action();
        } else {
            if (substr_count($file, '.') === 0) {
                $file = Request::controller().'.'.$file;
            }
        }
        return $file;
    }

    /**
     * 直接渲染模板不包含layout视图
     */
    public function render($params = [], $file = null)
    {
        return view($this->viewFile($file), $params);
    }

    /**
     * 渲染模板嵌套到layout视图
     */
    public function display($params = [], $file = null, $layout = '')
    {
        $layout = $layout == '' ? $this->layout : $layout;
        $layout = view($layout, $params);
        return $layout->nest('content', $this->viewFile($file), $params);
    }
}
