<?php namespace App\Support;

/**
 * 作者：Wang
 * 创建时间：2006-12-18
 * 修改时间：2012-11-22
 * 类名：Image
 * 功能：生成多种类型的缩略图
*/

/**
 *  演示
 *  $img = new Image();
 *  $img->cut('imgx.jpg', 400, 280, 3, false);
 *  // 图片水印
 *  // $img->mark('logo1.png', '', 3);
 *  // 文字水印
 *  $img->mark('FV.Zone Test', 40, 3, 2, 2, 5, 5, '#ffffff', '#000000', 80, 20);
 *  // $img->save('ss.jpg');
 *  // $img->show();
 *  echo $img->msg();
 */

class Image
{
    public $srcw;

    public $srch;

    public $destw;

    public $desth;

    // 原图类型
    public $type;

    // 生成的数据
    public $cache;

    public $image;

    public $mime;

    // 将16进制的颜色转换成10进制的(R, G, B)
    public function hexdec($color)
    {
        $color = ltrim($color, '#');
        $match = str_split($color, 2);
        if (count($match) == 3) {
            $rgb['r'] = hexdec($match[0]);
            $rgb['g'] = hexdec($match[1]);
            $rgb['b'] = hexdec($match[2]);
        }
        return $rgb;
    }

    // 水印函数
    public function mark($source, $alpha = 100, $seat = 1, $type = 1, $font_type = 3, $font_x = 10, $font_y = 10, $font_bgcolor = '#ffffff', $font_color = '#000000', $font_w = 80, $font_h = 20)
    {
        // 图片水印
        if ($type == 1) {
            list($w, $h, $t) = getimagesize($source);
            // 水印图片大于原图
            if ($w > $this->srcw || $h > $this->srch) {
                throw new Exception('水印图片大于原图。');
            }
            switch ($t) {
                case 1:
                $water = imagecreatefromgif($source);
                break;
                case 2:
                $water = imagecreatefromjpeg($source);
                break;
                case 3:
                $water = imagecreatefrompng($source);
                imagesavealpha($water, true);
                break;
            }

            // 文字水印
        } else {

            // 文字水印大于原图
            if ($font_w > $this->srcw || $font_h > $this->srch) {
                throw new Exception('文字水印大于原图。');
            }

            // 创建一个真彩色图片
            $water = imagecreatetruecolor($font_w, $font_h);
            
            // 转换文字颜色值
            $font_color = $this->hexdec($font_color);
            
            // 转换背景颜色值
            $font_bgcolor = $this->hexdec($font_bgcolor);

            $color = imagecolorallocate($water, $font_color['r'], $font_color['g'], $font_color['b']);
            $bgcolor = imagecolorallocate($water, $font_bgcolor['r'], $font_bgcolor['g'], $font_bgcolor['b']);
            
            // 背景色填充
            imagefill($water, 0, 0, $bgcolor);

            // 绘制文字
            imageString($water, $font_type, $font_x, $font_y, $source, $color);
            $w = $font_w;
            $h = $font_h;
        }
        
        switch ($seat) {
            case 1:
            $x = 10;
            $y = 0;
            break;
            case 2:
            $x = ($this->destw - $w)/2;
            $y = ($this->desth - $h)/2;
            break;
            case 3:
            $x = $this->destw - $w - 10;
            $y = $this->desth - $h - 10;
            break;
            default:
            $x = 10;
            $y = 0;
        }
        // png 24位，真彩色不需要透明设置
        if ($t == 3) {
            imagecopy($this->cache, $water, $x, $y, 0, 0, $w, $h);
        } else {
            imagecopymerge($this->cache, $water, $x, $y, 0, 0, $w, $h, $alpha);
        }
    }

    public function crop($file, $width, $height, $mode, $center)
    {
        // 原文件不存在
        if (!is_file($file)) {
            throw new Exception('原文件不存在。');
        }
        $temp = getimagesize($file);
        $this->srcw = $srcw = $temp[0];
        $this->srch = $srch = $temp[1];
        $this->type = $temp[2];

        // 1=gif 2=jpg 3=png
        $this->mime = $temp['mime'];
        switch ($this->type) {
            case 1:
            $image = imagecreatefromgif($file);
            break;
            case 2:
            $image = imagecreatefromjpeg($file);
            break;
            case 3:
            $image = imagecreatefrompng($file);
            break;
        }

        if (!$image) {
            return;
        }

        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }

        // 原始宽高比大于目标宽高比, 则取目标
        $this->image = $image;
        $destw = $w = $width;
        $desth = $h = $height;
        $srcx = $srcy = 0;

        // 按宽度等比例缩放
        switch ($mode) {
            case 1:
            $desth = ceil($srch / $srcw * $destw);
            break;

            case 2:
            $destw = ceil($srcw / $srch * $desth);
            break;

            case 3:
            // 原始宽高比目标宽高大，调整原始复制宽度
            if ($srcw/$srch > $w/$h) {
                $oldw = $srcw;
                $srcw = ceil($w / $h * $srch);
                if ($center) {
                    $srcx = ceil(($oldw - $srcw)/2);
                }
            } else {
                // 调整原始复制高度
                $oldh = $srch;
                $srch = ceil($h / $w * $srcw);
                if ($center) {
                    $srcy = ceil(($oldh - $srch)/2);
                }
            }
            break;
        }
        
        $this->destw = $destw;
        $this->desth = $desth;
        $this->cache = imagecreatetruecolor($destw, $desth);

        if (function_exists('imagecopyresampled')) {
            imagecopyresampled($this->cache, $image, 0, 0, $srcx, $srcy, $destw, $desth, $srcw, $srch);
        } else {
            imagecopyresized($this->cache, $image, 0, 0, $srcx, $srcy, $destw, $desth, $srcw, $srch);
        }
    }

    public function save($path, $quality = 100)
    {
        switch ($this->type) {
            case 1:
            imagegif($this->cache, $path);
            break;
            case 2:
            imagejpeg($this->cache, $path, $quality);
            break;
            case 3:
            imagepng($this->cache, $path);
            break;
        }
        if ($this->image) {
            imageDestroy($this->image);
        }
    }

    public function display($quality = 100)
    {
        header("Content-type: $this->mime");

        switch ($this->type) {
            case 1:
            imagegif($this->cache, $path);
            break;
            case 2:
            imagejpeg($this->cache, $path, $quality);
            break;
            case 3:
            imagepng($this->cache, $path);
            break;
        }
        imageDestroy($this->image);
    }
}
