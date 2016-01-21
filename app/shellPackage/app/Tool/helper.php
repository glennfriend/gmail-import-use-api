<?php
namespace AppModule;

// --------------------------------------------------------------------------------
// wrap controller help functions
// --------------------------------------------------------------------------------

/**
 *  取得 route 處理之後獲得的參數
 */
function attrib($key, $defaultValue=null)
{
    $allParams = Tool\LoadHelper::getArguments();
    if (in_array($key, $allParams)) {
        return true;
    }

    foreach ($allParams as $param) {
        $tmp = explode('=', $param);
        $name = $tmp[0];
        array_shift($tmp);
        $value = join('=', $tmp);

        if ($name===$key) {
            return $value;
        }
    }

    return $defaultValue;
}

/**
 *  輸出
 */
function put($message)
{
    switch (gettype($message)) {
        case "array":
        case "object":
        case "resource":
            print_r($message);
            break;

        case "integer":
        case "double":
        case "string":
            echo $message;
            echo "\n";
            break;

        case "NULL":
        case "boolean":
        case "unknown type":
            var_dump($message);
            break;

        default:
            die('put() Error: fasdfasdfasfadfasdfsad');
    };
}
