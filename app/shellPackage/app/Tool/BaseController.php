<?php
namespace AppModule\Tool;

/**
 *
 */
class BaseController
{

    /**
     *
     */
    public function __call($method, $controllerArgs)
    {
        global $argv;  // by command line

        if (!method_exists($this, $method)) {
            throw new \Exception("API method '{$method}' is not exist!");
            exit;
        }
        $this->loadHelper($argv);
        $this->$method();
    }

    /**
     *  load functions, to help controller
     *
     *  裡面包裹的 help function
     *  僅給 controller 使用
     *  並不給予 view 使用
     */
    protected function loadHelper(Array $args)
    {
        LoadHelper::init($args);
    }

}
