<?php
namespace Bridge\Options;

/**
 *  不做任何 cache
 */
class CacheNothing
{
    protected $cache;

    /**
     *  init
     */
    public function __construct()
    {
        // nothing
    }

    /* --------------------------------------------------------------------------------
        access
    -------------------------------------------------------------------------------- */

    /**
     *  get cache
     */
    public function get($key)
    {
        return null;
    }

    /* --------------------------------------------------------------------------------
        write
    -------------------------------------------------------------------------------- */

    /**
     *  set cache
     */
    public function set($key, $value)
    {
        // nothing
    }

    /**
     *  remove cache
     */
    public function remove($key)
    {
        // nothing
    }

    /**
     *  remove cache by prefix
     *  移除該值開頭的所有快取
     */
    public function removePrefix($prefix)
    {
        // nothing
    }

    /**
     *  clean all cache data
     */
    public function flush()
    {
        // nothing
    }

}
