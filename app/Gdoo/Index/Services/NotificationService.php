<?php namespace Gdoo\Index\Services;

use Log;
use DB;
use Mail;

use Gdoo\System\Models\Setting;

use Gdoo\Wechat\Services\WechatService;
use Gdoo\System\Services\SmsService;

class NotificationService
{
    /**
     * 微信公众号模板消息
     */
    public static function wechatTemplate($users, $content)
    {
        if (env('WECHAT_MESSAGE_PUSH') === false) {
            return false;
        }

        if (empty($users) || empty($content)) {
            return false;
        }

        try {
            $tousers = DB::table('wechat_user')->whereIn('user_id', $users)->pluck('openid');
            if ($tousers) {
                $app = WechatService::getApp();
                foreach ($tousers as $touser) {
                    $content['touser'] = $touser;
                    $app->template_message->send($content);
                }
            }
            return true;

        } catch(\Exception $e) {
            system_log('notification.wechat', '微信消息', $e->getMessage(), 'error');
            return false;
        } 
    }

    /**
     * 站内通知
     */
    public static function site($users, $subject, $content, $url)
    {
        if (empty($subject) || empty($content) || empty($users)) {
            return false;
        }

        try {
            foreach ($users as $user_id) {
                DB::table('user_message')->insert([
                    'content' => $subject.$content,
                    'url' => $url,
                    'read_id' => $user_id,
                    'created_id' => auth()->id(),
                ]);
            }
            return true;

        } catch(\Exception $e) {
            system_log('notification.site', '站内消息', $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 添加新通知
     */
    public static function sms($users, $subject, $content = '')
    {
        if (empty($subject) || empty($users)) {
            return false;
        }

        try {
            // 短信群发一次最大条数
            $users = array_chunk($users, 500);
            foreach ($users as $user) {
                $user = join(',', $user);
                if ($user) {
                    // 记录发送结果
                    $res = SmsService::send($user, $subject.$content);
                    if ($res['code'] <> 0) {
                        abort_error($res['msg']);
                    }
                    foreach ($res['data'] as $row) {
                        $data = json_encode([
                            'msg' => $row['msg'],
                            'code' => $row['code'],
                            'count' => $row['count'],
                        ], JSON_UNESCAPED_UNICODE);
                        $log = [
                            'content' => $subject.$content,
                            'data' => $data,
                            'phone' => $row['mobile'],
                            'status' => $row['code'] == 0 ? 1 : 0,
                        ];
                        DB::table('sms_log')->insert($log);
                    }
                }
            }
            return true;
            
        } catch(\Exception $e) {
            system_log('notification.sms', '短信消息', $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 邮件通知
     */
    public static function mail($view, $users, $subject, $content)
    {
        if ($subject == '' || $content == '' || empty($users)) {
            return false;
        }

        $setting = Setting::where('type', 'system')->pluck('value', 'key');
        $mail = DB::table('mail')->where('status', 1)->orderBy('sort', 'asc')->first();
        $config = config('mail');
        config([
            'mail' => array_merge($config, [
                'host' => $mail['smtp'],
                'port' => $mail['port'],
                'encryption' => $mail['secure'],
                'username' => $mail['user'],
                'password' => $mail['password'],
                'from' => [
                    'address' => $mail['user'],
                    'name' => $mail['name'],
                ],
            ])
        ]);

        $data['subject'] = $subject;
        $data['content'] = $content;

        try {
            return Mail::send('emails.'.$view, $data, function ($message) use ($setting, $users, $subject) {
                foreach ($users as $user) {
                    $message->to($user);
                }
                $message->subject($setting['title']);
            });
        } catch(\Exception $e) {
            system_log('notification.mail', '邮件消息', $e->getMessage(), 'error');
            return false;
        }
    }
}
