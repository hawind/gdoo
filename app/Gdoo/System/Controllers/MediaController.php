<?php namespace Gdoo\System\Controllers;

use Session;
use Request;
use Validator;
use DB;

use Gdoo\System\Models\Media;

use Gdoo\Index\Controllers\DefaultController;
use Illuminate\Support\Str;

class MediaController extends DefaultController
{
    public $permission = ['dialog', 'create', 'delete', 'download', 'folder'];

    public function create()
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
            $upload_path = upload_path();
            $file_path = $upload_path.'/'.$path;
            
            // 文件后缀名
            $extension = $file->getClientOriginalExtension();

            // 文件新名字
            $filename = date('dhis_').Str::random(4).'.'.$extension;
            $filename = mb_strtolower($filename);

            $fileTypes = ['image/png', 'image/jpg', 'image/jpeg'];
            $mimeType = $file->getMimeType();
            $filesize = $file->getSize();

            if ($file->move($file_path, $filename)) {
                $data = [
                    'name' => mb_strtolower($file->getClientOriginalName()),
                    'path' => $path.'/'.$filename,
                    'type' => $extension,
                    'folder_id' => $folderId,
                    'size' => $filesize,
                ];

                if (in_array($mimeType, $fileTypes)) {
                    // 生成缩略图，图片小于750px就不生成了
                    $thumb = $path. '/thumb_' . $filename;
                    if (image_thumb($file_path.'/'.$filename, $upload_path.'/'.$thumb, 750)) {
                        $data['thumb'] = $thumb;
                    } else {
                        $data['thumb'] = $path.'/'.$filename;
                    }
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
    
    public function dialog()
    {
        if (Request::method() == 'POST') {
            $folder = Request::get('folder');
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
                    if (empty($row['thumb'])) {
                        $row['thumb'] = $row['path'];
                    }
                }
            }
            return $this->json($rows, true);
        }
        return $this->render();
    }

    /**
     * 新建文件夹
     */
    public function folder()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            if (empty($gets['name'])) {
                return $this->json('名称不能为空。');
            }
            $model = Media::findOrNew($gets['id']);
            $model->folder = 1;
            $model->name = $gets['name'];
            $model->save();
            return $this->json('操作成功', true);
        }
        $folder = Media::where('id', $gets['id'])->first();
        return $this->render(['folder' => $folder]);
    }

    /**
     * 下载文件
     */
    public function download()
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
    public function delete()
    {
        $id = (array)Request::get('id');
        $folder = Request::get('folder');

        if (empty($id)) {
            return $this->json('删除失败。');
        }

        // 判断删除的文件夹是否有文件
        if ($folder) {
            $count = Media::whereIn('folder_id', $id)->count();
            if ($count) {
                return $this->json('文件夹不为空，删除失败。');
            }
        }
        
        $rows = Media::whereIn('id', $id)->get();
        foreach ($rows as $row) {
            // 删除原文件
            $file = upload_path().'/'.$row['path'];
            if (is_file($file)) {
                unlink($file);
            }
            // 删除缩略图
            if ($row['thumb']) {
                $thumb = upload_path().'/'.$row['thumb'];
                if (is_file($thumb)) {
                    unlink($thumb);
                }
            }
        }
        Media::whereIn('id', $id)->delete();

        return $this->json('删除成功。', true);
    }
}
