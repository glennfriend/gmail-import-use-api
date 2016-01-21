<?php

/**
 *
 */
class ErrorSupportHelper
{
    /**
     *  get error message by array
     *  @return array
     */
    public static function get($code)
    {
        return [
            'error' => [
                'code'    => $code,
                'message' => self::_wrap($code),
            ]
        ];
    }

    /**
     *  get error message by Json
     *  @return string
     */
    public static function getJson($code)
    {
        return json_encode(self::get($code));
    }

    // --------------------------------------------------------------------------------
    // private
    // --------------------------------------------------------------------------------

    protected static function _wrap($code)
    {
        switch ($code) {
            case 'p404': return 'Page not found';
        }
        throw new Exception('Error support code error');
    }
}
