<?php namespace Gdoo\Index\Services;

use Gdoo\Index\Models\Share;
use Gdoo\User\Models\User;

class ShareService
{
    /**
     * 获取分享数据
     */
    public static function getItemsSourceBy(array $source_type, $user_id, $start_at = null, $end_at = null)
    {
        $user = User::find($user_id);
        $model = Share::leftJoin('user', 'share.created_by', '=', 'user.id')
        ->permission('share.receive_id', $user)
        ->whereIn('share.source_type', $source_type);

        $start_at = strtotime($start_at);
        $end_at = strtotime($end_at) + 86400;
        if ($start_at > 0 && $end_at > 0) {
            $model->whereRaw('(
                (share.start_at between '.$start_at.' and '.$end_at.' or share.end_at between '.$start_at.' and '.$end_at.')
                or (share.is_repeat = 1 and share.start_at <= '.$end_at.')
            )');
        }
        return $model->get(['share.*', 'user.name', 'user.username']);
    }

    /**
     * 获取分享数据
     */
    public static function getItemsCreatedBy(array $source_type, $user_id)
    {
        return Share::where('created_id', $user_id)
        ->whereIn('source_type', $source_type)
        ->get();
    }

    /**
     * 添加分享数据
     */
    public static function getItemsSourceId($source_type, $source_id)
    {
        return Share::where('source_id', $source_id)
        ->where('source_type', $source_type)
        ->get();
    }

    /**
     * 添加分享数据
     */
    public static function getItem($source_type, $source_id)
    {
        return Share::where('source_id', $source_id)
        ->where('source_type', $source_type)
        ->first();
    }

    /**
     * 删除一条分享数据
     */
    public static function removeItem($source_type, $source_id)
    {
        return Share::where('source_id', $source_id)
        ->where('source_type', $source_type)
        ->delete();
    }

    /**
     * 添加分享数据
     */
    public static function addItem($data)
    {
        if ($data['receive_id'] == '') {
            return;
        }
        if (empty($data['source_id']) || empty($data['source_type'])) {
            return;
        }
        Share::insert($data);
    }

    /**
     * 编辑分享数据
     */
    public static function editItem($source_type, $source_id, $data)
    {
        if ($data['receive_id'] == '') {
            // 共享对象为空删除共享记录
            static::removeItem($source_type, $source_id);
            return;
        }
        if (empty($source_id) || empty($source_type)) {
            return;
        }
        return Share::where('source_id', $source_id)
        ->where('source_type', $source_type)
        ->update($data);
    }
}
