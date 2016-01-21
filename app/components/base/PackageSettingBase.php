<?php

class PackageSettingBase
{
    protected $store = array();

    /**
     *  提取多個目錄中, 所有的檔案
     *  組成一個陣列輸出, 格式為
     *  [
     *      '主檔案名'   => '完整路徑',
     *      'Config.inc' => '/var/www/project/folder/Config.inc.php',
     *  ]
     */
    public function findFoldersFiles(array $folders)
    {
        $files = [];
        foreach ($folders as $folder) {
            foreach (glob($folder.'/*.php') as $file) {
                $key = pathinfo(basename($file), PATHINFO_FILENAME);
                $files = [
                    $key => $file,
                ];
            }
        }
        return $files;
    }

    // --------------------------------------------------------------------------------
    // base method
    // --------------------------------------------------------------------------------

    public function get($key)
    {
        if (!isset($this->store[$key])) {
            return null;
        }
        return $this->store[$key];
    }

    public function set($key , $value)
    {
        $this->store[$key] = $value;
    }

}
