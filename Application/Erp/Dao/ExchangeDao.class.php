<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/15
 * Time: 15:56
 */

namespace Erp\Dao;


use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

class ExchangeDao extends BaseDao implements BaseDaoInterface
{
    public function __construct()
    {

    }

    /**
     * @return mixed
     * 创建记录
     */
    public function insert($item)
    {
        $ctime = $item["ctime"];
        $data = array(
            "prize_item" => $item['item'],//奖项名称
            "ex_user" => $item['user'],//客户名称
            "ex_iphone" => $item['iphone'],//客户手机号
            "ex_address" => $item['address'],//客户地址
            "ex_ctime" => empty($ctime) ? venus_current_datetime() : $ctime,
            "is_exchange" => $item['isExchange'],
            "prize_edition" => $item['edition']
        );
        return M("Exchange")->add($data);

    }

    /**
     * @param $condition
     * @return mixed
     * 总条数
     */
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("Exchange")->where($condition)->count();
    }

    /**
     * @param $condition
     * @param int $page
     * @param int $count
     * @return mixed
     * 展示列表
     */
    public function queryListByCondition($condition, $page = 0, $count = 1000)
    {
        $condition = $this->conditionFilter($condition);
        return M("Exchange")->where($condition)->order("ex_ctime desc")->limit("{$page},{$count}")->fetchSql(false)->select();

    }

    private function conditionFilter($cond)
    {
        $condition = array();
        if (isset($cond["iphone"]) && !empty($cond["iphone"])) {
            $condition["ex_iphone"] = $cond["iphone"];
        }

        if (isset($cond["item"]) && !empty($cond["item"])) {
            $condition["prize_item"] = array("like", '%' . $cond["item"] . "%");//奖项
        }

        if (isset($cond["isExchange"]) && !empty($cond["isExchange"])) {
            $condition["is_exchange"] = $cond["isExchange"];//奖项
        }

        if (isset($cond["ctime"]) && !empty($cond["ctime"])) {
            $condition["ex_ctime"] = $cond["ctime"];
        }

        if (isset($cond["user"]) && !empty($cond["user"])) {
            $condition["ex_user"] = array("like", '%' . $cond["user"] . "%");
        }

        if (isset($cond["sctime"]) && isset($cond["ectime"])) {
            $condition["ex_ctime"] = array(
                array('EGT', $cond["sctime"]),
                array('ELT', $cond["ectime"]),
                'AND'
            );
        } else if (isset($cond["sctime"])) {
            $condition["ex_ctime"] = array("EGT", $cond["sctime"]);
        } else if (isset($cond["ectime"])) {
            $condition["ex_ctime"] = array("ELT", $cond["ectime"]);
        }
        return $condition;
    }


}