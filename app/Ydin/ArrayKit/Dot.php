<?php

namespace Ydin\ArrayKit;

/**
 *  DataConverge
 *
 *  @version     1.0.0
 *  @category    Ydin
 *  @package     Ydin\ArrayKit
 */
class Dot
{

    // 未測試, 未使用
    // 可以改成產生 instance, 直接輸出 $arrayDot('vivian.age', $defaultValue=0 );
    
    /**
     *  產生 instance
     *  包裝後可以直接以 function 方式使用
     *      - $dot('user.email')
     *      - $dot('user.friend.0.name')
     *
     *
     *  @param  array
     *  @return wrap Closure Object
     */
    static public function factory(Array $items)
    {
        /**
         *  用法請參考
         *  DotInstance->get()
         */
        $wrap = function($keyword, $defaultValue=null) use ($items)
        {
            $dot = new DotInstance($items);
            return $dot->get($keyword, $defaultValue);
        };
        return $wrap;
    }

}




/**
 *  使用 . 符號的方式, 將陣列由字串的方式來 取得
 *  Data Converge
 *
 *  例如
 *      $stdClass->user->email
 *      $array['user']['email']
 *
 *  example
 *      $dotInstance = new Ydin\ArrayKit\DotInstance($stdClass)
 *      $dotInstance->get('user.email', null);           // 如果該值不存在, 則抓取 null 值
 *      $dotInstance->get('user.friend.0.name', null);   // 索引值如果是數值, 也可以直接使用
 *
 */
class DotInstance
{
    var $data = null;

    /**
     *
     */
    public function __construct($data)
    {
        $this->data = $this->convertObjectToArray($data);
    }

    /**
     *
     */
    public function get($keyword, $defaultValue=null)
    {
        $pieces = explode('.', trim($keyword));
        $data = $this->data;

        foreach ( $pieces as $piece ) {
            $piece = trim($piece);
            if ( !array_key_exists($piece, $data) ) {
                return $defaultValue;
            }
            $data = $data[$piece];
            $data = $this->convertObjectToArray($data);
        }
        return $data;
    }

    /**
     *  取值的方式統一使用 array
     *  所以如果是 stdClass 則須要轉為 array
     */
    private function convertObjectToArray($data)
    {
        if ( is_object($data) ) {
            $data = (array) $data;
        }
        return $data;
    }

}



