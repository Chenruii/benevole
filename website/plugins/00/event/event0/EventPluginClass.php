<?php


class EventPluginClass
{

    public static function getInstance($options = null): EventPlugin
    {


        $event = new EventPlugin();
        if (!empty($options) && is_array($options)) {
            foreach ($options as $k => $v) {
                $event->setOption($k, $v);
            }
        }
        $event->init();
        if (function_exists('customizeMibBlog')) {
            customizeMibBlog($event);
        }

        return $event;
    }

}