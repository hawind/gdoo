<?php namespace Gdoo\System\Controllers;

use Session;
use Request;
use Validator;
use DB;

use Gdoo\System\Models\Media;

use Gdoo\Index\Controllers\DefaultController;

class MediaController extends DefaultController
{
    public $permission = ['dialog', 'qrcode', 'create', 'delete', 'download', 'folder'];

    /**
     * 二维码上传
     */
    public function qrcodeAction()
    {
        $key = Request::get('key');
        list($table, $field) = explode('.', $key);
        $model = DB::table('model')->where('table', $table)->first();
        $token = Request::get('x-auth-token');
        return $this->render([
            'model' => $model,
            'token' => $token,
            'key'   => $key,
        ], 'qrcode');
    }

    /**
     * 新建文件
     */
    public function createAction()
    {
        $folderId = Request::get('folder_id');
        $file = Request::file('Filedata');
        $rules = [
            'file' => 'mimes:'.$this->setting['upload_type'],
        ];
        $v = Validator::make(['file' => $file], $rules);

        if ($file->isValid() && $v->passes()) {
            // 获取上传uri第一个目录
            $path = 'media/'.date('Y/m');
            $upload_path = upload_path().'/'.$path;
            
            // 文件后缀名
            $extension = $file->getClientOriginalExtension();

            // 文件新名字
            $filename = date('dhis_').str_random(4).'.'.$extension;
            $filename = mb_strtolower($filename);

            $fileTypes = ['image/png', 'image/jpg', 'image/jpeg'];
            $mimeType = $file->getMimeType();

            if ($file->move($upload_path, $filename)) {
                $data = [
                    'name' => mb_strtolower($file->getClientOriginalName()),
                    'path' => $path.'/'.$filename,
                    'type' => $extension,
                    'folder_id' => $folderId,
                    'size' => $file->getClientSize(),
                ];

                if (in_array($mimeType, $fileTypes)) {
                    thumb($upload_path.'/'.$filename, 420, 420);
                    $path = pathinfo($path.'/'.$filename);
                    $thumb = $path['dirname'].'/thumb-420-'.$path['basename'];
                    $data['thumb'] = $thumb;
                }

                $id = Media::insertGetId($data);
                if ($id) {
                    $data['id'] = $id;
                    return $this->json($data, true);
                } else {
                    return $this->json('文件上传失败');
                }
            }
        }
        return $this->json('文件上传失败');
    }
    
    /**
     * 文件对话框
     */
    public function dialogAction()
    {
        if (Request::method() == 'POST') {
            $folder   = Request::get('folder');
            $folderId = Request::get('folder_id');
            $userId = auth()->id();

            $model = Media::where('created_id', $userId);
            if ($folder == 1) {
                $model->whereRaw('folder = 1');
            } else {
                $model->whereRaw('isnull(folder, 0) = 0');
                if ($folderId) {
                    $model->where('folder_id', $folderId);
                }
            }
            $rows = $model->orderBy('id', 'desc')->get()->toArray();
            foreach($rows as &$row) {
                if (in_array($row['type'], ['png', 'jpg', 'jpeg'])) {
                    $file = $row['path'];
                    $path = pathinfo($file);
                    $thumb = $path['dirname'].'/thumb-420-'.$path['basename'];
                    if (!is_file(upload_path().'/'.$thumb)) {
                        thumb(upload_path().'/'.$file, 420, 420);
                    }
                    $row['path'] = $file;
                    $row['thumb'] = $thumb;
                }
            }

            return $this->json($rows, true);
        }
        return $this->render();
    }

    /**
     * 新建文件夹
     */
    public function folderAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $gets['folder'] = 1;
            Media::insertGetId($gets);
            return $this->json('操作成功', true);
        }
        return $this->render();
    }

    /**
     * 下载文件
     */
    public function downloadAction()
    {
        $id = Request::get('id');
        if ($id) {
            $row = Media::where('id', $id)->first();
            $path = upload_path().'/'.$row['path'];
            return response()->download($path, $row['name']);
        }
    }
    
    /**
     * 删除文件
     */
    public function deleteAction()
    {
        $id = (array)Request::get('id');
        if (empty($id)) {
            return $this->json('删除失败');
        }
        
        $rows = Media::whereIn('id', $id)->get();
        foreach ($rows as $row) {
            $file = upload_path().'/'.$row['path'];
            if (is_file($file)) {
                unlink($file);
            }
        }
        Media::whereIn('id', $id)->delete();
        return $this->json('删除成功', true);
    }
}
