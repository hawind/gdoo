<?php namespace Gdoo\User\Controllers;

use DB;
use Auth;
use Request;

use Gdoo\Index\Controllers\Controller;

use Gdoo\User\Models\User;
use Gdoo\User\Models\Message;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

class MessageController extends Controller
{
    public $permission = ['index', 'create', 'show', 'count', 'status', 'delete'];

    /**
     * 消息列表
     */
    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'user_message',
            'referer' => 1,
            'search' => ['status' => 'unread'],
            'trash_btn' => 0,
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => 1,
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = Message::query();
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);

            $model->where('user_message.read_id', auth()->id());

            if ($query['status']) {
                $status = $query['status'] == 'unread' ? 0 : 1;
                $model->where('user_message.status', $status);
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select']);

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => 1],
            ['action' => 'divider'],
            ['name' => '标记已读', 'icon' => '', 'action' => 'read', 'display' => 1],
            ['name' => '标记未读', 'icon' => '', 'action' => 'unread', 'display' => 1],
        ];
        $header['cols'] = $cols;
        $header['tabs'] = Message::$tabs;
        $header['bys'] = Message::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    /*
     * 消息状态标记
     */
    public function statusAction()
    {
        $id = (array)Request::get('id');
        $type = Request::get('type');

        $rows = DB::table('user_message')
        ->whereIn('id', $id)
        ->where('read_id', auth()->id())
        ->get();

        $status = $type == 'unread' ? 0 : 1;

        foreach ($rows as $row) {
            $row['status']  = $status;
            $row['read_at'] = time();
            DB::table('user_message')->where('id', $row['id'])->update($row);
        }
        return $this->json('操作成功。', true);
    }

    /**
     * 新建提醒
     */
    public function createAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            if (empty($gets['content'])) {
                return $this->json('内容必须填写。');
            }
            DB::table('user_message')->insert($gets);
            return $this->json('发送成功。', true);
        }
        $userId = Request::get('user_id');
        $user = User::find($userId);
        return $this->render([
            'user' => $user,
        ]);
    }

    /**
     * 显示提醒
     */
    public function showAction()
    {
        $id = Request::get('id');
        $row = DB::table('user_message')->find($id);
        if ($row['status'] == 0) {
            DB::table('user_message')
            ->where('id', $id)
            ->update(['status' => 1, 'read_at' => time()]);
        }
        return $this->render([
            'row' => $row,
        ]);
    }

    /**
     * 未读提醒
     */
    public function countAction()
    {
        $count = DB::table('user_message')
        ->where('read_id', Auth::id())
        ->where('status', 0)
        ->count();
        return response()->json($count);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'user_message', 'ids' => $ids]);
        }
    }
}
