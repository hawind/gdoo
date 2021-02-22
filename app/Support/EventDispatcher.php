<?php namespace App\Support;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class EventDispatcher extends BaseEventDispatcher
{
    protected function doDispatch($listeners, $eventName, GenericEvent $event)
    {
        foreach ($listeners as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }
            $arguments = \call_user_func($listener, $event->getArguments(), $eventName, $this);
            $event->setArguments($arguments);
        }
    }
}
