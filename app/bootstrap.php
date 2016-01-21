<?php

function initialize($basePath, $packageName)
{
    error_reporting(-1);
    ini_set('html_errors','Off');
    ini_set('display_errors','Off');

    if ( phpversion() < '5.5' ) {
        pr("PHP Version need >= 5.5");
        exit;
    }

    $autoload = $basePath . '/composer/vendor/autoload.php';
    if (!file_exists($autoload)) {
        die('Lose your composer!');
    }
    require_once ($autoload);

    // init config
    ConfigManager::init( $basePath . '/app/config');
    if ( conf('app.path') !== $basePath ) {
       pr('base path setting error!');
       exit;
    }

    if (isTraining()) {
        error_reporting(E_ALL);
        ini_set('html_errors','On');
        ini_set('display_errors','On');
    }

    date_default_timezone_set(conf('app.timezone'));

    // load Package
    switch($packageName)
    {
        case 'shell':
            include_once "/{$basePath}/app/{$packageName}Package/PackageSetting.php";
            $setting = new PackageSetting();
            $setting->set('basePath', $basePath);
            $setting->perform();
            break;

        default:
            throw new Exception('Warning! setting file error!'); 
            exit;
    }

}

function getDefaultSlimConfig()
{
    $container = new \Slim\Container();

    if (isTraining()) {
        $container['settings']['displayErrorDetails'] = true;
    }

    // Override the default Not Found Handler
    $container['notFoundHandler'] = function ($c) {
        return function ($request, $response) use ($c) {

            $error = ErrorSupportHelper::getJson('p404');
            return $c['response']
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write($error)
            ;

        };
    };

    return $container;
}

function isTraining()
{
    if ( 'training' === conf('app.env') ) {
        return true;
    }
    return false;
}

function isCli()
{
    return PHP_SAPI === 'cli';
}

function conf($key)
{
    return ConfigManager::get($key);
}

function pr($data, $writeLog=false)
{
    if (is_object($data) || is_array($data)) {
        print_r($data);

        if ($writeLog) {
            di('log')->record(
                print_r($data, true)
            );
        }
    }
    else {
        echo $data;
        echo "\n";

        if ($writeLog) {
            di('log')->record($data);
        }
    }
}

/**
 *  包裝了 Symfony Dependency-Injection
 *  提供了簡易的取用方式 DI->get( $service )
 */
function di($getParam=null)
{
    static $container;
    if ($container) {
        if ($getParam) {
            return $container->get($getParam);
        }
        return $container;
    }

    $container = new Symfony\Component\DependencyInjection\ContainerBuilder();
    return $container;
}
