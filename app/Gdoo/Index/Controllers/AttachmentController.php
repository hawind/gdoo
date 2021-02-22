<?php namespace Gdoo\Index\Controllers;

use Session;
use Request;
use Validator;
use DB;

use Gdoo\Index\Services\AttachmentService;
use URL;

class AttachmentController extends DefaultController
{
    public $permission = ['list','view','preview','create','delete','download','show', 'uploader', 'draft', 'qrcode'];

    /**
     * 上传文件
     */
    public function uploaderAction()
    {
        if (Request::method() == 'POST') {
            $file = Request::file('file');

            /*
            $rules = [
                'file' => 'mimes:'.$this->setting['upload_type'],
            ];
            $v = Validator::make(['file' => $file], $rules);
            */

            $upload_type = explode(',', $this->setting['upload_type']);
        
            if ($file->isValid()) {

                // 文件后缀名
                $extension = $file->getClientOriginalExtension();

                // 判断文件类型
                if (!in_array($extension, $upload_type)) {
                    return;
                }

                // 获取上传uri第一个目录
                $key  = Request::get('key', 'default');
                $node = Request::get('path', 'default');
                $path = $node.date('/Ym/');

                $upload_path = upload_path().'/'.$path;
                
                // 文件新名字
                $filename = date('dhis_').str_random(4).'.'.$extension;
                $filename = mb_strtolower($filename);

                if ($file->move($upload_path, $filename)) {
                    $data = [
                        'name' => mb_strtolower($file->getClientOriginalName()),
                        'node' => $node,
                        'path' => $path.$filename,
                        'type' => $extension,
                        'key'  => $key,
                        'size' => $file->getClientSize(),
                    ];
                    $insertId = DB::table('attachment')->insertGetId($data);
                    $data['id'] = $insertId;
                    $data['success'] = true;
                    return json_encode($data);
                }
            }
        }
        $query = Request::all();
        $SERVER_URL = url("index/attachment/uploader", $query);
        return $this->render([
            'SERVER_URL' => $SERVER_URL,
            'key' => $query['key'],
        ]);
    }

    /**
     * 新建文件
     */
    public function createAction()
    {
        set_time_limit(0);

        $file = Request::file('Filedata');

        $rules = [
            'file' => 'mimes:'.$this->setting['upload_type'],
        ];
        $v = Validator::make(['file' => $file], $rules);

        if ($file->isValid() && $v->passes()) {
            // 获取上传uri第一个目录
            $path = Request::get('path', 'main').date('/Y/m/');

            $upload_path = upload_path().'/'.$path;
            
            // 文件后缀名
            $extension = $file->getClientOriginalExtension();

            // 文件新名字
            $filename = date('dhis_').str_random(4).'.'.$extension;
            $filename = mb_strtolower($filename);

            if ($file->move($upload_path, $filename)) {
                return DB::table('attachment')->insertGetId([
                    'name' => mb_strtolower($file->getClientOriginalName()),
                    'path' => $path.$filename,
                    'type' => $extension,
                    'size' => $file->getClientSize(),
                ]);
            }
        }
        return 0;
    }

    /**
     * 二维码上传
     */
    public function qrcodeAction()
    {
        $key = Request::get('key');
        $path = Request::get('path');
        list($table, $field) = explode('.', $key);
        $model = DB::table('model')->where('table', $table)->first();
        $token = Request::get('x-auth-token');
        return $this->render([
            'model' => $model,
            'token' => $token,
            'key' => $key,
        ], 'attachment.qrcode');
    }
    
    /**
     * 获取文件列表
     */
    public function listAction()
    {
        $id = Request::get('id');
        $rows = AttachmentService::get($id);
        return response()->json($rows);
    }

    /**
     * 获取草稿列表
     */
    public function draftAction()
    {
        $key = Request::get('key');
        $rows = AttachmentService::draft($key);
        return response()->json($rows);
    }
    
    /**
    * 预览文件
    */
    public function showAction()
    {
        $id = Request::get('id');

        $rows = AttachmentService::get($id);

        if (empty($rows)) {
            return $this->error('文件不存在。');
        }

        $image = upload_path($rows[0]['path']);

        if (is_file($image)) {
            Header('Content-type:image/'.$rows[0]['type']);
            return file_get_contents($image);
        } else {
            return $this->error('文件不存在。');
        }
    }

    /**
     * 预览文件
     */
    public function previewAction()
    {
        $id = Request::get('id');
        $file = AttachmentService::get($id)[0];

        $url = '';
        $stream = URL::to('uploads').'/'.$file['path'];
        
        if (in_array($file['type'], array('jpg', 'gif', 'png'))) {
            $view = 'image';
            $url = "javascript:imageBox('{$file['name']}','{$file['name']}','{$file['name']}');";
        } else {
            return $this->back()->with('error', '此格式不支持预览。');
        }

        return $this->display([
            'url' => $url,
            'stream' => $stream,
        ], 'attachment.view');
    }
    
    /**
     * 下载文件
     */
    public function downloadAction()
    {
        $id = Request::get('id');
        if ($id) {
            $row = DB::table('attachment')->where('id', $id)->first();
            $path = upload_path().'/'.$row['path'];
            return response()->download($path, $row['name']);
        }
    }
    
    public function deleteAction()
    {
        $gets = Request::all();
        if ($gets['id']) {
            AttachmentService::remove($gets['id']);
            return 1;
        }
        return 0;
    }
}
