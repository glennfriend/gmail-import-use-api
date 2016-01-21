<?php
namespace Bridge;

class Log
{

    protected static $_logPath = null;

    /**
     *  init
     */
    public static function init( $logPath )
    {
        self::$_logPath = $logPath;
    }

    /* --------------------------------------------------------------------------------
        access
    -------------------------------------------------------------------------------- */

    /**
     *  error log
     */
    public static function getPath()
    {
        if (null === self::$_logPath) {
            throw new Exception('Error: Log path empty');
            exit;
        }

        return self::$_logPath;
    }

    /* --------------------------------------------------------------------------------
        write
    -------------------------------------------------------------------------------- */

    /**
     *  system log
     */
    public static function record($content)
    {
        $content = date("Y-m-d H:i:s") . ' - '. $content;
        self::write('system.log', $content );
    }

    /**
     *  sql log
     */
    public static function sql($content)
    {
        $content = date("Y-m-d H:i:s") .' - '. $content;
        self::write('debug-sql.log', $content);
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    /**
     *  write file
     */
    public static function write($file, $content)
    {
        if (!preg_match('/^[a-z0-9_\-\.]+$/i', $file)) {
            return;
        }

        $filename = self::getPath() .'/'. $file;
        file_put_contents( $filename, $content."\n", FILE_APPEND );
    }

}
