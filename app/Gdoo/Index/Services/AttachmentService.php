<?php namespace Gdoo\Index\Services;

use Request;
use DB;
use Auth;

class AttachmentService
{
    public static function files($name, $path = 'default')
    {
        $files = Request::file($name);
    
        $path = $path.'/'.date('Ym');
        $upload_path = upload_path().'/'.$path;

        $res = [];

        foreach ($files as $file) {
            if ($file->isValid()) {
                // 文件后缀名
                $extension = $file->getClientOriginalExtension();
                // 兼容do客户端上传
                if ($extension == 'do') {
                    $clientName = $file->getClientOriginalName();
                    $extension = pathinfo(substr($clientName, 0, -3), PATHINFO_EXTENSION);
                }

                // 文件新名字
                $name = date('dhis_').str_random(4).'.'.$extension;
                $name = mb_strtolower($name);

                if ($file->move($upload_path, $name)) {
                    $res[] = DB::table('attachment')->insertGetId([
                        'name' => $name,
                        'path' => $path.'/'.$name,
                        'type' => $extension,
                        'size' => $file->getClientSize(),
                        'status' => 1,
                    ]);
                }
            }
        }
        return join(',', array_filter($res));
    }

    public static function base64($images, $path = 'default', $extension = 'jpg')
    {
        $path = $path.date('/Ym');
        $directory = upload_path().'/'.$path;

        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }
        
        $res = [];

        foreach ($images as $image) {
            $name = date('dhis_').str_random(4).'.'.$extension;
            $name = mb_strtolower($name);

            $image = base64_decode(str_replace(' ', '+', $image));
            $size = file_put_contents($directory.'/'.$name, $image);
            if ($size) {
                $res[] = DB::table('attachment')->insertGetId([
                    'name' => $name,
                    'path' => $path.'/'.$name,
                    'type' => $extension,
                    'size' => $size,
                    'status' => 1,
                ]);
            }
        }
        return join(',', array_filter($res));
    }

    /**
     * 将ids字符串转换为数组
     */
    public static function getIds($ids)
    {
        if (is_array($ids)) {
            return $ids;
        }
        $ids = array_filter(explode("\n", $ids));

        return (array)$ids;
    }

    /**
     * 获取指定编号附件列表
     */
    public static function get($ids)
    {
        $ids = self::getIds($ids);
        return DB::table('attachment')
        ->whereIn('id', $ids)
        ->where('status', 1)->get();
    }

    /**
     * 获取当前ID附件和草稿
     */
    public static function edit($ids, $key)
    {
        $res['rows'] = self::get($ids);
        $res['draft'] = self::draft($key);
        return $res;
    }

    /**
     * 获取当前ID附件
     */
    public static function show($ids)
    {
        $res['rows'] = self::get($ids);
        return $res;
    }

    /**
     * 发布附件，改成状态为可用
     */
    public static function publish($ids)
    {
        $ids = self::getIds($ids);
        $rows = DB::table('attachment')
        ->whereIn('id', $ids)
        ->where('status', 0)
        ->get();
        foreach ($rows as $row) {
            DB::table('attachment')->where('id', $row['id'])->update([
                'status' => 1,
            ]);
        }
        return true;
    }

    /**
     * 发布附件，改成状态为可用
     */
    public static function store($key)
    {
        $rows = self::draft($key);
        foreach ($rows as $row) {
            DB::table('attachment')->where('id', $row['id'])->update([
                'status' => 1,
            ]);
        }
        return true;
    }

    /**
     * 获取草稿文件
     */
    public static function draft($key, $user_id = 0)
    {
        if ($user_id == 0) {
            $user_id = auth()->id();
        }
        return DB::table('attachment')
        ->where('created_id', $user_id)
        ->where('key', $key)
        ->where('status', '0')
        ->get();
    }

    /**
     * 删除附件和文件
     */
    public static function remove($ids)
    {
        $ids = self::getIds($ids);
        $rows = DB::table('attachment')->whereIn('id', $ids)->get();
        foreach ($rows as $row) {
            // 删除文件
            $file = upload_path().'/'.$row['path'];
            if (is_file($file)) {
                unlink($file);
            }
            DB::table('attachment')->where('id', $row['id'])->delete();
        }
        return 1;
    }
}
