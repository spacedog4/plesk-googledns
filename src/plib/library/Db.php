<?php

namespace PleskExt\GoogleDns;

class Db {

    static function path()
    {
        return \pm_Context::getVarDir() . \pm_Context::getModuleId() . '.sqlite3';
    }

    static function adapter()
    {
        static $adapter = null;

        if ($adapter === null) {
            $adapter = new \Zend_Db_Adapter_Pdo_Sqlite([
                'dbname' => static::path(),
            ]);

            $adapter->getConnection()->exec('PRAGMA foreign_keys = ON');
        }

        return $adapter;
    }
}