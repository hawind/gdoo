<?php namespace App\Support;

use Session;

class Captcha
{
    //显示验证码
    public static function make($key = 'captcha')
    {
        header('Content-type:image/png');
        //创建图片
        $im = imagecreate(80, 30);
        //第一次对imagecolorallocate()的调用会给基于调色板的图像填充背景色
        $bg = imagecolorallocate($im, 255, 255, 255);
        //验证码使用字体
        $font_style = public_path('assets/fonts/milkcocoa.ttf');
        //字符
        $text_char = 'BCEFGHJKMPQRTVWXY2346789';
        //字符间隔
        $text_spac = 16;

        $auth_code = '';

        //产生随机字符
        for ($i = 0; $i < 4; $i++) {
            $font_color = imagecolorallocate($im, mt_rand(50, 200), mt_rand(0, 155), mt_rand(0, 155));
            $text_str = $text_char[mt_rand(0, 23)];
            imagettftext($im, 24, mt_rand(0, 20) - mt_rand(0, 25), 5 + $i * $text_spac, mt_rand(25, 30), $font_color, $font_style, $text_str);
            $auth_code .= $text_str;
        }
        
        //用户和用户输入验证码做比较
        Session::put($key, $auth_code);
     
        //干扰点
        for ($i = 0; $i < 250; $i++) {
            imagesetpixel($im, rand(0, 130), rand(0, 145), $font_color);
            imagesetpixel($im, rand(0, 130), rand(0, 145), $font_color);
        }
        imagepng($im);
        imagedestroy($im);
    }

    public static function check($key, $value)
    {
        return Session::has($key) && Session::get($key) === strtoupper($value) ? true : false;
    }
}
