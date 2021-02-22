<?php namespace Gdoo\Index\Controllers;

use DB;
use URL;
use Request;

use App\Support\Pinyin;

use Gdoo\User\Models\User;
use Gdoo\Index\Models\Notification;

class DemoController extends Controller
{

    #[Attribute(Attribute::TARGET_FUNCTION)]
    public function vouchAction()
    {
        return $this->display();
    }

    public function helloAction()
    {
        //\App\Jobs\SendEmail::dispatch('abc', ['fvzone@qq.com'], '您的验证码是0123', 'fsdafsd哈哈哈');
        //\App\Jobs\SendSms::dispatch(['15182223008'], '您的验证码是01234');

        $menus = DB::table('menu')->get();
        foreach($menus as $menu) {
            $url = str_replace('.', '/', $menu['url']);
            DB::table('menu')->where('id', $menu['id'])->update([
                'url' => $url,
            ]);
        }

        //\App\Jobs\SendSite::dispatch([1], '您的验证码是0123');
        exit;
        
        /*
        $dbParams = array(
            'dbname' => 'gdoooa_demo',
            'user' => 'root',
            'password' => 'root',
            'host' => 'localhost:3307',
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
            'default_table_options' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]
        );

        $paths = array(base_path(). "/abc");
        $isDevMode = false;

        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $em = \Doctrine\ORM\EntityManager::create($dbParams, $config);

        $platform = $em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        $metadata = $em->getClassMetadata('App\\Share');
        //$cmf = $em->getMetadataFactory();
        //$class = $cmf->getMetadataFor('Share');
        print_r($metadata);
        */

        $abc['indexes'] = [
            'idx_object_id' => [
                'columns' => ['source_id'],
            ],
        ];

        $abc['columns'] = [
            'id' => [
            'name' => '', 
            'type' => '', 
            'default' => '', 
            'notnull' => '', 
            'length' => '', 
            'unsigned' => '', 
            'autoincrement' => '', 
            'comment' => '',
        ], 'name' => [
            'name' => '', 
            'type' => '', 
            'default' => '', 
            'notnull' => '', 
            'length' => '', 
            'unsigned' => '', 
            'autoincrement' => '',
            'comment' => '',
        ]];

        file_put_contents(base_path().'/abc.json', json_encode($abc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        /*
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
        $sm = $conn->getSchemaManager();
        $columns = $sm->listTableColumns('role');

        foreach ($columns as $column) {
            $column->
            echo $column->getName() . ': ' . $column->getType() . "\n";
        }
        print_r($columns);
        */
        exit;

        $abc = \Gdoo\Produce\Services\ProduceService::getPlanDetail('2020-08-01', '2020-08-04', 0, 0, 0);
        // $abc = \Gdoo\Stock\Service\StockService::reportOrderStockInOut(139, 0, '', '', '2020-08-01', '2020-09-21', 1, 1, 0);
        print_r($abc);
        exit;

        /*
        $rows = DB::table('model_permission')->get();
        foreach($rows as $row) {
            $data = json_decode($row['data'], true);
            print_r($data);
            foreach($data as $k => $rr) {
                // _product
                print_r($k);
                echo "\n";
            }
        }
        exit;
        */

        /*
        $users = DB::table('user')
        ->where('group_id', 2)
        ->get(['id','status', 'name', 'username']);

        foreach($users as $user) {
            DB::table('customer')->where('user_id', $user['id'])->update([
                'status' => $user['status'], 
                'name' => $user['name'],
                'code' => $user['username'],
            ]);
        }
        echo 'demo';
        exit;
        */

        $gets['stock_allocation']['out_warehouse_id'] = 111;
        if($gets['stock_allocation']['out_warehouse_id'] <> 140 and $gets['stock_allocation']['out_warehouse_id'] <> 139 and $gets['stock_allocation']['out_warehouse_id'] <> 20005 and $gets['stock_allocation']['out_warehouse_id'] <> 20048) {
            echo '1111111111111';
        }

        if($gets['stock_allocation']['out_warehouse_id'] == 140 or $gets['stock_allocation']['out_warehouse_id'] == 139 or $gets['stock_allocation']['out_warehouse_id'] == 20005 or $gets['stock_allocation']['out_warehouse_id'] == 20048) {
            echo '22222222222222';
            exit;
        }
        exit;

        /*
        $customers = DB::table('tbb_customer')
        ->get(['tbb_customer.*']);
        
        $users = [];
        foreach($customers as $customer) {
            $users[] = [
                'id' => $customer['CustID'],
                'code' => $customer['cCusCode'],
                'name' => $customer['cCusName'],
                'tel' => $customer['cCusPhone'],
                'fax' => $customer['cCusFax'],
                'address' => $customer['cCusAddress'],

                'head_phone' => $customer['cCusLPersonPhone'],
                'head_name' => $customer['cCusLPerson'],
                'email' => $customer['cCusEmail'],
                
                // 直营 3
                'type_id' => (int)($customer['bZykh2'] == 1 ? 3 : 1),

                // 是否调拨
                'is_allocate' => (int)$customer['bZykh'],

                // 一般纳税人
                'general_taxpayer' => (int)$customer['Sfybnsr'],

                'status' => (int)$customer['Status'],

                'warehouse_address' => $customer['CustWhAddress'],
                'warehouse_tel' => $customer['TelPhone'],
                'warehouse_contact' => $customer['CustWhPerson'],
                'warehouse_phone' => $customer['CustWhPhone'],
                'warehouse_size' => $customer['CustWhSqure'],
            ];
        }
        */

        /*
        $pwd = bcrypt('123456');
        foreach($users as $user) {
            DB::table('user')->insert([
                'id' => $user['id'],
                'username' => $user['code'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['head_phone'],
                'status' => $user['status'], 
                'group_id' => 2,
                'role_id' => 2,
                'password' => $pwd,
            ]);
            DB::table('customer')->insert($user);
        }
        exit;

        $ufcustomer = DB::table('ufcustomer')->get()->toArray();
        $ccodes = [];
        foreach($ufcustomer as $_ufcustomer) {
            $ccodes[$_ufcustomer['cCusHeadCode']][] = $_ufcustomer['cCusCode'];
        }

        foreach($ccodes as $cid => $_codes) {
            foreach($_codes as $i => $_code) {

            }
        }
        */

        //100189

        $ufcustomer = DB::table('ufcustomer')->get()->toArray();
        $ccodes = [];
        foreach($ufcustomer as $_ufcustomer) {
            $ccodes[$_ufcustomer['cCusHeadCode']][] = $_ufcustomer['cCusCode'];
        }

        $users = DB::table('user')->where('group_id', 2)->get()->keyBy('username')->toArray();
        foreach($ccodes as $cid => $_codes) {
            foreach($_codes as $i => $_code) {

            }
        }

        /*
        set_time_limit(0);
        $rows = file_get_contents(public_path('r.json'));
        $rows = json_decode($rows, true);
        foreach($rows as $row) {
            $id1 = DB::table('region')->insertGetId(['name' => $row['name'], 'code' => $row['code'], 'layer' => 1]);
            foreach($row['cityList'] as $city) {
                $id2 = DB::table('region')->insertGetId(['layer' => 2, 'parent_id' => $id1, 'name' => $city['name'], 'code' => $city['code']]);
                foreach($city['areaList'] as $area) {
                    DB::table('region')->insertGetId(['layer' => 3, 'parent_id' => $id2, 'name' => $area['name'], 'code' => $area['code']]);
                }
            }
        }
        echo 111;
        */
        exit;

        /*
        \App\Jobs\SendSite::dispatch([1], '您的验证码是0123');
        exit;

        DB::enableQueryLog();
        $user = DB::table('user as u')->orderBy('id', 'desc')->orderBy('username', 'asc')->first();
        print_r(DB::getQueryLog());
        */
        /*
        $units = option('product.unit')->pluck('id', 'name');
        $rows = DB::table('product')->get();
        foreach($rows as $row) {
            $unit = strtolower($row['unit']);
            if (isset($units[$unit])) {
                $row['unit_id'] = $units[$unit];
                DB::table('product')->where('id', $row['id'])->update($row);
            } else {
                echo $unit."\n";
            }
        }
        */
        exit;
        /*
        $t1 = microtime(true);

        $stocks = DB::table('stock_yonyou_data')
        ->groupBy('code')
        ->selectRaw('sum(quantity_set - quantity_get) as quantity,code')
        ->pluck('quantity', 'code');

        $abc = 0;
        foreach ($stocks as $stock) {
            $abc += $stock;
        }

        echo $abc."<br>";

        $t2 = microtime(true);
        echo '耗时'.($t2 - $t1).'秒';

        ``

        exit;
        */

        //$abc = \Yunpian::send('15182223008', '您的验证码是5967');
        //print_r($abc);
        //exit;

        /*
        $agentid = 1000035;
        $url = 'http://www.shenghuafood.com/article/article/view?id=1336&agentid='.$agentid;
        //$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=ww42727b1e44abc7fa&redirect_uri='.$u.'&response_type=code&scope=snsapi_privateinfo&agentid='.$agentid.'&state='.$agentid.'#wechat_redirect';

        $msg = array(
            'touser'  => 'qy01bbfb5d6f30ae009bc0e5b8fb',
            'toparty' => '',
            'msgtype' => 'news',
            'agentid' => $agentid,
            'news' => array(
                "articles"=> array(
                    0 => array(
                        "title"       => "有新的公告提醒",
                        "description" => "[公告】关于西安大军区的调整公告",
                        "url"         => $url,
                        "picurl"      => ""
                ))
            )
            'text'    => array(
                "content"=>"各部门及同事：\n".
                "为更好的服务好再来大厦，满足大厦入驻员工的班车需求，现对部分班车路线及时刻做相应调整，自2016年9月20日零时生效。详情点击\n<a href=\"http://banche.hoolilai.com\">http://banche.hoolilai.com</a>"
            )
        );
                
        $api = new \App\Wechat\Work\App($agentid);
        
        var_dump($api->sendMsgToUser($msg));

        */
        $xml = simplexml_load_file('tpl.xml');
        /*
        $attributes = $xml->record->attributes();
        foreach ($attributes as $k => $v) {
            print_r($k.'---'.$v);
        }
        */
        $form = $xml->xpath("record[@type='form']/form")[0];

        foreach ($form as $key => $node) {
            if ($key == 'group') {
                $fields = [];
                foreach ($node as $k => $field) {
                    if ($k == 'field') {
                        $attr = $field->attributes();
                        $col  = $attr['col'] - 2;
                        $abc[] = '<label class="col-sm-2 control-label" for=""><span class="red">*</span> 供应商</label>';
                        $abc[] = '<div class="col-sm-'.$col.' control-text"><input type="text" value="180407-185" required="required" class="form-control input-sm" id="supplier_price_sn" name="supplier_price[sn]" readonly="readonly"></div>';
                    }
                    //print_r($k);
                }
                print_r($abc);
            }
        }

        //$abc = Yunpian::send('15182223008', '您的验证码是5967');
        //print_r($abc);

        //$ab = new Hawind\Core();
        
        // 开启 log
        //DB::connection()->enableQueryLog();

        //$abc = User::whereIn('user.id', [1,2,3,4])->select(['user.*','user.name as role_name'])->paginate();

        // 获取已执行的查询数组
        //$abc = DB::getQueryLog();

        //$ab->test($abc);

        //print_r($abc);

        //print_r($cron->isDue());
        //$cron = Cron\CronExpression::factory('0 0 0 ? 1/2 FRI#2 *');
        //if ($cron->isDue()) {
        // The promotion should be enabled!
        //}

        /*
        $datas = DB::table('stock')
        ->where('date', '0000-00-00')
        ->get();

        foreach ($datas as $key => $data) {
            $data['date'] = date('Y-m-d', $data['add_time']);
            DB::table('stock')->where('id', $data['id'])->update($data);
        }
        */

        /*
        $logs = DB::table('model_step_log')
        ->where('table', 'promotion')
        ->where('step_status', 'next')
        ->where('created_id', '278')
        ->get();

        foreach ($logs as $log) {
            $data['data_30'] = date('Y-m-d', $log['created_at']);
            DB::table('promotion')->where('id', $log['table_id'])->update($data);
        }
        */

        //$sms = new iscms\Alisms\SendsmsPusher();

        //$t = "项目流程提醒! 主题：关于违反销售管理制度之扣分——龚涛天 -【销售行为】处罚等待确认！";
        //$words = Yunpian::replaceWords($t);

        //$t = str_replace($words[0], $words[1], $t);

        //$b = mb_str_split('销售');

        //print_r(var_dump($words));

        //$abc = Yunpian::getBlackWord($words);

        //$abc = Yunpian::getTpl('1701454');

        // $abc = Yunpian::getUser();

        //print_r($abc['balance'] / 0.05);

        //print_r($words);

        exit;

        /*
        $departments = DB::table('department')->pluck('name', 'id');
        $roles = DB::table('role')->pluck('name', 'id');
        $users = DB::table('user')->pluck('name', 'id');

        $shares = DB::table('article')->get();

        foreach ($shares as $share) {

            $id = $name = [];

            $share_user = explode(',', $share['user_id']);
            foreach ($share_user as $user) {
                if($users[$user]) {
                    $id[] = 'u'.$user;
                    $name[] = $users[$user];
                }
            }

            $share_role = explode(',', $share['role_id']);
            foreach ($share_role as $role) {
                if($roles[$role]) {
                    $id[] = 'r'.$role;
                    $name[] = $roles[$role];
                }
            }

            $share_department = explode(',', $share['department_id']);
            foreach ($share_department as $department) {
                if($departments[$department]) {
                    $id[] = 'd'.$department;
                    $name[] = $departments[$department];
                }
            }

            DB::table('article')->where('id', $share['id'])->update([
                'receive_id'   => join(',', $id),
                'receive_name' => join(',', $name)
            ]);
        }
        */

        /*
        $users = User::get();

        foreach ($users as $user) {

            if($user->password_text == '' && mb_strlen($user->password) == 32) {
                $user->password = \Hash::make($user->username);
                $user->password_text = $user->username;
                $user->save();
            }
        }
        */

        /*
        $p2 = DB::connection('sqlite')
        ->table('city')
        ->where('parent_id', 2621)
        ->get();

        print_r($p2);
        exit;

        */

        // app()->configure('pcas');

        // $abc = config('pcas');

        // print_r(json_encode($abc, JSON_UNESCAPED_UNICODE));

        /*

        $users = DB::table('user')->get();

        foreach ($users as $user) {

            $data['warehouse_tel']     = $user['warehouse_tel'];
            $data['warehouse_contact'] = $user['warehouse_contact'];
            $data['warehouse_phone']  = $user['warehouse_phone'];
            $data['warehouse_address'] = $user['warehouse_address'];
            $data['invoice_type']      = $user['invoice'];

            DB::table('customer')->where('user_id', $user['id'])->update($data);
        }
        */
        // print_r(123);
        // exit;
        // return $this->render([]);
    }
}