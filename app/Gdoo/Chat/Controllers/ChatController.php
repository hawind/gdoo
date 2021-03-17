<?php namespace Gdoo\Chat\Controllers;

use Illuminate\Http\Request;
use URL;
use DB;
use Log;
use Session;
use Config;
use Auth;

use App\Support\JWT;

use Gdoo\Chat\Models\History;
use Gdoo\Chat\Models\Message;

use Gdoo\Chat\Services\ChatService;

use Gdoo\Index\Controllers\Controller;

class ChatController extends Controller
{
    public $user = null;

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            $this->user = $request->user();
            $action = $request->action();

            // 如果没有登录跳转到登录页面
            if (Auth::guest()) {
                if ($action == 'login' || $action == 'start') {
                } else {
                    return redirect("/chat/chat/login");
                }
            } else {
                // 已经登录了访问登录页面
                if ($action == 'login') {
                    return redirect("/chat/chat/index");
                }
            }
            return $next($request);
        });
    }

    public function start() 
    {
        return $this->return_json([
            'title' => $this->setting['title'],
            'auth_id' => (int)$this->user['id'],
        ]);
    }

    public function index() 
    {
        return $this->render([
            'user' => $this->user,
        ]);
    }

    public function getServerURL() 
    {
        return ChatService::getServerURL($this->user);
    }

    public function upload(Request $request) 
    {
        if ($request->method() == 'POST') {
            $user = $this->user;
            $file = $request->file('file');

            $path = 'chat'.date('/Ym/');
            $extension = $file->getClientOriginalExtension();
            $upload_path = upload_path().'/'.$path;

            // 文件新名字
            $filename = date('dhis_').str_random(4).'.'.$extension;
            $filename = mb_strtolower($filename);

            $size = $file->getClientSize();
            $name = mb_strtolower($file->getClientOriginalName());
            $mime = $file->getMimeType();

            if ($file->move($upload_path, $filename)) {
                $data = [
                    'name' => $name,
                    'node' => 'chat',
                    'path' => $path.$filename,
                    'type' => $extension,
                    'key' => 'chat.file',
                    'size' => $size,
                ];
                $insertId = DB::table('attachment')->insertGetId($data);

                $src = url($path.$filename);
                
                if (in_array($extension, ['jpg', 'gif', 'png', 'jpeg'])) {
                    list($picw, $pich, $t) = getimagesize($upload_path.$filename);
                }
                $json = [
                    "adddt" => date('Y-m-d H:i:s'),
                    "comid" => 1,
                    "fileext" => $extension,
                    "filename" => $name,
                    "filepath" => $src,
                    "filesize" => $size,
                    "filesizecn" => human_filesize($size),
                    "filetype" => $mime,
                    "id" => $insertId,
                    "ip" => $request->getClientIp(),
                    "mknum" => "",
                    "optid" => $user['id'],
                    "optname" => $user['name'],
                    "pich" => (int)$pich,
                    "picw" => (int)$picw,
                    "thumbpath" => $src,
                    "valid" => 1,
                    "web" => "Chrome",
                ];
                return json_encode($json);
            }
        }
    }

    public function getHistory() 
    {
        $auth_id = $this->user['id'];
        $json = ChatService::getHistory($auth_id);
        return $this->return_json($json);
    }

    public function getMaxUpload() 
    {
        return $this->return_json(['maxUpload' => $this->setting['upload_max']]);
    }

    public function getGroupUser(Request $request) 
    {
        $gets = $request->all();
        $auth_id = $this->user['id'];
        $json = ChatService::getGroupUser($gets['gid'], $auth_id);
        return $this->return_json($json);
    }

    public function clearRecord(Request $request) 
    {
        $gets = $request->all();
        $auth_id = $this->user['id'];
        ChatService::clearRecord($gets['type'], $gets['gid'], $auth_id, $gets['ids']);
        return $this->return_json('删除成功');
    }

    public function inviteUser(Request $request) 
    {
        $gets = $request->all();
        ChatService::inviteUser($this->user, $gets['gid'], $gets['val']);
        return $this->return_json('邀请成功');
    }

    public function getReceiver(Request $request) 
    {
        $gets = $request->all();
        $auth_id = $this->user['id'];
        $json = ChatService::getReceiver($gets['type'], $gets['gid'], $auth_id);
        return $this->return_json(['receinfor' => $json]);
    }

    public function exitGroup(Request $request) 
    {
        $gets = $request->all();
        $auth_id = $this->user['id'];
        ChatService::exitGroup($gets['gid'], $auth_id);
        return $this->return_json('退出会话成功');
    }

    public function createGroup(Request $request) 
    {
        $gets = $request->all();
        $auth_id = $this->user['id'];
        $group_id = DB::table('chat_group')->insertGetId([
            'name' => $gets['val'],
            'logo' => '/assets/chat/images/group.png',
        ]);
        DB::table('chat_group_user')->insert([
            'group_id' => $group_id,
            'user_id' => $auth_id,
        ]);
        return $this->return_json('创建会话成功');
    }

    public function clearHistory(Request $request) 
    {
        $gets = $request->all();
        $auth_id = $this->user['id'];
        ChatService::clearHistory($gets['type'], $gets['gid'], $auth_id);
        return $this->return_json('退出会话成功');
    }

	public function getDepartmentUserData()
	{
		$roles = DB::table('role')->get()->toNested();
        $departments = DB::table('department')
        ->leftJoin(DB::raw('(select count(id) utotal, department_id
                FROM [user]
                GROUP BY department_id
            ) u
        '), 'u.department_id', '=', 'department.id')
        ->selectRaw('department.*, isnull(u.utotal, 0) as ntotal')
        ->get()->toNested();

        $deptjson = [];
        foreach($departments as $department) {
            $department['stotal'] = 0;

            // 显示部门下的用户数
            $department['ntotal'] = 0;
            foreach($department['child'] as $child) {
                $department['ntotal'] += $departments[$child]['ntotal'];
            }

            $department['pid'] = $department['parent_id'];
            $deptjson[] = $department;
        }

        $users = ChatService::getUser(1, 1);
        $userjson = [];
        foreach($users as $user) {
            $user['pingyin'] = '';
            $user['deptname'] = $departments[$user['department_id']]['name'];
            $user['deptallname'] = $departments[$user['department_id']]['text'];
            $user['ranking'] = $roles[$user['role_id']]['name'];
            $user['face'] = avatar($user['avatar']);
            $userjson[] = $user;
        }

        $groupjson = [];
        $json = [
            'userjson' => $userjson,
			'deptjson' => $deptjson,
			'groupjson' => $groupjson,
        ];
        return $this->return_json($json);
    }
    
    public function login(Request $request) 
    {
        if ($request->method() == 'POST') {
            $gets = $request->all();
            $credentials = [
                'username' => $gets['adminuser'],
                'password' => $gets['adminpass'],
                'status' => 1,
            ];
            if (Auth::attempt($credentials)) {
                return $this->return_json('登录成功');
            } else {
                abort_error('成功失败，请检查用户名或者密码');
            }
        }
        return $this->render();
    }

    public function logout() 
    {
        Auth::logout();
        Session::flush();
        return $this->return_json('登出成功');
    }

    /**
	* 撤回消息功能
	*/
    public function recallMessage(Request $request) 
    {
        $auth_id = $this->user['id'];
        $gets = $request->all();
        $json = ChatService::recallMessage($gets['type'], $auth_id, $gets['gid'], $gets['id']);
        return $this->return_json($json);
    }

    public function sendMessage(Request $request) 
    {
        $gets = $request->all();
        $auth = $this->user;
        $send_id = (int)$auth['id'];
        $receive_id = (int)$gets['gid'];
        $type = $gets['type'];
        $json = ChatService::sendMessage($type, $send_id, $receive_id, $gets);
        return $this->return_json($json);
    }
    
    public function getRecord(Request $request) 
    {
        $auth = $this->user;
        $auth_id = (int)$auth['id'];

        $gid = (int)$request->get('gid');
        $type = $request->get('type');
        $page = $request->get('page');
        $lastdt = $request->get('lastdt');

        $minid = (int)$request->get('minid');

        $roles = DB::table('role')->get()->toNested();
        $departments = DB::table('department')->get()->toNested();

        $json = [
            "nowdt" => time(),
            "servernow" => date('Y-m-d H:i:s'),
        ];

        $receiver = $sender = [];
        if ($type == 'user') {
            $receiver = DB::table('user')
            ->where('id', $gid)
            ->selectRaw('id, name, role_id, department_id, department_id as deptid, avatar')
            ->first();
            $receiver['ranking'] = $roles[$receiver['role_id']]['name'];
            $receiver['unitname'] = $departments[$receiver['department_id']]['text'];
            $receiver['deptname'] = $departments[$receiver['department_id']]['name'];
            $receiver['face'] = avatar($receiver['avatar']);
            $receiver['type'] = 'user';
            $receiver['utotal'] = 0;
            $receiver['gid'] = $receiver['id'];
            $json['receinfor'] = $receiver;
        }
        else if ($type == 'group') {
            $receiver = DB::table('chat_group')
            ->where('id', $gid)
            ->selectRaw('id, name, logo')
            ->first();
            $receiver['face'] = $receiver['logo'];
            $receiver['type'] = 'group';

            // 查询用户数量
            $receiver['utotal'] = DB::table('chat_group_user')
            ->where('group_id', $gid)
            ->count();

            // 查询自己是否在组中
            $receiver['innei'] = DB::table('chat_group_user')
            ->where('group_id', $gid)
            ->where('user_id', $auth_id)
            ->count();

            $receiver['gid'] = $receiver['id'];
            $json['receinfor'] = $receiver;
        }

        if ($page == 0) {
            $sender = DB::table('user')
            ->where('id', $auth_id)
            ->selectRaw('id, name, role_id, department_id, department_id as deptid, avatar')
            ->first();
            $sender['ranking'] = $roles[$sender['role_id']]['name'];
            $sender['unitname'] = $departments[$sender['department_id']]['text'];
            $sender['deptname'] = $departments[$sender['department_id']]['name'];
            $sender['face'] = avatar($sender['avatar']);
            $json['sendinfo'] = $sender;
        }

        $rows = [];
        $unread_total = 0;

        // 获取用户
        if ($type == 'user') {

            // 获取全部未读
            $unread_total = DB::table('chat_message')
            ->whereRaw("(send_id = $gid and receive_id = $auth_id) and type = '$type' and id in(select message_id from chat_message_status where status = 0 and user_id = '$auth_id')")
            ->count();

            $model = DB::table('chat_message as cm')
            ->leftJoin('user', 'user.id', '=', 'cm.send_id')
            ->leftJoin('chat_message_status as cms', 'cm.id', '=', 'cms.message_id')
            ->where('cm.type', $type)
            ->orderBy('cm.id', 'desc');

            $model->whereRaw("((cm.send_id = '$gid' and cm.receive_id = '$auth_id') or (cm.receive_id = '$gid' and cm.send_id = '$auth_id')) and cms.user_id = '$auth_id'");

            // 这里有一点bug，如果只有一条未读只能显示一条
            if ($unread_total > 0) {
                $model->where('cms.status', 0);
            }

            // 获取大于当前时间的记录
            if ($lastdt > 0) {
                $model->where('cm.created_dt', '>', date('Y-m-d H:i:s', $lastdt));
            }

            // 获取小于当前id的记录
            if ($minid > 0) {
                $model->where('cm.id', '<', $minid);
            }

            $messages = $model->selectRaw('
                cm.*,
                cm.send_id as sendid,
                cm.content as cont,
                cm.created_dt as optdt,
                cms.status as zt,
                [user].name as sendname,
                [user].avatar
            ')
            ->limit(10)
            ->get();

            $message_ids = [];
            foreach($messages as $message) {
                $message_ids[] = $message['id'];
                $message['optdt'] = date('Y-m-d H:i:s', strtotime($message['created_dt']));
                $message['face'] = avatar($message['avatar']);
                $rows[] = $message;
                $unread_total--;
            }

            // 设置已读
            DB::table('chat_message_status')
            ->where('user_id', $auth_id)
            ->whereIn('message_id', $message_ids)
            ->update(['status' => 1]);
        }
        // 获取讨论组
        else if ($type == 'group') {

            // 获取全部未读
            $unread_total = DB::table('chat_message')
            ->whereRaw("type = '$type' and receive_id = '$gid' and id in(select message_id from chat_message_status where status = 0 and user_id = '$auth_id')")
            ->count();

            $model = DB::table('chat_message as cm')
            ->leftJoin('user', 'user.id', '=', 'cm.send_id')
            ->leftJoin('chat_message_status as cms', 'cm.id', '=', 'cms.message_id')
            ->where('cm.type', $type)
            ->orderBy('cm.id', 'desc');

            $model->whereRaw("(cm.receive_id = '$gid') and cms.user_id = '$auth_id'");

            // 这里有一点bug，如果只有一条未读只能显示一条
            if ($unread_total > 0) {
               $model->where('cms.status', 0);
            }

            // 获取大于当前时间的记录
            if ($lastdt > 0) {
                $model->where('cm.created_dt', '>', date('Y-m-d H:i:s', $lastdt));
            }

            // 获取小于当前id的记录
            if ($minid > 0) {
                $model->where('cm.id', '<', $minid);
            }
            
            $messages = $model->selectRaw('
                cm.*, 
                cm.send_id as sendid,
                cm.content as cont,
                cm.created_dt as optdt,
                cms.status as zt,
                [user].name as sendname,
                [user].avatar
            ')
            ->limit(10)
            ->get();

            $message_ids = [];
            foreach($messages as $message) {
                $message_ids[] = $message['id'];
                $message['optdt'] = date('Y-m-d H:i:s', strtotime($message['created_dt']));
                $message['face'] = avatar($message['avatar']);
                $rows[] = $message;
                $unread_total--;
            }

            // 设置未读为已读
            DB::table('chat_message_status')
            ->where('group_id', $gid)
            ->where('user_id', $auth_id)
            ->whereIn('message_id', $message_ids)
            ->update(['status' => 1]);
        }

        $rows = ChatService::formatMessage($rows);

        $unread_total = $unread_total < 0 ? 0 : $unread_total;
        $json['wdtotal'] = $unread_total;

        // 设置会话已读
        DB::table('chat_history')
        ->where('send_id', $auth_id)
        ->where('receive_id', $gid)
        ->where('type', $type)
        ->update(['unread_total' => $unread_total]);

        $json['rows'] = $rows;
        return $this->return_json($json);
    }

    public function init(Request $request)
    {
        $auth_id = $this->user['id'];
        $roles = DB::table('role')->get()->toNested();

        $departments = DB::table('department')
        ->leftJoin(DB::raw('(select count(id) utotal, department_id
                FROM [user]
                GROUP BY department_id
            ) u
        '), 'u.department_id', '=', 'department.id')
        ->selectRaw('department.*, isnull(u.utotal, 0) as ntotal')
        ->get()->toNested();

        $deptjson = [];
        foreach($departments as $department) {
            $department['stotal'] = 0;

            // 显示部门下的用户数
            $department['ntotal'] = 0;
            foreach($department['child'] as $child) {
                $department['ntotal'] += $departments[$child]['ntotal'];
            }

            $department['pid'] = $department['parent_id'];
            $deptjson[] = $department;
        }

        $users = ChatService::getUser(1, 1);
        $userjson = [];
        foreach($users as $user) {
            $user['deptname'] = $departments[$user['department_id']]['name'];
            $user['deptallname'] = $departments[$user['department_id']]['text'];
            $user['ranking'] = $roles[$user['role_id']]['name'];
            $user['face'] = avatar($user['avatar']);
            $userjson[] = $user;
        }

        // 获取组
        $groups = ChatService::getGroup($auth_id);
        $groupjson = [];
        foreach ($groups as $group) {
            $group['deptid'] = $group['department_id'];
            $groupjson[] = $group;
        }

        $agentjson = array(
            array('id' => '1', 
            'name' => 'Gdoo Team', 
            'url' => 'link', 
            'face' => 'images/logo.png', 
            'num' => 'xinhu', 
            'pid' => '0', 
            'iconfont' => 'cf-c90', 
            'iconcolor' => '#1ABC9C', 
            'types' => '官网(1)', 
            'urlpc' => 'http://www.gdoo.net', 
            'urlm' => NULL, 
            'titles' => '', 
            'menu' => array(
                array('pid' => '0', 'mid' => '1', 'id' => '18', 'name' => '最新信息', 'type' => '0', 'url' => 'new', 'num' => NULL, 'color' => NULL, 'receid' => NULL, 'submenu' => array()), 
                array('pid' => '0', 'mid' => '1', 'id' => '89', 'name' => '打开官网', 'type' => '1', 'url' => 'http://www.gdoo.net', 'num' => NULL, 'color' => NULL, 'receid' => NULL, 'submenu' => array(),), 
                array('pid' => '0', 'mid' => '1', 'id' => '19', 'name' => '＋建议反馈', 'type' => '1', 'url' => 'http://www.gdoo.net/fankui.html', 'num' => NULL, 'color' => NULL, 'receid' => NULL, 'submenu' => array(),),
            ), 
            'stotal' => 0, 
            'totals' => 0
            )
        );
        $agentjson = [];

        $historyjson = ChatService::getHistory($auth_id);

        $json = [
            'deptjson' => $deptjson,
            'userjson' => $userjson,
            'groupjson' => $groupjson,
            'agentjson' => $agentjson,
            'historyjson' => $historyjson,
            "modearr" => [],
            "config" => [
                "recid" => "gdoo",
                "title" => "Gdoo",
                "chehui" => 5,
                "wsurl" => env('REALTIME_URL'),
            ],
            "loaddt" => date('Y-m-d H:i:s'),
            "ip" => $request->getClientIp(),
            "editpass" => 1,
            "companyinfo" => [
                "id" => "1",
                "logo" => "images/logo.png",
                "name" => "Gdoo Team",
                "nameen" =>  null,
                "oaname" => null,
                "oanemes" => null,
                "tel" => "028-123456",
                "fax" => "028-123456",
                "pid" => "0",
                "sort" => "0",
                "fuzeid" => "5",
                "fuzename" => "乐风",
                "address" =>  "软件园",
                "city" => "眉山",
                "num" =>  null,
                "comid" => "0"
            ]
        ];
        return $this->return_json($json);
    }

    public function return_json($data, $success = true, $code = 200, $msg = '') {
        $json = [
            'data' => $data,
            'success' => $success,
            'code' => $code,
            'msg' => $msg,
        ];
        return $json;
    }
}
