<?php namespace Gdoo\Forum\Controllers;

use DB;
use Auth;
use Request;

use Gdoo\Forum\Models\Forum;
use Gdoo\Forum\Models\ForumPost;
use Gdoo\Index\Controllers\DefaultController;

class PostController extends DefaultController
{
    // 板块列表
    public function indexAction()
    {
        $forum = Forum::where('forum.state', 1);

        // 权限限制
        $level = authorise('index');

        if ($level < 4) {
            $forum->permission('forum.scope_id');
        }

        $forum->selectRaw('forum.*')
        ->groupBy('forum.id');

        $rows = $forum->get();

        foreach ($rows as &$row) {
            // 板块最后一个帖子
            $row['post'] = ForumPost::whereRaw('parent_id=0')
            ->where('forum_id', $row['id'])
            ->orderBy('id', 'desc')
            ->first(['id', 'title', 'add_user_id', 'add_time']);

            // 统计板块今天帖子数
            $row['today'] = ForumPost::whereRaw('parent_id=0')
            ->where('forum_id', $row['id'])
            ->whereRaw('TO_DAYS(FROM_UNIXTIME(add_time)) = TO_DAYS(NOW())')
            ->count('id');
        }

        return $this->display([
            'rows' => $rows,
        ]);
    }

    // 论坛类别帖子列表
    public function forumAction()
    {
        $id = Request::get('id');

        $search = search_form([
            'id' => $id,
            'referer' => 1
        ], [
            ['text','title','主题'],
        ]);

        $query  = $search['query'];

        $model = ForumPost::whereRaw('parent_id=0 and forum_id=?', [$id])
        ->orderBy('id', 'desc');

        foreach ($search['where'] as $where) {
            if ($where['active']) {
                $model->search($where);
            }
        }

        $rows = $model->paginate()->appends($query);

        foreach ($rows as $key => $row) {
            $row->post = ForumPost::where('parent_id', $row->id)
            ->orderBy('id', 'desc')
            ->first();

            $row->count = ForumPost::where('parent_id', $row->id)
            ->count('id');

            $rows->put($key, $row);
        }

        $today = ForumPost::whereRaw('parent_id=0 and TO_DAYS(FROM_UNIXTIME(add_time))=TO_DAYS(NOW()) and forum_id=?', [$id])->count('id');

        $forum['count'] = $rows->total();
        $forum['today'] = $today;
        $forum['id'] = $id;

        return $this->display([
            'forum' => $forum,
            'rows' => $rows,
            'search' => $search,
        ]);
    }

    public function addAction()
    {
        $id = Request::get('id');
        $forum_id = Request::get('forum_id');

        $row = DB::table('forum_post')->where('id', $id)->first();

        $row['forum_id'] = empty($row['forum_id']) ? $forum_id : $row['forum_id'];

        // 更新数据
        if ($post = $this->post()) {
            if (empty($post['title'])) {
                return $this->error('主题必须填写。');
            }

            if (empty($post['content'])) {
                return $this->error('正文必须填写。');
            }

            $post['content'] = $_POST['content'];
            $post['attachment'] = join(',', (array)$post['attachment']);

            // 更新数据库
            if ($post['id'] > 0) {
                DB::table('forum_post')->where('id', $post['id'])->update($post);
            } else {
                $post['add_time'] = time();
                $post['add_user_id'] = Auth::id();
                $post['id'] = DB::table('forum_post')->insertGetId($post);
            }

            // 设置附件为已经使用
            attachment_store('forum_attachment', $_POST['attachment']);

            return $this->success('view', ['id' => $post['id']], '帖子发表成功。');
        }

        $attachList = attachment_edit('forum_attachment', $row['attachment'], 'forum');

        return $this->display([
            'attachList' => $attachList,
            'row' => $row,
        ]);
    }

    public function commentAction()
    {
        $id = Request::get('id');
        $parent_id = Request::get('parent_id');

        // 更新数据
        if ($post = $this->post()) {
            if (empty($post['content'])) {
                return $this->error('正文必须填写。');
            }

            $post['content'] = $_POST['content'];
            $post['attachment'] = join(',', (array)$post['attachment']);

            // 更新数据库
            if ($post['id']) {
                DB::table('forum_post')->where('id', $post['id'])->update($post);
            } else {
                $post['add_time'] = time();
                $post['add_user_id'] = Auth::id();
                DB::table('forum_post')->insert($post);
            }

            // 设置附件为已经使用
            attachment_store('forum_attachment', $_POST['attachment']);
            return $this->success('view', ['id' => $post['parent_id']], '帖子回复保存成功。');
        }

        $row = DB::table('forum_post')->where('id', $id)->first();
        $attachList = attachment_edit('forum_attachment', $row['attachment'], 'forum');

        return $this->display([
            'attachList' => $attachList,
            'row' => $row,
        ]);
    }

    public function viewAction()
    {
        $id = (int)Request::get('id');

        // 获取帖子主题
        $post = ForumPost::find($id);

        // 获取帖子回复
        $rows = ForumPost::where('parent_id', $id)->get();
        if ($rows->count()) {
            foreach ($rows as $key => $row) {
                $row->attach = attachment_get('forum_attachment', $row['attachment']);
                $rows->put($key, $row);
            }
        }

        if (empty($post)) {
            return $this->error('没有数据。');
        }

        // 更新点击率
        $post->increment('hit');

        $attachList = attachment_view('forum_attachment', $post['attachment']);
        $attachment = attachment_edit('forum_attachment', '', 'forum');

        return $this->display([
            'attachList' => $attachList,
            'attachment' => $attachment,
            'post' => $post,
            'rows' => $rows,
        ]);
    }

    public function deleteAction()
    {
        $id = (int)Request::get('id');

        $post = ForumPost::where('id', $id)->first();

        if (empty($post)) {
            return $this->error('帖子不存在。');
        }

        // 删除帖子附件
        attachment_delete('forum_attachment', $post['attachment']);

        // 删除帖子
        $post->delete();

        // 删除的是回复
        if ($post->parent_id == 0) {
            // 删除帖子全部回复
            $rows = ForumPost::where('parent_id', $id)->get();
            if ($rows->count()) {
                foreach ($rows as $row) {
                    $row->delete();
                    attachment_delete('forum_attachment', $row['attachment']);
                }
            }
            return $this->success('forum', ['id' => $post->forum_id], '帖子删除成功。');
        } else {
            return $this->success('view', ['id' => $post->parent_id], '帖子删除成功。');
        }
    }
}
