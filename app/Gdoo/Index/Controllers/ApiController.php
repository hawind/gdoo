<?php namespace Gdoo\Index\Controllers;

use DB;
use URL;
use Request;

use App\Support\Pinyin;

use Gdoo\User\Models\User;
use Gdoo\Index\Models\Notification;
use Cron\CronExpression;

class ApiController extends Controller
{
    /**
     * 初始化JS输出
     */
    public function common()
    {
        $settings['public_url'] = URL::to('/');
        $settings['upload_file_type'] = $this->setting['upload_type'];
        $settings['upload_max_size'] = $this->setting['upload_max'];
        $settings['realtime'] = not_empty(env('REALTIME_KEY'));
        
        header('Content-type: text/javascript');
        $e[] = 'var settings = '. json_encode($settings, JSON_UNESCAPED_UNICODE);
        $e[] = env("AGGRID_LICENSE");
        echo join(";\n", $e).';';
        exit;
    }

    /**
     * 任务调用
     */
    public function task()
    {
        $rows = DB::table('cron')->where('status', 1)->get();
        if ($rows) {
            foreach ($rows as $row) {
                $cron = new \Cron\CronExpression($row['expression']);

                // 由于定时任务无法定义秒这里特殊处理一下
                if (strtotime($row['next_run']) <= time()) {
                    // 这里执行代码
                    // 记录下次执行和本次执行结果
                    $next = $cron->getNextRunDate()->format('Y-m-d H:i:00');
                    $data = [
                        'next_run' => $next,
                        'last_run' => '执行成功。'
                    ];
                    DB::table('cron')->where('id', $row['id'])->update($data);
                }
            }
        }
    }

    /**
     * 获取单据编号
     */
    public function billSeqNo()
    {
        $bill_id = Request::get('bill_id');
        $date = Request::get('date');
        $bill = DB::table('model_bill')->where('id', $bill_id)->first();
        $model = DB::table('model')->where('id', $bill['model_id'])->first();
        $make_sn = make_sn([
            'table' => $model['table'],
            'date' => $date,
            'bill_id' => $bill['id'],
            'prefix' => $bill['sn_prefix'],
            'rule' => $bill['sn_rule'],
            'length' => $bill['sn_length'],
        ]);
        return $this->json($make_sn['new_value'], true);
    }

    /**
     * 汉字转拼音
     */
    public function pinyin()
    {
        $word = Request::get('name');
        $type = Request::get('type');

        if (empty($word)) {
            return '';
        }

        if ($type == 'first') {
            return str_replace('/', '', Pinyin::output(str_replace(' ', '', $word)));
        } else {
            return str_replace('/', '', Pinyin::getstr(str_replace(' ', '', $word)));
        }
    }

    /**
     * 显示位置信息
     */
    public function location()
    {
        $gets = Request::all();
        return $this->render(array(
            'gets' => $gets
        ));
    }

    /**
     * 系统字典
     */
    public function dict()
    {
        $key = Request::get('key');
        $rows = option($key);
        return $rows;
    }

    /**
     * 系统选项
     */
    public function option()
    {
        $key = Request::get('key');
        $rows = option($key);
        return $rows;
    }

    /**
     * 不支持浏览器提示
     */
    public function unsupportedBrowser()
    {
        return $this->render();
    }

    /*
     * 显示用户列表
     */
    public function dialog()
    {
        $gets = Request::all();
        return $this->render([
            'gets' => $gets
        ]);
    }

    /*
     * 调用省市县显示
     */
    public function region()
    {
        $parent_id = Request::get('parent_id', 0);
        $layer = Request::get('layer', 1);
        $names = [1=>'省' ,2=>'市', 3=>'县'];
        $title[] = ['id' => '' ,'name' => $names[$layer]];

        $rows = DB::table('region')
        ->where('parent_id', (int)$parent_id)
        ->where('layer', $layer)
        ->get()->toArray();

        $rows = array_merge($title, $rows);
        return $rows;
    }
}
