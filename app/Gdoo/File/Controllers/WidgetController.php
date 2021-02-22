<?php namespace Gdoo\File\Controllers;

use Request;
use URL;

use Gdoo\Index\Controllers\Controller;

class WidgetController extends Controller
{
    public $permission = ['index', 'download'];

    public $rows = [/*[
        'title' => '盛华管理手机客户端',
        'date'  => '2018-04-09',
        'android_url' => '/uploads/com.shenghua.oa/1.0.12.apk',
        'ios_url'     => ''
    ],[
        'title' => '盛华市场手机客户端',
        'date' => '2018-04-07',
        'android_url' => '/uploads/com.shenghua.customer/1.0.3.apk',
        'ios_url'     => '/uploads/com.shenghua.customer/1.0.3.ipa'
    ],*/[
        'title' => '身份验证器(GoogleAuthenticator)',
        'date' => '2015-06-17',
        'android_url' => '/uploads/android/authenticator2_21.apk',
        'ios_url'     => ''
    ],[
        'title' => '日历同步助手(Caldav)',
        'date' => '2017-06-29',
        'android_url' => '/uploads/android/calendar.caldav.sync.apk',
        'ios_url'     => ''
    ]];

    public function downloadAction()
    {
        $key = Request::get('key');
        $row = $this->rows[$key];

        $file = '';

        if (get_device_type() == 'ios') {
            if ($row['ios_url']) {
                $file = url($row['ios_url']);
            }
            $img  = '/assets/images/ios_download.png';
        } else {
            if ($row['android_url']) {
                $file = url($row['android_url']);
            }
            $img  = '/assets/images/android_download.png';
        }

        if (is_weixin()) {
            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta name="viewport" content="width=device-width" />
                <title></title>
                <style>
                    img {
                        width: 100%;
                        height: 100%;
                    }
                    body {
                        background-color:#a2a2a2;
                    }
                </style>
            </head>
            <body>
                <div>
                    <img src="'.$img.'" />
                </div>
            </body>
            </html>';
        } else {
            if ($file) {
                echo "<form action='".$file."' id='go' method='GET'></form><script type='text/javascript'>document.getElementById('go').submit();</script>";
            }
        }
    }

    public function indexAction()
    {
        if (Request::isJson()) {
            foreach ($this->rows as $k => &$row) {
                $url = '';

                if ($row['android_url']) {
                    $url .= '<a class="option" href="'.url($row['android_url']).'"><i class="fa fa-fw fa-android"></i></a>';
                }

                if ($row['ios_url']) {
                    $url .= ' <a class="option" href="'.url($row['ios_url']).'"><i class="fa fa-fw fa-apple"></i></a>';
                } else {
                    $url .= ' <span class="text-muted" href="javascript:;"><i class="fa fa-fw fa-apple"></i></span>';
                }

                $qrcodeURL = url('index/api/qrcode', ['size' => 6, 'data' => url('file/widget/download', ['key' => $k])]);
                
                $url .= ' <a class="option" href="javascript:;" onclick=\'$.messager.alert("'.$row['title'].'","<div align=\"center\"><img src=\"'.$qrcodeURL.'\"></div>");\'><i class="fa fa-fw fa-qrcode"></i></a>';
                
                $row['option'] = $url;
            }

            $json['total'] = sizeof($this->rows);
            $json['data'] = $this->rows;
            return response()->json($json);
        }
        return $this->render();
    }
}
