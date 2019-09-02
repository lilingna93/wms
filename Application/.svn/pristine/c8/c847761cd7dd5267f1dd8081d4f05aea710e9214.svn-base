<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 货架货位数据
 * Class PositionDao
 * @package Wms\Dao
 */
class PositionDao extends BaseDao implements BaseDaoInterface {


    //查询
    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code) {
        $condition = array("war_code" => $this->warehousecode, "pos_code" => $code);
        return M("Position")->where($condition)->find();
    }

    //免仓内操作获取位置编号
    /**
     * @return mixed
     */
    public function queryByWarCode() {
        return M("Position")->where(array("war_code" => $this->warehousecode))->find();
    }
}