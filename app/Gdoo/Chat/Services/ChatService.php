<?php namespace Gdoo\Chat\Services;

use DB;
use URL;

use Gdoo\Chat\Models\History;
use Gdoo\Chat\Models\GroupUser;
use Gdoo\Chat\Models\Message;

class ChatService
{
    /**
     * 构建ws连接url
     *
     * @param array $user
     * @access public
     * @return array
     */
    public static function getServerURL($user) {
        $key = env('REALTIME_KEY');
        $url = env('REALTIME_URL');
        $timestamp = time();
        $nonce = rand(10000, 99999);
        $signature = hash_hmac('sha256', $key.$timestamp.$nonce, $key);
        $query = [
            'signature' => $signature,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'user_id' => $user['id'],
            'user_name' => $user['name'],
        ];
        return ['url' => $url. '?'. http_build_query($query)];
    }

    /**
     * 获取组
     *
     * @param int $admin_id
     * @access public
     * @return array
     */
    public static function getGroup($auth_id = 0, $admin_id = 0)
	{
        static $rows = null;
        if ($rows == null) {
            $model = DB::table('chat_group as cg')
            ->leftJoin('chat_group_user', 'chat_group_user.group_id', '=', 'cg.id');

            if ($auth_id > 0) {
                $model->where('chat_group_user.user_id', $auth_id);
            }

            if ($admin_id > 0) {
                $model->whereRaw(db_instr('admin_ids', $admin_id));
            }

            $model->leftJoin(DB::raw('(select count(id) utotal, group_id
                    FROM chat_group_user
                    GROUP BY group_id
                ) cgu
            '), 'cg.id', '=', 'cgu.group_id')
            ->selectRaw('cg.*, cgu.utotal, cg.logo as face');

            $rows = $model->get()->keyBy('id');
        }
		return $rows;
    }

    /**
     * 撤回消息
     *
     * @param int $type
     * @param int $auth_id
     * @param int $group_id
     * @param int $id
     * @access public
     * @return array
     */
    public static function recallMessage($type, $auth_id, $group_id, $id)
	{
        $chehui = 5;
		if ($chehui <= 0) {
            abort_error('没有开启此功能');
        }

        $message = DB::table('chat_message')->where('id', $id)->first();
        if(!$message) {
            abort_error('记录不存在了');
        }
        
		$outtime = time() - strtotime($message['created_dt']);
		if($outtime > $chehui * 60) {
            abort_error('已经超过'.$chehui.'分钟无法撤回');
        }

        if ($type == 'user') {
            $receiver = [$auth_id, (int)$message['receive_id']];
        } elseif ($type == 'group') {
            $user_ids = DB::table('chat_group_user')->where('group_id', $group_id)->pluck('user_id');
            foreach($user_ids as $user_id) {
                $receiver[] = (int)$user_id;
            }
        }

        $msg = '已撤回';
        DB::table('chat_message')->where('id', $message['id'])->update([
            'file_id' => 0,
            'content' => $msg,
        ]);

        $pushData = [
            'send_id' => $auth_id,
            'content' => $msg,
            'event' => 'recallMessage',
            'receive_ids' => $receiver,
			'message_id' => (int)$message['id'],
        ];

        $push = new PushService();
        $push->send($pushData);

		return $pushData;
    }

    /**
     * 格式化消息
     *
     * @param array $rows
     * @access public
     * @return array
     */
    public static function formatMessage($rows)
	{
		$file_ids = [];
        foreach($rows as $row) {
            if ($row['file_id'] > 0) {
                $file_ids[] = $row['file_id'];
            }
		}
        $imgext = ['gif','png','jpg','jpeg','bmp'];

		if (count($file_ids) > 0) {

            $model = DB::table('attachment');
			$farr = [];
            $files = $model->whereIn('id', $file_ids)->get();
            foreach ($files as $file)
            $farr[$file['id']] = $file;

            if ($farr) {
                foreach ($rows as $k => $row) {

                    $frs = [];
                    $fid = $row['file_id'];
                    
                    if (isset($farr[$fid])) {
                        $frs = $farr[$fid];
                        $frs['fileext'] = $frs['type'];
                        $frs['fileid'] = $fid;
                    }
                    
                    if ($frs) {
                        $type = $frs['type'];
                        $path = $frs['path'];
                        $boc = false;

                        if (substr($path, 0, 4) == 'http') {
                            $boc = true;
                        } else {
                            if (is_file(upload_path().'/'.$path)) {
                                $path = url('uploads/'.$path);
                                $frs['thumbpath'] = $path;
                                $frs['filepath'] = $path;
                                $frs['filesize'] = $frs['size'];
                                $frs['filesizecn'] = human_filesize($frs['size']);
                                $frs['filename'] = $frs['name'];
                                $boc = true;
                            }
                        }

                        if ($boc) {
                            if (in_array($type, $imgext)) {
                                // $frs['thumbpath'] = $fobj->getthumbpath($frs);
                                $rows[$k]['cont'] = '<img fid="'.$fid.'" src="'.$path.'">';
                            }
                            $rows[$k]['filers'] = $frs;
                        } else {
                            $rows[$k]['fileid']	= 0;
                        }
                    }
                }
            }
        }
		return $rows;
	}

    /**
     * 获取用户
     *
     * @param int $user_id
     * @param int $group_id
     * @access public
     * @return array
     */
    public static function getUser($status = 1, $group_id = 1)
	{
        static $rows = null;
        if ($rows == null) {
            $model = DB::table('user');
            if (is_numeric($status)) {
                $model->where('status', $status);
            }
            if (is_numeric($group_id)) {
                $model->where('group_id', $group_id);
            }

            $model->selectRaw("''as pingyin,tel,(CASE WHEN (gender = 1) THEN '男' ELSE '女' END) as sex, email, id, name, phone, phone as mobile, role_id, department_id, department_id as deptid");
            $rows = $model->get()->keyBy('id');
        }
		return $rows;
    }

    /**
    * 邀请用户到组
    *
    * @param string $ids
    * @param int $group_id
	*/
	public static function inviteUser($auth, $group_id, $ids)
	{
        $user_ids = explode(',', $ids);
        if (count($user_ids) > 0) {
            foreach($user_ids as $index => $user_id) {
                $model = GroupUser::firstOrNew([
                    'user_id' => $user_id,
                    'group_id' => $group_id,
                ]);
                if ($model->exists == true) {
                    unset($user_ids[$index]);
                }
                $model->save();
            }
            if (count($user_ids) > 0) {
                $users = DB::table('user')->whereIn('id', $user_ids)->get()->pluck('name')->implode(',');
                $gets = [
                    "cont" => $auth['name'].'邀请['.$users.']加入本会话',
                    "sendid" => $auth['id'],
                    "receid" => $group_id,
                    "type" => 'group',
                    "optdt" => date('Y-m-d H:i:s'),
                    "zt" => 0,
                    "fileid" => '',
                    "msgid" => "",
                    "gid" => $group_id,
                    "nuid" => time(),
                ];
                $json = ChatService::sendMessage('group', $auth['id'], $group_id, $gets);
            }
        }
    }
    
    /**
    * 退出用户组
    *
    * @param int $group_id
    * @param int $auth_id
    * @access public
    * @return void
	*/
	public static function exitGroup($group_id, $auth_id)
	{
        // 删除用户组权限
        GroupUser::where('group_id', $group_id)
        ->where('user_id', $auth_id)
        ->delete();
        
        // 删除组聊天记录
        DB::table('chat_message_status')
        ->where('type', 'group')
        ->where('group_id', $group_id)
        ->where('user_id', $auth_id)
        ->delete();

        // 删除会话记录
        DB::table('chat_history')
        ->where('type', 'group')
        ->where('receive_id', $group_id)
        ->where('send_id', $auth_id)
        ->delete();
    }

    /**
    * 删除服务器上记录
    *
    * @param string $type
    * @param int $group_id
    * @param int $auth_id
    * @param string $ids
    * @param int $day
    * @access public
    * @return void
	*/
	public static function clearRecord($type, $group_id, $auth_id, $ids = [], $day = 0)
	{
        DB::beginTransaction();
        try {
            // 获取聊天记录数量
            $model = DB::table('chat_message_status')
            ->where('type', $type);
            if (count($ids) > 0) {
                $model->whereIn('message_id', $ids);
            }
            if ($type == 'group') {
                $model->where('group_id', $group_id);
            } else if ($type == 'user') {
                $model->whereRaw("((group_id = '$group_id' and user_id = '$auth_id') or (group_id = '$auth_id' and user_id = '$group_id'))");
            }
            $messages = $model->groupBy('message_id')
            ->selectRaw('message_id, count(id) as count')
            ->get();

            $model = DB::table('chat_message_status')
            ->where('group_id', $group_id)
            ->where('user_id', $auth_id);
            if (count($ids) > 0) {
                $model->whereIn('message_id', $ids);
            }
            $model->delete();

            // 获取回话的最后消息id
            $last_message_id = DB::table('chat_message_status')
            ->where('type', $type)
            ->where('group_id', $group_id)
            ->where('user_id', $auth_id)
            ->orderBy('message_id', 'desc')
            ->value('message_id');

            // 更新回话的最后消息id
            DB::table('chat_history')
            ->where('type', $type)
            ->where('receive_id', $group_id)
            ->where('send_id', $auth_id)
            ->update(['last_message_id' => (int)$last_message_id]);

            // 删除消息状统计为1的数据
            foreach($messages as $message) {
                if ($message['count'] == 1) {
                    DB::table('chat_message')->where('id', $message['message_id'])->delete();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            abort_error($e->getMessage());
        }
    }
    
    /**
    * 删除会话记录
    *
    * @param string $type
    * @param int $group_id
    * @param int $auth_id
    * @access public
    * @return void
	*/
	public static function clearHistory($type, $group_id, $auth_id)
	{
        DB::beginTransaction();
        try {
            // 获取聊天记录数量
            $model = DB::table('chat_message_status')
            ->where('type', $type);

            if ($type == 'group') {
                $model->where('group_id', $group_id);
            } else if ($type == 'user') {
                $model->whereRaw("((group_id = '$group_id' and user_id = '$auth_id') or (group_id = '$auth_id' and user_id = '$group_id'))");
            }

            $messages = $model->groupBy('message_id')
            ->selectRaw('message_id, count(id) as count')
            ->get();

            // 删除聊天记录
            $model = DB::table('chat_message_status')
            ->where('type', $type)
            ->where('group_id', $group_id)
            ->where('user_id', $auth_id)
            ->delete();

            // 删除会话记录
            DB::table('chat_history')
            ->where('type', $type)
            ->where('receive_id', $group_id)
            ->where('send_id', $auth_id)
            ->delete();

            // 删除消息状统计为1的数据
            foreach($messages as $message) {
                if ($message['count'] == 1) {
                    DB::table('chat_message')->where('id', $message['message_id'])->delete();
                }
            }
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            abort_error($e->getMessage());
        }
	}

    /**
     * 获取组和用户
     *
     * @param int $group_id
     * @param int $auth_id
     * @access public
     * @return array
     */
    public static function getGroupUser($group_id, $auth_id)
	{
        $group = DB::table('chat_group')->where('id', $group_id)->first();
        $group['face'] = $group['logo'];
        $group['deptid'] = $group['department_id'];

        $group['utotal'] = DB::table('chat_group_user')->where('group_id', $group_id)->count();
        $group['innei'] = DB::table('chat_group_user')->where('user_id', $auth_id)->count();

        $rows = DB::table('chat_group_user as cgu')
        ->leftJoin('user', 'user.id', 'cgu.user_id')
        ->where('cgu.group_id', $group_id)
        ->get(['user.id', 'user.name', 'user.avatar as face']);

        $users = [];
        foreach($rows as $row) {
            $row['face'] = avatar($row['avatar']);
            $users[] = $row;
        }

        $json['infor'] = $group;
        $json['uarr'] = $users;

		return $json;
    }

    /**
     * 获取发送者
     * @access public
     * @param int $type 回话类型
     * @param int $send_id 发送人
     * @param int $receive_id 接受者id，如果是group那就是group_id
     * @param int $user_id group的user_id
     * @param int $message_id 消息id
     * @return array
     */
    public static function getReceiver($type, $receive_id, $send_id)
	{
        if ($type == 'user') {
            $receiver = DB::table('user')
            ->where('id', $receive_id)
            ->selectRaw('id, name, role_id, department_id as deptid, avatar')
            ->first();
            $receiver['face'] = avatar($receiver['avatar']);
            $receiver['type'] = 'user';
            $receiver['utotal'] = 0;
            $receiver['gid'] = $receiver['id'];
        }
        else if ($type == 'group') {
            $receiver = DB::table('chat_group')
            ->where('id', $receive_id)
            ->selectRaw('id, name, logo')
            ->first();
            $receiver['face'] = $receiver['logo'];
            $receiver['type'] = 'group';

            // 查询用户数量
            $receiver['utotal'] = DB::table('chat_group_user')
            ->where('group_id', $receive_id)
            ->count();

            // 查询自己是否在组中
            $receiver['innei'] = DB::table('chat_group_user')
            ->where('group_id', $receive_id)
            ->where('user_id', $send_id)
            ->count();
            $receiver['gid'] = $receiver['id'];
        }
        return $receiver;
    }

    /**
     * 获取发送者
     * @access public
     * @param int $type 回话类型
     * @param int $send_id 发送人
     * @param int $receive_id 接受者id，如果是group那就是group_id
     * @param array $data 发送内容
     * @param int $client 0=app发送 1=web客户端
     * @return array
     */

    public static function sendMessage($type, $send_id, $receive_id, $data, $client = 1)
	{
        $gets = $data;
        $data = [
            'send_id' => $send_id,
            'receive_id' => $receive_id,
            "content" => $gets['cont'],
            'type' => $type,
            'url' => $gets['url'],
            'file_id' => $gets['fileid'],
            'created_dt' => date('Y-m-d H:i:s')
        ];
        $message_id = Message::insertGetId($data);

        // 返回数据
        $json = [
            "cont" => $gets['cont'],
            "sendid" => $send_id,
            "receid" => $receive_id,
            "type" => $type,
            "optdt" => date('Y-m-d H:i:s'),
            "zt" => 0,
            "fileid" => $gets['fileid'],
            "msgid" => "",
            "gid" => $receive_id,
            "id" => $message_id,
            "nuid" => $gets['nuid'],
        ];

        $json['event'] = 'message';
        $json['content'] = $gets['cont'];
        $json['send_id'] = $send_id;

        // 写入历史记录
        $user_ids = ChatService::setHistory($type, $send_id, $receive_id, $message_id);

        if ($type == 'user') {

            // 写入已读(自己)
            DB::table('chat_message_status')->insert([
                'group_id' => $receive_id,
                'user_id' => $send_id,
                'message_id' => $message_id,
                'status' => 1,
                'type' => $type,
            ]);
            // 写入未读(对方)
            DB::table('chat_message_status')->insert([
                'group_id' => $send_id,
                'user_id' => $receive_id,
                'message_id' => $message_id,
                'status' => 0,
                'type' => $type,
            ]);

        } else if ($type == 'group') {

            // 写入未读(讨论组)
            foreach($user_ids as $user_id) {
                // 设置为已读(自己)
                $status = $send_id == $user_id ? 1 : 0;
                DB::table('chat_message_status')->insert([
                    'group_id' => $receive_id,
                    'user_id' => $user_id,
                    'message_id' => $message_id,
                    'status' => $status,
                    'type' => $type,
                ]);
            }

            // 组的名称
            $group = DB::table('chat_group')->where('id', $receive_id)->first();
            $json['gname'] = $group['name'];
        }
        
        $json['receuid'] = join(',', $user_ids);
        $json['receive_ids'] = $user_ids;

        return $json;
    }

    /**
     * 写入会话
     * @access public
     * @param int $type 会话类型
     * @param int $send_id 发送人
     * @param int $receive_id 接受者id，如果是group那就是group_id
     * @param int $user_id group的user_id
     * @param int $message_id 消息id
     * @return array
     */
    public static function setHistory($type, $send_id, $receive_id, $message_id)
	{
        $model = History::firstOrNew([
            'send_id' => $send_id,
            'receive_id' => $receive_id,
            'type' => $type,
        ]);
        $model->last_message_id = $message_id;
        $model->updated_dt = date('Y-m-d H:i:s');
        $model->save();

        if ($type == 'user') {
            $receive_ids = [$send_id, $receive_id];

            $model = History::firstOrNew([
                'send_id' => $receive_id,
                'receive_id' => $send_id,
                'type' => $type,
            ]);
            $model->last_message_id = $message_id;
            $model->updated_dt = date('Y-m-d H:i:s');

            if ($send_id != $receive_id) {
                $model->unread_total = $model->unread_total + 1;
            }

            $model->save();

        } else if($type == 'group') {

            $user_ids = DB::table('chat_group_user')
            ->where('group_id', $receive_id)
            ->pluck('user_id');

            // 写入接收者历史记录
            foreach($user_ids as $user_id) {
                // 返回所有接受者
                $receive_ids[] = (int)$user_id;

                // 发送人和接收人相同
                if ($send_id == $user_id) {
                    continue;
                }
                $model = History::firstOrNew([
                    'send_id' => $user_id,
                    'receive_id' => $receive_id,
                    'type' => $type,
                ]);
                $model->last_message_id = $message_id;
                $model->updated_dt = date('Y-m-d H:i:s');

                if ($send_id != $user_id) {
                    $model->unread_total = $model->unread_total + 1;
                }

                $model->save();
            }
        }
		return array_unique($receive_ids);
    }

    /**
     * 获取回话历史
     *
     * @param int $user_id
     * @param datetime $updated_dt
     * @param int $unread
     * @access public
     * @return array
     */
    public static function getHistory($user_id, $updated_dt = '', $unread = 0)
	{
        $model = DB::table('chat_history as ch')
        ->leftJoin('chat_message as cm', 'cm.id', '=', 'ch.last_message_id')
        ->where('ch.send_id', $user_id)

        ->orderBy('ch.updated_dt', 'desc')
        ->selectRaw("
            ch.type type,
            ch.send_id uid,
            ch.receive_id receid,
            ch.send_id sendid,
            ".sql_month_day('ch.updated_dt')." optdts,
            cm.content cont,
            cm.id messid,
            cm.send_id,
            ch.unread_total stotal,
            0 utotal,
            ch.updated_dt as optdt
        ");

        if ($updated_dt) {
            $model->where('ch.updated_dt', '>', $updated_dt);
        }

        if ($unread) {
            $model->where('ch.unread_total', '>', $unread);
        }
        $rows = $model->get();

        $users = static::getUser();
        $groups = static::getGroup();

        $historys = [];
        foreach ($rows as $row) {
            if ($row['type'] == 'user') {
                $row['face'] = avatar($users[$row['receid']]['avatar']);
                $row['name'] = $users[$row['receid']]['name'];
                if (empty($row['cont'])) {
                    $row['cont'] = '';
                }
            }
            if ($row['type'] == 'group') {
                $row['deptid'] = $groups[$row['receid']]['department_id'];
                $row['face'] = $groups[$row['receid']]['face'];
                $row['name'] = $groups[$row['receid']]['name'];
                if (empty($row['cont'])) {
                    $row['cont'] = '';
                } else {
                    $row['cont'] = $users[$row['send_id']]['name'].':'.$row['cont'];
                }
            }
            $historys[] = $row;
        }
		return $historys;
    }
}
