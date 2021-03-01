<?php namespace Gdoo\Wechat\Controllers;

use Cache;
use DB;
use Log;

use Gdoo\Wechat\Models\WechatUser;
use Gdoo\Wechat\Services\WechatService;

use Gdoo\Index\Controllers\Controller;

use Gdoo\System\Models\Setting;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;

class EchoController extends Controller
{
    private $wechat = null;
    private $openid = null;

    public function __construct() {
        parent::__construct();
    }

    public function indexAction()
    {
        if (empty($_GET['echostr']) && empty($_GET["signature"]) && empty($_GET["nonce"])) {
            exit('Access denied');
        }

        $app = WechatService::getApp();
        $app->server->push(function ($msg) {

            $config = WechatService::getConfig();
            $this->openid = $msg['FromUserName'];

            if ($config['status'] == 0) {
                return '服务关闭中，请稍后再试。';
            }

            switch ($msg['MsgType']) {
                case 'text':
                    return $msg['Content'];
                    break;
                case 'image':
                    return $msg['Content'];
                    break;
                case 'voice':
                    return $msg['Content'];
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                    break;
                case 'event':
                    switch (strtolower($msg['Event'])) {
                        // 关注
                        case 'subscribe':
                            //$this->subscribe($msg);
                            break;
                        // 取消关注
                        case 'unsubscribe':
                            //$this->subscribe($msg, 'unsubscribe');
                            break;
                        // 用户扫码已关注时的事件推送
                        case 'scan':
                            //$this->scan($msg);
                            break;
                        // 上报地理位置事件 event_location
                        case 'location':
                            //$this->special('event_location', $msg);
                            break;
                        // 自定义菜单事件
                        case 'click':
                            //$this->keyword($msg['EventKey'], $msg);
                            break;
                        // 点击菜单跳转链接时的事件推送
                        case 'view':
                            //$this->special('view', $msg);
                            break;
                        default:
                            break;
                        break;
                    }
                // 其它消息
                default:
                    // return '收到其它消息';
                    break;
            }
        });

        return $app->server->serve();
    }

    /**
     * 文件消息处理
     */
    protected function api()
    {
        return $this->keys("wechat_keys#keys#{$this->receive['content']}", false, $this->forceCustom);
    }

    /**
     * 文件消息处理
     */
    protected function text()
    {
        return $this->keys("wechat_keys#keys#{$this->receive['content']}", false, $this->forceCustom);
    }

    /**
     * 粉丝关注\取消关注
     */
    public function subscribe($msg, $type = 'subscribe')
    {
        $friendInfo = $this->api->get_user_info($this->openid);
        $friendInfo = (array)$friendInfo[1];
        $model = WechatUser::firstOrNew(['openid' => $this->openid]);
        // 公众号没有权限获取用户基本信息 可按需求扩展
        if (empty($friendInfo)) {
            if ($type == 'subscribe') {
            } elseif ($type == 'unsubscribe') {
            }
        } else {
            if ($type == 'subscribe') {
                $model->avatar = $friendInfo['headimgurl'];
                $model->openid = $friendInfo['openid'];
                $model->gender = $friendInfo['sex'];
                $model->name = $friendInfo['nickname'];
                $model->address = $friendInfo['country']."\n".$friendInfo['province']."\n".$friendInfo['city'];
                $model->subscribe = 1;
                $model->created_at = time();
                $model->status = 1;

                // 有场景参数
                if (isset($msg['Ticket'])) {
                    $recommend = WechatUser::where('ticket', $msg['Ticket'])->first();
                    $model->parent_id = $recommend['id'];
                }
                // 关注成功后发送消息

            } elseif ($type == 'unsubscribe') {
                $model->subscribe = 0;
                $model->unsubscribe_time = time();
                $model->status = 0;
            }

            $model->save();
        }

        if (isset($msg['Ticket'])) {
            $this->replyNews($msg);
        } else {
            $rule = Db::table('wx_mp_rule')
            ->where('mpid', $this->mid)
            ->where('event', 'subscribe')
            ->first();

            if ($rule) {
                $reply = DB::table('wx_mp_reply')->where('reply_id', $rule['reply_id'])->first();
                $this->we->reply($reply['content']);
            }
        }
    }

    public function replyArticle($msg) {
        $this->we->reply([
            'type' => 'news',
            'articles' => [[
                'title' => '关注',								
                'description' => '关注',						
                'picurl' => '',
                'url' => '',
            ]]
        ]);
    }

    /**
     * 扫描二维码事件
     */
    public function scan($msg)
    {
        // 有场景参数
        if (isset($msg['Ticket'])) {
            $this->replyArticle($msg);
        }
    }

    public function json($data, $status = false)
    {
        $json = [
            'data' => $data,
            'status' => $status,
        ];
        return response()->json($json)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}