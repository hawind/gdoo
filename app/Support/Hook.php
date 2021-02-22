<?php namespace App\Support;

use Symfony\Component\EventDispatcher\GenericEvent;

class Hook
{
    public static function listen($tag, $class)
    {
        $object = with(new $class);
        $methods = get_class_methods($object);
        foreach ($methods as $method) {
            app('dispatcher')->addListener($tag.'.'.$method, [$object, $method]);
        }
        unset($object);
    }

    public static function fire($tag, $data = [])
    {
        $event = new GenericEvent();
        $event->setArguments($data);
        $event = app('dispatcher')->dispatch($event, $tag);
        return $event->getArguments();
    }
    
}
