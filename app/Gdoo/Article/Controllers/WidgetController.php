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
    public function index()
    {
        if (Request::method() == 'POST') {
            $model = DB::table('article')
            ->permission('receive_id')
            ->orderBy('created_at', 'desc');

            $rows = $model->limit(15)->get(['id', 'name', 'created_at']);
            
            $json['total'] = sizeof($rows);
            $json['data'] = $rows;
            return $json;
        }
        return $this->render();
    }

    /**
     * 公告信息
     */
    public function info()
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
            $rate = ($count - $count2) / $count2 * 100;
            $rate = number_format($rate, 2);
        }
        $res = [
            'count' => $count,
            'count2' => $count2,
            'rate' => $rate,
        ];
        return $this->json($res, true);
    }
}
