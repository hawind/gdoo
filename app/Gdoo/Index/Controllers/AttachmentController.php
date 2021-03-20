<?php namespace Gdoo\Index\Controllers;

use Session;
use Request;
use Validator;
use DB;

use Gdoo\Index\Services\AttachmentService;
use Illuminate\Support\Str;
use URL;

class AttachmentController extends DefaultController
{
    public $permission = [
        'list',
        'preview',
        'create',
        'upload',
        'delete',
        'download',
        'show',
        'draft',
        'qrcode'
    ];

    /**
     * 上传文件
     */
    public function upload()
    {
        if (Request::method() == 'POST') {
            $file = Request::file('file');

            $upload_type = explode(',', $this->setting['upload_type']);
        
            if ($file->isValid()) {

                // 文件后缀名
                $extension = $file->getClientOriginalExtension();

                // 判断文件类型
                if (!in_array($extension, $upload_type)) {
                    return;
                }

                // 获取上传uri第一个目录
                $table = Request::get('table');
                $field = Request::get('field');
                $node = Request::get('path', 'default');
                $path = $node.date('/Ym/');

                $upload_path = upload_path().'/'.$path;
                
                // 文件新名字
                $filename = date('dhis_').Str::random(4).'.'.$extension;
                $filename = mb_strtolower($filename);
                $filesize = $file->getSize();

                if ($file->move($upload_path, $filename)) {
                    $data = [
                        'name' => mb_strtolower($file->getClientOriginalName()),
                        'table' => $table,
                        'field' => $field,
                        'path' => $path.$filename,
                        'type' => $extension,
                        'size' => $filesize,
                    ];
                    $insertId = DB::table('attachment')->insertGetId($data);
                    $data['id'] = $insertId;
                    $data['success'] = true;
                    return json_encode($data);
                }
            }
        }
        $query = Request::all();
        $SERVER_URL = url("index/attachment/upload", $query);
        return $this->render([
            'SERVER_URL' => $SERVER_URL,
            'key' => $query['table'].'_'.$query['field'],
        ]);
    }
    
    /**
     * 获取文件列表
     */
    public function list()
    {
        $id = Request::get('id');
        $rows = AttachmentService::get($id);
        return $rows;
    }

    /**
     * 获取草稿列表
     */
    public function draft()
    {
        $key = Request::get('key');
        $rows = AttachmentService::draft($key);
        return $rows;
    }
    
    /**
    * 预览文件
    */
    public function show()
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
    public function preview()
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
    public function download()
    {
        $id = Request::get('id');
        if ($id) {
            $row = DB::table('attachment')->where('id', $id)->first();
            $path = upload_path().'/'.$row['path'];
            return response()->download($path, $row['name']);
        }
    }
    
    public function delete()
    {
        $gets = Request::all();
        if ($gets['id']) {
            AttachmentService::remove($gets['id']);
            return 1;
        }
        return 0;
    }
}
