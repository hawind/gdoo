<?php namespace Gdoo\Model\Services;

use App\Support\Hook;

class ModuleService
{
    public static $modules;

    public static $details = [];

    public static $dialogs = [];

    public static $widgets = [];

    public static $badges = [];

    public static function details()
    {
        return static::$details;
    }

    public static function all()
    {
        if (static::$modules) {
            return static::$modules;
        }

        $path = base_path().'/app/Gdoo';

        if (is_dir($path)) {
            $folders = new \DirectoryIterator($path);

            foreach ($folders as $folder) {
                if (!$folder->isDot() && $folder->isDir()) {
                    $folder = $folder->getFileName();
                    $file = $path.'/'.$folder.'/config.php';
                    
                    if (is_file($file)) {
                        static::$modules[$folder] = $path.'/'.$folder;
                    }
                }
            }
        }
        return static::$modules;
    }

    public static function allWithDetails()
    {
        $modules = static::all();
        $json = [];
        foreach ($modules as $key => $module) {
            $file = $module.'/config.php';

            if (is_file($file)) {
                $content = include($file);
                $content['path'] = $module;

                if (not_empty($content['listens'])) {
                    foreach ($content['listens'] as $k => $v) {
                        Hook::listen($k, $v);
                    }
                }

                if (not_empty($content['widgets'])) {
                    static::$widgets = static::$widgets + $content['widgets'];
                }

                if (not_empty($content['badges'])) {
                    static::$badges = static::$badges + $content['badges'];
                }

                // 获取对话框
                if (not_empty($content['dialogs'])) {
                    static::$dialogs = static::$dialogs + $content['dialogs'];
                }

                $json[lcfirst($key)] = $content;
            }
        }
        static::$details = $json;
        return $json;
    }

    public static function widgets($key = null)
    {
        if ($key) {
            return static::$widgets[$key];
        }
        return static::$widgets;
    }

    public static function dialogs($key = null)
    {
        if ($key) {
            return static::$dialogs[$key];
        }
        return static::$dialogs;
    }

    public static function badges($key = null)
    {
        if ($key) {
            return static::$badges[$key];
        }
        return static::$badges;
    }

}
