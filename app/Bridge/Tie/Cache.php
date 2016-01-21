<?php
namespace Bridge\Tie;

interface Cache
{
    public static function init($cachePath);

    public static function get($key);
  //public static function has($key);

    public static function set($key, $value);

    /**
     *  remove cache
     */
    public static function remove($key);

    /**
     *  remove cache by prefix
     *  移除該值開頭的所有快取
     */
    public static function removePrefix($prefix);

    /**
     *  clean all cache data
     */
    public static function flush();
}
