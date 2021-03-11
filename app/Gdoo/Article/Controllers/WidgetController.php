<?php namespace Gdoo\Article\Controllers;

use DB;
use Auth;
use Request;
use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\InfoService;
use Gdoo\User\Models\UserWidget;

class WidgetController extends DefaultController
{
    public $permission = ['index', 'info'];

    /**
     * 公告部件
     */
    public function indexAction()
    {
        if (Request::isJson()) {
            $model = DB::table('article')
            ->permission('receive_id')
            ->orderBy('created_at', 'desc');

            // 查询是否已经阅读
            $reader = function ($q) {
                $q->selectRaw('1')
                ->from('article_reader')
                ->whereRaw('article_reader.article_id = article.id')
                ->where('article_reader.created_id', auth()->id());
            };
            $model->whereNotExists($reader);

            $rows = $model->get(['id', 'name', 'created_at']);
            
            $json['total'] = sizeof($rows);
            $json['data'] = $rows;
            return response()->json($json);
        }
        return $this->render();
    }

    /**
     * 公告信息
     */
    public function infoAction()
    {
        $config = InfoService::getInfo('article');

        $count = DB::table('article')
        ->permission('receive_id')
        ->whereNotExists(function ($q) {
            $q->selectRaw('1')
            ->from('article_reader')
            ->whereRaw('article_reader.article_id = article.id')
            ->where('article_reader.created_id', auth()->id());
        })->whereRaw('('.$config['sql'].')')->count();

        $count2 = DB::table('article')
        ->permission('receive_id')
        ->whereNotExists(function ($q) {
            $q->selectRaw('1')
            ->from('article_reader')
            ->whereRaw('article_reader.article_id = article.id')
            ->where('article_reader.created_id', auth()->id());
        })->whereRaw('('.$config['sql2'].')')->count();

        $rate = 0;
        if ($count2 > 0) {
            $rate = $count / $count2 * 100;
        }
        $res = [
            'count' => $count,
            'count2' => $count2,
            'rate' => $rate,
        ];
        return $this->render([
            'dates' => $config['dates'],
            'info' => $config['info'],
            'res' => $res,
        ]);
    }
}
