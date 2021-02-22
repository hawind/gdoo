<?php namespace App\Support;

// 极光推送的类
// 文档见：http://docs.jpush.cn/display/dev/Push-API-v3
 
/*  使用示例
    $pushObj = new Jpush();
    //组装需要的参数
    //$receive = 'all';     //全部
    //$receive = array('tag'=>array('2401','2588','9527'));      //标签
    $receive = array('alias'=>array('93d78b73611d886a74*****88497f501'));    //别名
    $content = '这是一个测试的推送数据....测试....Hello World...';
    $m_type = 'http';
    $m_txt = 'http://www.iqujing.com/';
    $m_time = '600';        //离线保留时间

    //调用推送,并处理
    $result = $pushObj->push($receive,$content,$m_type,$m_txt,$m_time);
    if($result){
        $res_arr = json_decode($result, true);
        if(isset($res_arr['error'])){                       //如果返回了error则证明失败
            echo $res_arr['error']['message'];          //错误信息
            echo $res_arr['error']['code'];             //错误码
            return false;
        }else{
            //处理成功的推送......
            echo '推送成功.....';
            return true;
        }
    }else{      //接口调用失败或无响应
        echo '接口调用失败或无响应';
        return false;
    }
*/
 
class JPush
{
    // 待发送的应用程序(appKey)
    private $app_key = '';
    // 主密码
    private $master_secret = '';
    // 推送的地址
    private $url = "https://api.jpush.cn/v3/push";
 
    // 若实例化的时候传入相应的值则按新的相应值进行
    public function __construct($app_key, $master_secret)
    {
        $this->app_key = $app_key;
        $this->master_secret = $master_secret;
    }
 
    /*  $receiver 接收者的信息
        all 字符串 该产品下面的所有用户. 对app_key下的所有用户推送消息
        tag(20个)Array标签组(并集): tag=>array('昆明','北京','曲靖','上海');
        tag_and(20个)Array标签组(交集): tag_and=>array('广州','女');
        alias(1000)Array别名(并集): alias=>array('93d78b73611d886a74*****88497f501','606d05090896228f66ae10d1*****310');
        registration_id(1000)注册ID设备标识(并集): registration_id=>array('20effc071de0b45c1a**********2824746e1ff2001bd80308a467d800bed39e');
    */

    // $content 推送的内容。
    // $m_type 推送附加字段的类型(可不填) http,tips,chat....
    // $m_txt 推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
    // $m_time 保存离线时间的秒数默认为一天(可不传)单位为秒
    public function send($receiver = 'all', $content = '', $extras = array(), $time = '86400')
    {
        $base64 = base64_encode("$this->app_key:$this->master_secret");
        $header = array("Authorization:Basic $base64",'Content-Type:application/json');
        $data = array();

        // 目标用户终端手机的平台类型 android, ios, winphone
        $data['platform'] = 'all';
        // 目标用户
        $data['audience'] = $receiver;
         
        $data['notification'] = array(
            // 统一的模式--标准模式
            'alert' => $content,
            // 安卓自定义
            'android' => array(
                'alert'      => $content,
                'title'      => '',
                'builder_id' => 1,
                'extras'     => $extras,
            ),
            // ios的自定义
            'ios' => array(
                // 'alert' => $content,
                'badge' => '1',
                'sound' => 'default',
                // 'extras' => $extras,
            ),
        );
 
        // 苹果自定义---为了弹出值方便调测
        $data['message'] = array(
            'msg_content' => $content,
            'extras'      => $extras,
        );
 
        // 附加选项
        $data['options'] = array(
            'sendno'          => time(),
            // 保存离线时间的秒数默认为一天
            'time_to_live'    => $time,
            // 指定 APNS 通知发送环境：0开发环境，1生产环境。
            'apns_production' => 1,
        );
        $param = json_encode($data);
        $res = $this->post($param, $header);
        
        if ($res) {
            // 得到返回值 -- 成功已否后面判断
            return $res;
        } else {
            // 未得到返回值--返回失败
            return false;
        }
    }
 
    // 推送的Curl方法
    public function post($data = '', $header = '')
    {
        if (empty($data)) {
            return false;
        }
        $ch = curl_init();
        // 初始化curl
        curl_setopt($ch, CURLOPT_URL, $this->url);
        // 抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);
        // post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 运行curl
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
