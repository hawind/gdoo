<?php namespace Gdoo\File\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Index\Models\Share;
use Gdoo\File\Models\File;

use Gdoo\Index\Controllers\DefaultController;
use Illuminate\Support\Str;

class FileController extends DefaultController
{
    public $permission = ['index', 'common', 'share', 'receive', 'receivedata', 'folder', 'upload', 'sharing'];

    public $folders = [
            ['id' => 'common', 'name' => '公共云盘'],
            ['id' => 'index',  'name' => '个人云盘'],
            ['id' => 'share',  'name' => '我共享的'],
            ['id' => 'receive','name' => '我收到的'],
        ];

    public function index1Action()
    {
        $rows = [
            ['id' => 'common', 'name' => '公共云盘'],
            ['id' => 'personal', 'name' => '个人云盘'],
            ['id' => 'share', 'name' => '我共享的'],
            ['id' => 'receive', 'name' => '我收到的'],
        ];
        return $this->display([
            'rows' => $rows,
        ]);
    }

    // 新建文件夹
    public function folderAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $model = File::findOrNew($gets['id']);
            $model->fill($gets)->save();
            return $this->json('reload', true);
        }

        $row = File::find($gets['id']);
        $row['parent_id'] = $gets['parent_id'];
        $row['folder']    = $gets['folder'];
        return $this->render([
            'row' => $row,
        ]);
    }

    // 共享操作
    public function sharingAction()
    {
        $gets = Request::all();

        $file = File::find($gets['id']);
        $type = $file['folder'] == 1 ? 'folder' : 'file';

        if (Request::method() == 'POST') {
            $share_data = [
                'source_id'    => $gets['id'],
                'source_type'  => $type,
                'receive_id'   => $gets['receive_id'],
                'receive_name' => $gets['receive_name'],
            ];

            $share = Share::getItem($type, $gets['id']);
            if (empty($share)) {
                Share::addItem($share_data);
            } else {
                Share::editItem($type, $gets['id'], $share_data);
            }

            return $this->json('reload', true);
        }

        $row = Share::getItem($type, $gets['id']);
        $row['id'] = $gets['id'];
        return $this->render([
            'row' => $row,
        ]);
    }

    // 上传文件
    public function uploadAction()
    {
        $parent_id = Request::get('parent_id', 0);
        
        if (Request::method() == 'POST') {
            $file = Request::file('Filedata');
            $mime_type = $this->setting['upload_type'];
            $validator = Validator::make(
                ['file' => $file],
                ['file' => 'mimes:'.$mime_type]
            );

            if ($validator->passes()) {
                $path = 'file/'.date('Y/m');

                $upload_path = upload_path().'/'.$path;

                // 扩展名称
                $extension = $file->getClientOriginalExtension();
                // 附件新名字
                $filename = date('dhis_').Str::random(4).'.'.$extension;
                $filename = mb_strtolower($filename);

                $uploadSuccess = $file->move($upload_path, $filename);
                if ($uploadSuccess) {
                    // 数据库写入
                    $draft = new File;
                    $draft->parent_id = $parent_id;
                    $draft->name = mb_strtolower($file->getClientOriginalName());
                    $draft->path = $path.'/'.$filename;
                    $draft->type = $extension;
                    $draft->size = $file->getClientSize();
                    $draft->save();
                    return $draft->id;
                }
                return 0;
            }
            return 0;
        }
        return $this->render([
            'parent_id' => $parent_id,
        ]);
    }

    // 个人云盘
    public function indexAction()
    {
        $user_id = auth()->id();
        $parent_id = Request::get('parent_id', 0);

        $rows = DB::table('file')
        ->where('parent_id', $parent_id)
        ->where('public', 0)
        ->where('created_id', $user_id)
        ->orderBy('folder', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate();

        // 获取文件夹路径
        $parents = DB::table('file')
        ->where('public', 0)
        ->where('folder', 1)
        ->where('created_id', $user_id)
        ->get();

        $parents = array_nest($parents);

        $breadcrumb = [];
        if ($parent_id) {
            $paths = $parents[$parent_id]['parent'];
            foreach ($paths as $path) {
                $breadcrumb[] = ['name' => $parents[$path]['name'], 'id' => $path];
            }
        }
        
        return $this->display([
            'rows'       => $rows,
            'breadcrumb' => $breadcrumb,
            'parent_id'  => $parent_id,
            'folders'    => $this->folders,
        ]);
    }

    // 共享给我的
    public function receiveAction()
    {
        $user_id = auth()->id();
        $shares = Share::getItemsSourceBy(['folder', 'file'], $user_id)->pluck('created_id');
        $rows = DB::table('user')->whereIn('id', $shares)->paginate();
        return $this->display([
            'rows' => $rows,
        ]);
    }

    // 共享给我的
    public function receivedataAction()
    {
        $user_id = Request::get('user_id');
        $parent_id = Request::get('parent_id');

        // 共享的全部文件和文件夹
        $shares = Share::getItemsCreatedBy(['folder', 'file'], $user_id)
        ->pluck('source_id')
        ->toArray();
 
        $model = DB::table('file')
        ->where('public', 0)
        ->where('created_id', $user_id);

        if ($parent_id) {
            $model->where('parent_id', $parent_id);
        } else {
            $model->whereIn('id', $shares);
        }

        $rows = $model->orderBy('folder', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate();

        // 获取全部文件夹
        $parents = DB::table('file')
        ->where('public', 0)
        ->where('folder', 1)
        ->where('created_id', $user_id)
        ->get();

        // 生成树形数组
        $parents = array_nest($parents);

        $_shares = array_flip($shares);

        $shared = [];

        // 获取已经共享的文件夹和文件编号
        foreach ($parents as $parent) {
            if ($_shares[$parent['id']]) {
                $shared = array_merge($shared, $parent['child']);
            }
        }

        $breadcrumb = [];

        if ($parent_id) {
            // 点开了文件夹
            $paths = $parents[$parent_id]['parent'];
            // 获取文件夹所有父节编号
            foreach ($paths as $path) {
                // 有一种情况共享文件夹是子文件夹，必须排除没有共享的父节点
                if (in_array($path, $shared)) {
                    $breadcrumb[] = ['name' => $parents[$path]['name'], 'id' => $path];
                }
            }
        }

        $user = $row = DB::table('user')->where('id', $user_id)->first();
        return $this->display([
            'rows' => $rows,
            'breadcrumb' => $breadcrumb,
            'parent_id' => $parent_id,
            'user' => $user,
        ]);
    }

    // 我共享的
    public function shareAction()
    {
        $user_id = Request::get('user_id', auth()->id());

        // 共享的全部文件和文件夹
        $shares = Share::getItemsCreatedBy(['folder', 'file'], $user_id)
        ->pluck('source_id')
        ->toArray();
 
        $parent_id = Request::get('parent_id');

        $model = DB::table('file')
        ->where('public', 0)
        ->where('created_id', $user_id);

        if ($parent_id) {
            $model->where('parent_id', $parent_id);
        } else {
            $model->whereIn('id', $shares);
        }

        $rows = $model->orderBy('folder', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate();

        // 获取全部文件夹
        $parents = DB::table('file')
        ->where('public', 0)
        ->where('folder', 1)
        ->where('created_id', $user_id)
        ->get();

        // 生成树形数组
        $parents = array_nest($parents);

        $_shares = array_flip($shares);

        $shared = [];

        // 获取已经共享的文件夹和文件编号
        foreach ($parents as $parent) {
            if ($_shares[$parent['id']]) {
                $shared = array_merge($shared, $parent['child']);
            }
        }

        $breadcrumb = [];

        if ($parent_id) {
            // 点开了文件夹
            $paths = $parents[$parent_id]['parent'];
            // 获取文件夹所有父节编号
            foreach ($paths as $path) {
                // 有一种情况共享文件夹是子文件夹，必须排除没有共享的父节点
                if (in_array($path, $shared)) {
                    $breadcrumb[] = ['name' => $parents[$path]['name'], 'id' => $path];
                }
            }
        }
        
        return $this->display([
            'rows' => $rows,
            'breadcrumb' => $breadcrumb,
            'parent_id' => $parent_id,
        ]);
    }

    // 公共网盘
    public function commonAction()
    {
        $folder_id = Request::get('folder_id', 0);
        $files = DB::table('file')
        ->where('parent_id', $folder_id)
        ->where('folder', 0)
        ->orderBy('created_at', 'desc')
        ->paginate();

        $folders = DB::table('file')
        ->orderBy('created_at', 'desc')
        ->paginate();

        $rows = [
            'name' => '公共网盘', 'type' => '',
        ];

        return $this->display([
            'rows' => $rows,
        ]);
    }

    public function downAction()
    {
        $id = (int) Request::get('id', 0);
        $row = DB::table('file')->where('id', $id)->first();

        if (empty($row)) {
            return $this->error('附件不存在。');
        }

        $name = empty($row['path']) ? 'file/'.$row['name'] : $row['path'].'/'.$row['name'];

        $downfile = upload_path($name);

        if (is_file($downfile)) {
            //打开文件
            $filename = mb_convert_encoding($row['title'], "gbk", "UTF-8");
            $file = fopen($downfile, "r");
            DB::table('download')->where('id', $id)->increment('hits');
            //输入文件标签
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . filesize($downfile));
            Header("Content-Disposition: attachment; filename=" . $filename);
            //输出文件内容
            echo fread($file, filesize($downfile));
            fclose($file);
            exit;
        } else {
            return $this->error('附件文件不存在。');
        }
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = (array)Request::get('id');
            $rows = DB::table('file')->whereIn('parent_id', $id)->get();
            if ($rows->count()) {
                return $this->error('文件夹不为空无法删除。');
            }

            $rows = DB::table('file')->whereIn('id', $id)->get();
            foreach ($rows as $row) {
                $path = 'file/'.$row['path'];
                if (is_file(upload_path($path))) {
                    unlink(upload_path($path));
                }
                DB::table('file')->where('id', $row['id'])->delete();
            }
        }
        return $this->success('index', '删除成功。');
    }
}
