<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 仓库数据
 * Class WarehouseDao
 * @package Wms\Dao
 */
class WarehouseDao extends BaseDao implements BaseDaoInterface
{
    /**
     * @var string
     */
    private $dbname = "";

    /**
     * WarehouseDao constructor.
     */
    function __construct()
    {
        $this->dbname = C("WMS_CLIENT_DBNAME");
    }

    //查询

    /**
     * @return mixed
     */
    public function query()
    {
        $condition = array("war_code" => $this->warehousecode);
        return M("Warehouse")->where($condition)->find();
    }

    //查询

    /**
     * @param int $count
     * @return mixed
     */
    public function queryClientList($count = 100)
    {
        return M("{$this->dbname}.Warehouse")->limit(0, $count)->select();
    }

    public function queryClientByCode($code)
    {
        return M("{$this->dbname}.Warehouse")->where(array("war_code" => $code))->find();
    }

    /**
     * @param int $count
     * @return mixed
     * 根据条件查询
     */
    public function queryClientListByCondition($condition, $count = 100)
    {
        $cond = array();
        if (isset($condition['name'])) {
            $name = $condition['name'];
            $cond['war_name'] = array("like", "%$name%");
        }
        return M("{$this->dbname}.Warehouse")
            ->where($cond)->limit(0, $count)->select();
    }

}