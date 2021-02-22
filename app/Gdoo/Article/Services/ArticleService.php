<?php namespace Gdoo\Article\Services;

use DB;

class ArticleService
{
    /**
     * 获取未读公告
     */
    public static function getBadge()
    {
        $rows = DB::table('article')
        ->permission('receive_id')
        ->whereNotExists(function ($q) {
            $q->selectRaw('1')
            ->from('article_reader')
            ->whereRaw('article_reader.article_id = article.id')
            ->where('article_reader.created_id', auth()->id());
        })->get();
        $ret['total'] = sizeof($rows);
        $ret['data'] = $rows;
        return $ret;
    }
}
