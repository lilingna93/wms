<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 轨迹数据
 * Class TraceDao
 * @package Wms\Dao
 */
class TraceDao extends BaseDao implements BaseDaoInterface {


    //添加数据[]
    /**
     * @return bool
     */
    public function insert() {
        $code = venus_unique_code("TK");
        $data = array(
            "trace_code" => $code,
            "trace_data" => "[]",
        );
        return M("Trace")->add($data) ? $code : false;
    }
    //查询
    /**
     * @param $code
     * @return mixed
     */
    public function queryDataByCode($code) {
        return M("Trace")->where(array("trace_code" => $code))->getField('trace_data');
    }
    //查询
    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code) {
        return M("Trace")->where(array("trace_code" => $code))->find();
    }

    //更新轨迹记录
    /**
     * @param $code
     * @param $data
     * @return mixed
     */
    public function updateDataByCode($code, $data) {
        return M("Trace")->where(array("trace_code" => $code))->save($data);
    }

}