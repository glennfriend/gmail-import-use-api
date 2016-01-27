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

        self::$args = $args;
        include_once "helper.php";
    }

    /**
     *  get pure command-line arguments
     */
    public static function getArguments()
    {
        return self::$args;
    }

    /**
     *  取得整理過後的 CLI 參數
     *  @dependency CommandLine class
     */
    public static function getArgs()
    {
        return \CommandLine::parseArgs(self::$args);
    }

}
