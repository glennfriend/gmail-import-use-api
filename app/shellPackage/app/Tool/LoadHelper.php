<?php
namespace AppModule\Tool;

class LoadHelper
{
    /**
     *
     */
    public static $args;

    /**
     *
     */
    public static function init(Array $args)
    {
        if (!is_array($args)) {
            $args = [];
        }

        array_shift($args);
        self::$args = $args;
        include_once "helper.php";
    }

    /**
     *  get command line request
     *  TODO: 請改用其它套件來解析裡面的參數!!
     */
    public static function getArguments()
    {
        return self::$args;
    }

}
