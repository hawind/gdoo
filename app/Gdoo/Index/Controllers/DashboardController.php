<?php namespace Gdoo\Index\Controllers;

use Request;
use DB;
use Gdoo\System\Models\Menu;
use Gdoo\System\Models\Widget;
use Gdoo\User\Models\UserWidget;

class DashboardController extends DefaultController
{
    public $permission = ['index', 'config', 'quickMenu', 'settingWidget', 'settingInfo'];

    public function index()
    {
        $auth = auth()->user();

        $widgets = DB::table('widget')
        ->where('type', 1)
        ->where('status', 1)
        ->where('default', 1)
        ->permission('receive_id')
        ->orderBy('sort', 'asc')
        ->get();

        $user_widgets = UserWidget::where('user_id', $auth['id'])
        ->where('type', 1)
        ->orderBy('sort', 'asc')
        ->get()->keyBy('node_id');

        $widgets->transform(function ($row) use($user_widgets) {
            $user_widget = $user_widgets[$row['id']];
            if (not_empty($user_widget)) {
                $row['id'] = $user_widget['id'];
                $row['status'] = $user_widget['status'];
                $row['grid'] = $user_widget['grid'];
                $row['sort'] = $user_widget['sort'];
                if ($user_widget['name']) {
                    $row['name'] = $user_widget['name'];
                }
                if ($user_widget['color']) {
                    $row['color'] = $user_widget['color'];
                }
                if ($user_widget['icon']) {
                    $row['icon'] = $user_widget['icon'];
                }
                $row['params'] = json_decode($user_widget['params'], true);
                return $row;
            }
        });
        $widgets = $widgets->sortBy('sort');

        $infos = DB::table('widget')
        ->where('type', 2)
        ->where('status', 1)
        ->where('default', 1)
        ->permission('receive_id')
        ->orderBy('sort', 'asc')
        ->get();

        $user_infos = UserWidget::where('user_id', $auth['id'])
        ->where('type', 2)
        ->orderBy('sort', 'asc')
        ->get()->keyBy('node_id');

        $infos->transform(function ($row) use($user_infos) {
            $user_info = $user_infos[$row['id']];
            if (not_empty($user_info)) {
                $row['id'] = $user_info['id'];
                $row['status'] = $user_info['status'];
                $row['sort'] = $user_info['sort'];
                if ($user_info['name']) {
                    $row['name'] = $user_info['name'];
                }
                if ($user_info['color']) {
                    $row['color'] = $user_info['color'];
                }
                if ($user_info['icon']) {
                    $row['icon'] = $user_info['icon'];
                }
                $row['params'] = json_decode($user_info['params'], true);
                return $row;
            }
        });
        $infos = $infos->sortBy('sort');

        $quicks = Menu::leftJoin('user_widget', 'user_widget.node_id', '=', 'menu.id')
        ->where('user_widget.user_id', $auth['id'])
        ->where('user_widget.type', 3)
        ->orderBy('user_widget.sort', 'asc')
        ->get(['menu.*','user_widget.name','user_widget.color','user_widget.icon']);
        $quicks->transform(function ($row) {
            $count = substr_count($row['url'], '/');
            if ($count == 0) {
                $row['url'] = $row['url'].'/index/index';
            } else if($count == 1) {
                $row['url'] = $row['url'].'/index';
            }
            $row['key'] = str_replace(['/', '?', '='], ['_', '_', '_'], $row['url']);
            return $row;
        });
        
        $grids = ['8', '4'];

        return $this->display([
            'widgets' => $widgets,
            'infos' => $infos,
            'grids' => $grids,
            'quicks' => $quicks,
        ]);
    }

    // 仪表板设置
    public function config()
    {
        $auth = auth()->user();

        if (Request::method() == 'POST') {
            $gets = Request::all();
            $widgets = $gets['widget'];
            $sort = 1;
            foreach($widgets as $widget) {
                $widget['sort'] = $sort;
                $sort ++;
                $model = UserWidget::firstOrNew(['type' => 1, 'node_id' => $widget['id'], 'user_id' => $auth['id']]);
                $model->fill($widget);
                $model->save();
            }

            $infos = $gets['info'];
            $sort = 1;
            foreach($infos as $info) {
                $info['sort'] = $sort;
                if ($info['permission'] && $info['date']) {
                    $info['params'] = json_encode(['permission' => $info['permission'], 'date' => $info['date']], JSON_UNESCAPED_UNICODE);
                }
                $sort ++;
                $model = UserWidget::firstOrNew(['type' => 2, 'node_id' => $info['id'], 'user_id' => $auth['id']]);
                $model->fill($info);
                $model->save();
            }

            $menus = $gets['menu'];
            $sort = 1;
            $nodeIds = UserWidget::where('type', 3)->where('user_id', $auth['id'])->pluck('id', 'node_id')->toArray();
            foreach($menus as $menu) {
                unset($nodeIds[$menu['node_id']]);
                $menu['sort'] = $sort;
                $sort ++;
                $model = UserWidget::firstOrNew(['type' => 3, 'node_id' => $menu['node_id'], 'user_id' => $auth['id']]);
                $model->fill($menu);
                $model->save();
            }
            // 删除旧菜单
            UserWidget::whereIn('id', array_values($nodeIds))->delete();

            return $this->json('仪表盘设置成功', true);
        }

        $widgets = DB::table('widget')
        ->where('type', 1)
        ->where('status', 1)
        ->where('default', 1)
        ->permission('receive_id')
        ->orderBy('sort', 'asc')
        ->get(['id', 'name', 'color', 'icon', 'grid', 'status']);

        $user_widgets = UserWidget::where('user_id', $auth['id'])
        ->where('type', 1)
        ->orderBy('sort', 'asc')
        ->get(['id', 'name', 'color', 'icon', 'grid', 'node_id', 'status'])->keyBy('node_id');

        $widgets->transform(function ($row) use($user_widgets) {
            $user_widget = $user_widgets[$row['id']];
            if (not_empty($user_widget)) {
                $row['sort'] = $user_widget['sort'];
                $row['status'] = $user_widget['status'];
                $row['node_id'] = $user_widget['node_id'];
                $row['widget_id'] = $user_widget['id'];
                $row['grid'] = $user_widget['grid'];
                if ($user_widget['name']) {
                    $row['name'] = $user_widget['name'];
                }
                if ($user_widget['color']) {
                    $row['color'] = $user_widget['color'];
                }
                if ($user_widget['icon']) {
                    $row['icon'] = $user_widget['icon'];
                }
            }
            return $row;
        });
        $widgets = $widgets->sortBy('sort');

        $infos = DB::table('widget')
        ->where('type', 2)
        ->where('status', 1)
        ->where('default', 1)
        ->permission('receive_id')
        ->orderBy('sort', 'asc')
        ->get(['id', 'name', 'color', 'icon', 'grid', 'status']);

        $user_infos = UserWidget::where('user_id', $auth['id'])
        ->where('type', 2)
        ->orderBy('sort', 'asc')
        ->get(['id', 'name', 'color', 'icon', 'grid', 'node_id', 'status', 'params'])->keyBy('node_id');

        $infos->transform(function ($row) use($user_infos) {
            $user_info = $user_infos[$row['id']];
            if (not_empty($user_info)) {
                $row['sort'] = $user_info['sort'];
                $row['status'] = $user_info['status'];
                $row['node_id'] = $user_info['node_id'];
                $row['info_id'] = $user_info['id'];
                if ($user_info['name']) {
                    $row['name'] = $user_info['name'];
                }
                if ($user_info['color']) {
                    $row['color'] = $user_info['color'];
                }
                if ($user_info['icon']) {
                    $row['icon'] = $user_info['icon'];
                }
                $row['params'] = json_decode($user_info['params'], true);
            }
            return $row;
        });
        $infos = $infos->sortBy('sort');

        $menus = UserWidget::where('user_id', $auth['id'])
        ->where('type', 3)
        ->orderBy('sort', 'asc')
        ->get();

        $grids = ['8', '4'];

        $json = [
            'widgets' => $widgets,
            'infos' => $infos,
            'menus' => $menus,
        ];
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);

        return $this->render([
            'json' => $json,
            'widgets' => $widgets,
            'infos' => $infos,
            'grids' => $grids,
            'menus' => $menus,
        ]);
    }

    // 添加快捷菜单
    public function quickMenu()
    {
        $menus = DB::table('menu')->orderBy('lft', 'asc')->get();
        $menus = array_nest($menus);
        
        return $this->render([
            'menus' => $menus,
        ]);
    }

    // 设置单个组件
    public function settingInfo()
    {
        // 定义权限
        $permissions = option('role.access')->pluck('name', 'id');
        $dates = [
            'day' => '今天',
            'day2' => '昨天',
            'week' => '本周',
            'week2' => '上周',
            'month' => '本月',
            'month2' => '上月',
            'season' => '本季度',
            'season2' => '上季度',
            'year' => '本年',
            'year2' => '去年',
        ];
        
        $info_id = Request::input('info_id');
        $row = UserWidget::where('id', $info_id)->first();
        $widget = Widget::where('id', $row['node_id'])->first();

        if (empty($row['date'])) {
            $row['date'] = 'month';
        }
        if (empty($row['permission'])) {
            $row['permission'] = 'dept';
        }
        $row['widget_name'] = $widget['name'];

        return $this->render([
            'permissions' => $permissions,
            'dates' => $dates,
            'row' => $row,
        ]);
    }

    // 设置单个组件
    public function settingWidget()
    {
        $widget_id = Request::input('widget_id');
        $row = UserWidget::where('id', $widget_id)->first();
        $widget = Widget::where('id', $row['node_id'])->first();
        if (empty($row['date'])) {
            $row['date'] = 'month';
        }
        if (empty($row['permission'])) {
            $row['permission'] = 'dept';
        }
        $row['widget_name'] = $widget['name'];

        return $this->render([
            'row' => $row,
        ]);
    }
}