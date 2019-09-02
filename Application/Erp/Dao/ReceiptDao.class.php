<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/30
 * Time: 10:33
 */

namespace Erp\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

class ReceiptDao extends BaseDao implements BaseDaoInterface
{
    public function __construct()
    {

    }

    /**
     * @return mixed
     * 创建
     */
    public function insert($item)
    {
        $ctime = $item["ctime"];
        $code = venus_unique_code("ERR");
        $data = array(
            "rec_code" => $code,
            "rec_ctime" => empty($ctime) ? venus_current_datetime() : $ctime,
            "goods_code" => $item['code'],
            "goods_bprice" => $item['bprice'],
            "goods_init" => $item['init'],
        );
        return M("erpreceipt")->add($data);
    }

    public function queryByCode($code)
    {
        return M("erpreceipt")->where(array("rec_code"=>$code))->find();
    }

    /**
     * @param $condition
     * @return mixed
     * 总条数
     */
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("erpreceipt")->where($condition)->count();
    }

    /**
     * @param $condition
     * @param int $page
     * @param int $count
     * @return mixed
     * 列表
     */
    public function queryListByCondition($condition, $page = 0, $count = 1000)
    {
        $condition = $this->conditionFilter($condition);
        return M("erpreceipt")->where($condition)->order("rec_code desc")->limit("{$page},{$count}")->fetchSql(false)->select();

    }

    /**
     * @param $condition
     * @param int $page
     * @param int $count
     * @return mixed
     * 升序列表
     */
    public function queryListAscByCondition($condition, $page = 0, $count = 1000)
    {
        $condition = $this->conditionFilter($condition);
        return M("erpreceipt")->where($condition)->order("rec_code desc")->limit("{$page},{$count}")->fetchSql(false)->select();

    }

    private function conditionFilter($cond)
    {
        $condition = array();
        if (isset($cond["code"]) && !empty($cond["code"])) {
            $condition["rec_code"] = $cond["code"];
        }


        if (isset($cond["goodsCode"]) && !empty($cond["goodsCode"])) {
            $condition["goods_code"] = $cond["goodsCode"];
        }

        if (isset($cond["surplus"]) && !empty($cond["surplus"])) {
            $condition["goods_surplus"] = $cond["surplus"];
        }

        if (isset($cond["ctime"]) && !empty($cond["ctime"])) {
            $condition["rec_ctime"] = $cond["ctime"];
        }

        if (isset($cond["sctime"]) && isset($cond["ectime"])) {
            $condition["rec_ctime"] = array(
                array('EGT', $cond["sctime"]),
                array('LT', $cond["ectime"]),
                'AND'
            );
        } else if (isset($cond["sctime"])) {
            $condition["rec_ctime"] = array("EGT", $cond["sctime"]);
        } else if (isset($cond["ectime"])) {
            $condition["rec_ctime"] = array("LT", $cond["ectime"]);
        }
        return $condition;
    }

    /**
     * @param $recCode
     * @param $count
     * @return mixed
     * 修改实收数量
     */
    public function updateGoodsCountByRecCode($recCode, $count)
    {
        return M("erpreceipt")->where(array("rec_code" => $recCode))->save(array("goods_count" => $count, "timestamp" => venus_current_datetime()));
    }

    /**
     * @param $recCode
     * @param $surplus
     * @return mixed
     * 修改实收后剩余数量
     */
    public function updateGoodsSurplusByRecCode($recCode, $surplus)
    {
        return M("erpreceipt")->where(array("rec_code" => $recCode))->save(array("goods_surplus" => $surplus, "timestamp" => venus_current_datetime()));
    }
}