<?php namespace Gdoo\File\Controllers;

use Gdoo\Index\Controllers\DefaultController;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\File\Models\InspectReport;

class InspectReportController extends DefaultController
{
    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'file_inspect_report',
            'referer' => 1,
            'search' => [],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '下载',
            'action' => 'download',
            'display' => 1,
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            $items = Grid::dataFilters($rows, $header, function($item) {
                $item['size'] = human_filesize($item['size']);
                return $item;
            });
            return $items->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = InspectReport::$tabs;
        $header['bys'] = InspectReport::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction()
    {
        if (Request::method() == 'POST') {
            $file = Request::file('file');
            $name = Request::get('name');
            $mime_type = $this->setting['upload_type'];
            $validator = Validator::make(
                ['file' => $file, 'name' => $name],
                ['file' => 'mimes:'.$mime_type, 'name' => 'required']
            );
            if ($validator->passes()) {
                $path = 'inspect_report/'.date('Y/m');
                $upload_path = upload_path().'/'.$path;
                // 扩展名称
                $extension = $file->getClientOriginalExtension();
                // 附件新名字
                $filename = date('dhis_').str_random(4).'.'.$extension;
                $filename = mb_strtolower($filename);
                $uploadSuccess = $file->move($upload_path, $filename);
                if ($uploadSuccess) {
                    // 数据库写入
                    $draft = new InspectReport;
                    //$draft->name = mb_strtolower($file->getClientOriginalName());
                    $draft->name = $name;
                    $draft->path = $path.'/'.$filename;
                    $draft->type = $extension;
                    $draft->size = $file->getClientSize();
                    $draft->save();
                    return $this->json('文件上传成功。', true);
                }
            } else {
                return $this->json($validator->errors()->first());
            }
            return $this->json('文件不能为空。');
        }
        return $this->render();
    }

    public function downloadAction()
    {
        $id = (int) Request::get('id', 0);
        $row = DB::table('file_inspect_report')->where('id', $id)->first();
        if (empty($row)) {
            return $this->error('附件不存在。');
        }
        $filename = upload_path($row['path']);
        if (is_file($filename)) {
            // 打开文件
            $name = mb_convert_encoding($row['name'], "gbk", "UTF-8");
            $file = fopen($filename, "r");
            DB::table('file_inspect_report')->where('id', $id)->increment('hits');
            // 输入文件标签
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . filesize($filename));
            Header("Content-Disposition: attachment; filename=" . $name.'.'.$row['type']);
            // 输出文件内容
            echo fread($file, filesize($filename));
            fclose($file);
            exit;
        } else {
            return $this->error('附件文件不存在。');
        }
    }

    public function deleteAction()
    {
        $id = (array)Request::get('id');
        $rows = DB::table('file_inspect_report')->whereIn('id', $id)->get();
        if ($rows->isEmpty()) {
            return $this->json('附件不存在。');
        } else {
            foreach($rows as $row) {
                $path = upload_path($row['path']);
                if (is_file($path)) {
                    unlink($path);
                }
                DB::table('file_inspect_report')->where('id', $row['id'])->delete();
            }
        }
        return $this->json('文件删除成功。', true);
    }
}
