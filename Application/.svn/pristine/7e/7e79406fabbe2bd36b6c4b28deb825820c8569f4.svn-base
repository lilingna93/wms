<?php
namespace Common\Common;

class BaseDao {
    static protected $instances = [];

    static public function getInstance($code = "") {
        $class = get_called_class();
        $instkey = "{$class}{$code}";
        if (!isset(static::$instances[$instkey])) {
            static::$instances[$instkey] = new $class();
            static::$instances[$instkey]->warehousecode = $code;
        }
        return static::$instances[$instkey];
    }

    //仓库编码
    public $warehousecode = "";
}
