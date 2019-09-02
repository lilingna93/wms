<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/15
 * Time: 13:58
 */

namespace Erp\Dao;


use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

class PrizeDao extends BaseDao implements BaseDaoInterface
{
    /**
     * PrizeDao constructor.
     */
    public function __construct()
    {

    }

    public function queryById($id)
    {
        return M("Prize")->where(array("id" => $id))->fetchSql(false)->find();

    }


    public function queryList()
    {
        return M("Prize")->fetchSql(false)->select();

    }

    public function queryListDesc()
    {
        return M("Prize")->fetchSql(false)->order("id desc")->select();

    }

    public function queryListByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("Prize")->where($condition)->fetchSql(false)->select();
    }

    public function updateCountById($id, $count)
    {
        return M("Prize")->where(array("id" => $id))->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "prize_count" => $count));
    }

    public function updateWinningRateById($id, $rate)
    {
        return M("Prize")->where(array("id" => $id))->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "winning_rate" => $rate));
    }

    public function updateEdition($edition)
    {
        return M("Prize")->where(array())->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "prize_edition" => $edition));
    }

    private function conditionFilter($cond)
    {
        $condition = array();
        if (isset($cond["issetRateGt"])) {
            $condition["winning_rate"] = array("GT", $cond["issetRateGt"]);
        }
        if (isset($cond["issetNumGt"])) {
            $condition["prize_count"] = array("GT", $cond["issetNumGt"]);
        }
        return $condition;
    }

}