<?php namespace Gdoo\Article\Hooks;

use DB;
use Gdoo\Index\Services\AttachmentService;

class ArticleHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        $ids = $params['ids'];
        $masters = $params['masters'];
        // 新删除附件
        foreach($masters as $master) {
            AttachmentService::remove($master['attachment']);
        }
        // 删除阅读积累
        DB::table('article_reader')->whereIn('article_id', $ids)->delete();
        return $params;
    }

    public function onBeforeImport($params) 
    {
        return $params;
    }
}
