<?php
namespace AppModule;

// --------------------------------------------------------------------------------
// wrap controller help functions
// --------------------------------------------------------------------------------

/**
 *  取得 route 處理之後獲得的參數
 *
 *  @see https://github.com/pwfisher/CommandLine.php
 */
function attrib($key, $defaultValue=null)
{
    $args = Tool\LoadHelper::getArgs();
    if (isset($args[$key])) {
        return $args[$key];
    }

    return $defaultValue;
}

/**
 *  輸出
 */
function put($message=null)
{
    if(null === $message) {
        echo "\n";
        return;
    }

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
