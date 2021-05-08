<?php namespace Gdoo\Forum\Controllers;

use DB;
use Request;

use Gdoo\Index\Controllers\DefaultController;
use Symfony\Component\Console\Input\Input;

class CategoryController extends DefaultController
{
    public function index()
    {
        // 更新排序
        if (Request::method() == 'POST') {
            $gets = Request::all();
            foreach ($gets as $k => $v) {
                $data['sort'] = $v;
                DB::table('forum')->where('id', $k)->update($data);
            }
        }

        $rows = DB::table('forum')
        ->where('state', '1')
        ->get(['id', 'name']);
        
        if (Request::get('data_type') == 'json') {
            return $this->json($rows, true);
        }
        return $this->display([
            'rows' => $rows,
        ]);
    }

    public function add()
    {
        $id = (int)Request::get('id');

        if (Request::method() == 'POST') {
            $gets = Request::all();
            if (empty($gets['name'])) {
                return $this->error('类别名称必须填写。');
            }
            unset($gets['past_parent_id']);

            if ($gets['id'] > 0) {
                DB::table('forum')->where('id', $gets['id'])->update($gets);
            } else {
                DB::table('forum')->insert($gets);
            }
            return $this->success('index', '类别更新成功。');
        }
        $row = DB::table('forum')->where('id', $id)->first();
        return $this->display(array(
            'row' => $row,
        ));
    }

    public function delete()
    {
        if ($id = Request::get('id')) {
            DB::table('forum')->where('id', $id)->delete();
            DB::table('forum_post')->where('forum_id', $id)->delete();
            return $this->success('index', '类别删除成功。');
        }
        return $this->error('类别删除失败。');
    }
}
