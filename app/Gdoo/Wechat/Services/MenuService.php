<?php namespace Gdoo\Wechat\Services;

use Cache;

class MenuService
{
    public function GetTreeByMenu($list, $id = 'id', $pid = 'pid', $son = 'sub')
    {
        $tree = $map = [];
        foreach ($list as $item) {
            $map[$item[$id]] = $item;
        }
        foreach ($list as $item) {
            if (isset($item[$pid]) && isset($map[$item[$pid]])) {
                $map[$item[$pid]][$son][] = &$map[$item[$id]];
            } else {
                $tree[] = &$map[$item[$id]];
            }
        }
        unset($map);
        return $tree;
    }
}
