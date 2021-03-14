<?php namespace Gdoo\Promotion\Controllers;

use Request;
use Validator;
use URL;
use DB;
use ZipArchive;
use Session;

use Gdoo\Promotion\Models\Promotion;
use Gdoo\Promotion\Models\PromotionMaterial as Material;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\DefaultController;
use Illuminate\Support\Str;

class MaterialController extends DefaultController
{
    public $permission = ['detail', 'dialog', 'store', 'archive', 'download'];
    
    public function indexAction()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'promotion_material',
            'referer' => 1,
            'search' => ['by' => '-1'],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);

            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            $model->leftJoin('promotion_material_file as f', 'f.material_id', '=', 'promotion_material.id');

            $model->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if ($query['by'] >= 0) {
                $model->where('promotion_material.status', $query['by']);
            }

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $header['select'][] = 'promotion_material.promotion_id';
            $header['select'][] = 'f.path';
            $header['select'][] = 'f.created_by';
            $header['select'][] = 'f.created_at';

            $model->select($header['select']);

            $rows = $model->paginate($query['limit'])->appends($query);

            return Grid::dataFilters($rows, $header, function($item) {
                return $item;
            });
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Material::$tabs;
        $header['bys'] = Material::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function detailAction()
    {
        $search = search_form([
            'status' => '',
            'promotion_id' => 0,
        ]);

        $query = $search['query'];

        $promotion = Promotion::where('id', $query['promotion_id'])->first();

        $model = Material::leftJoin('promotion', 'promotion.id', '=', 'promotion_material.promotion_id')
        ->where('promotion_material.promotion_id', $query['promotion_id']);

        if (is_numeric($query['status'])) {
            $model->where('promotion_material.status', $query['status']);
        }

        $rows = $model->orderBy('promotion_material.id', 'desc')
        ->get(['promotion_material.*','promotion.sn'])
        ->toArray();

        $items = [];
        $audits = [];

        foreach ($rows as $i => $row) {
            $files = DB::table('promotion_material_file')->where('material_id', $row['id'])->get();
            $images = [];
            foreach ($files as $file) {
                $path_file = upload_path().'/'.$file['path'];
                if (is_file($path_file)) {
                    $images[] = ['url' => url('uploads/'.$file['path']), 'name' => ''];
                }
            }
            $audits[$row['status']] ++;
            $items[$row['id']] = $images;
            $rows[$i]['images'] = $images;
        }

        $changes = [
            0 => ['btn' => 'default', 'text' => '未审核'],
            1 => ['btn' => 'success', 'text' => '已审合格'],
            2 => ['btn' => 'danger', 'text' => '已审不合格'],
        ];

        $tabs = [
            'name' => 'status',
            'items' => [
                ['id' => '', 'name' => '全部'],
                ['id' => 0, 'name' => '未审核'],
                ['id' => 1, 'name' => '已审合格'],
                ['id' => 2, 'name' => '已审不合格']
            ]
        ];

        return $this->display([
            'tabs' => $tabs,
            'items' => $items,
            'rows' => $rows,
            'audits' => $audits,
            'promotion' => $promotion,
            'changes' => $changes,
            'search' => $search,
        ]);
    }

    public function dialogAction()
    {
        $user = auth()->user();

        $customer_id = $user->customer->id;

        $rows = Promotion::with('datas')->where('material_id', '!=', 2)
        ->where('status', 0)
        ->where('customer_id', $customer_id)
        ->where('number', '!=', '')
        ->get(['id', 'number as text','data_4', 'data_19', 'start_at', 'end_at']);

        foreach ($rows as &$row) {
            $row->products = $row->datas->implode('product_name', ',');
        }
        
        return $this->json($rows, true);
    }
    
    public function showAction()
    {
        $id = Request::get('id');
        $row = Material::with('promotion')
        ->where('id', $id)
        ->first();

        // 获取核销图片
        $_files = explode(',', $row['files']);
        $files = DB::table('promotion_material_file')->whereIn('id', $_files)->get();

        foreach ($files as $file) {
            $file = upload_path().'/'.$file['path'].'/'.$file['file'];
            // 生成缩略图
            thumb($file, 80, 80);
        }

        $row['images'] = $files;
        $row['upload_url'] = URL::to('/uploads');

        if (Request::wantsJson()) {
            return $row;
        }

        return $this->render([
            'row' => $row,
        ]);
    }

    public function auditAction()
    {
        $id = (array)Request::get('id');
        $status = Request::get('status');
        $rows = DB::table('promotion_material')
        ->whereIn('id', $id)->get()->toArray();

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $row['status'] = $status;
                DB::table('promotion_material')->where('id', $row['id'])->update($row);
            }
        }
        return $this->json('操作成功。', true);
    }

    public function archiveAction()
    {
        $id = (array)Request::get('id');

        $rows = DB::table('promotion_material')
        ->whereIn('id', $id)->get();

        $zip_file = upload_path('promotion/material/archive.zip');
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($rows as $row) {
            $files = DB::table('promotion_material_file')->where('material_id', $row['id'])->get();
            foreach ($files as $i => $file) {
                $path_file = upload_path().'/'.$file['path'];
                if (is_file($path_file)) {
                    $zip->addFile($path_file, $row['name'].'_'.$row['location'].'_'.$i.'.'.$file['type']);
                }
            }
        }
        $zip->close();

        return $this->json('', true);
    }

    public function downloadAction()
    {
        $sn = Request::get('sn');
        if(empty($sn)) {
            return $this->json('促销编号不能为空。');
        }
        return response()->download(upload_path('promotion/material/archive.zip'), $sn.'_'.date('Y-m-d').'.zip');
    }

    public function storeAction()
    {
        // 上传文件
        if (Request::method() == 'POST') {
            $gets = Request::all();

            $promotion = DB::table('promotion')
            ->where('id', $gets['promotion_id'])
            ->first();

            if (empty($promotion)) {
                return $this->json('促销编号不能为空。');
            }

            DB::beginTransaction();
            try {
                $material_id = DB::table('promotion_material')->insertGetId($gets);
                $images = Request::file('images');
                $path = 'promotion/material/'.date('Y/m');
                $upload_path = upload_path().'/'.$path;

                $rows = [];
                foreach ($images as $image) {
                    if ($image->isValid()) {
                        // 文件后缀名
                        $extension = $image->getClientOriginalExtension();
                        // 文件新名字
                        $filename = date('dhis_').Str::random(4).'.'.$extension;
                        $filename = mb_strtolower($filename);
                        if ($image->move($upload_path, $filename)) {

                            // 修改图片大小
                            imageResize($upload_path.'/'.$filename, 1024, 1024);

                            $rows[] = DB::table('promotion_material_file')->insertGetId([
                                'material_id' => $material_id,
                                'path' => $path. '/'. $filename,
                                'name' => $filename,
                                'type' => $extension,
                                'status' => 1,
                                'size' => $image->getClientSize(),
                            ]);
                        }
                    }
                }
                DB::commit();
                return $this->json('照片保存成功。', true);

            } catch(\Exception $e) {
                DB::rollBack();
                return $this->json($e->getMessage());
            }
        }
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $var1 = (array)Request::get('id');
            $ids = [];
            foreach($var1 as $id) {
                $ids[] = floatval($id);
            }
            $rows = Material::whereIn('id', $ids)->get();
            foreach ($rows as $row) {
                $files = DB::table('promotion_material_file')->where('material_id', $row['id'])->pluck('id');
                attachment_delete('promotion_material_file', $files);
                $row->delete();
            }
            return $this->json('促销核销删除成功。', true);
        }
    }
}
