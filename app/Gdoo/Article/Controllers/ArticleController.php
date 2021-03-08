<?php namespace Gdoo\Article\Controllers;

use Arr;
use DB;
use Auth;
use Request;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\User\Models\User;
use Gdoo\User\Models\Role;
use Gdoo\Article\Models\Article;

use Gdoo\Index\Services\AttachmentService;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\User\Models\Department;
use Gdoo\User\Services\UserService;

class ArticleController extends DefaultController
{
    public $permission = [];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'article',
            'referer' => 1,
            'search' => ['by' => '', 'tab' => 'all'],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ],[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
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

            if ($this->access['index'] < 4) {
                // 这里需要包括创建者权限
                $model->permission('receive_id', null, false, true, false, 'created_id');
            }
    
            // 查询是否已经阅读
            $reader = function ($q) {
                $q->selectRaw('1')
                ->from('article_reader')
                ->whereRaw('article_reader.article_id = article.id')
                ->where('article_reader.created_id', auth()->id());
            };
            if ($query['tab'] == 'done') {
                $model->whereExists($reader);
            }
            if ($query['tab'] == 'unread') {
                $model->whereNotExists($reader);
            }
            
            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);

            $header['cols'] = $cols;
            $header['tabs'] = Article::$tabs;
            $header['bys'] = Article::$bys;
            $header['js'] = Grid::js($header);

            $items = Grid::dataFilters($rows, $header, function($item) {
                return $item;
            });
            return $items;
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Article::$tabs;
        $header['bys'] = Article::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'article', 'id' => $id]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function showAction()
    {
        $id = (int)Request::get('id');

        $res = Article::withAt('user', ['id','username','name'])
        ->where('id', $id)->first();

        // 发布人
        $from = DB::table('user')->where('id', $res['created_id'])->first();

        // 附件
        $attachment = AttachmentService::show($res['attachment']);

        // 已读记录
        $reads = DB::table('article_reader')->where('article_id', $id)->get();
        $reads = array_by($reads, 'created_id');

        // 更新阅读记录
        if (empty($reads[Auth::id()])) {
            DB::table('article_reader')->insert([
                'article_id' => $id,
            ]);
        }

        // 返回json
        if (Request::wantsJson()) {
            return $res->toJson();
        }

        $form = Form::make(['code' => 'article', 'id' => $id, 'action' => 'show']);
        return $this->display([
            'attachment' => $attachment,
            'res' => $res,
            'from' => $from,
            'form' => $form,
        ]);
    }

    /**
     * 阅读记录
     */
    public function readerAction()
    {
        $id = Request::get('id', 0);

        // 取得当前项目阅读情况
        $reads = DB::table('article_reader')->where('article_id', $id)->get();
        $reads = array_by($reads, 'created_id');
        $row = DB::table('article')->where('id', $id)->first();
        $scopes = UserService::getDRU($row['receive_id']);
        if ($scopes->count()) {
            $rows = [];
            $departments = Department::orderBy('lft', 'asc')->pluck('name', 'id');
            foreach ($scopes as $scope) {
                $read = isset($reads[$scope['id']]) ? 1 : 0;
                $rows['total'][$read]++;
                $rows['data'][] = [
                    'read' => $read,
                    'department_id' => $scope['department_id'],
                    'department' => $departments[$scope['department_id']],
                    'name' => $scope['name'],
                    'created_at' => $reads[$scope['id']]['created_at'],
                ];
            }

            $rows['data'] = Arr::sort($rows['data'], function ($value) {
                return $value['created_at'];
            });
        }

        return $this->render([
            'rows' => $rows,
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'article', 'ids' => $ids]);
        }
    }
}
