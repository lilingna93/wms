<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/30
 * Time: 10:25
 */

namespace Erp\Dao;


use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * Class SpuDao
 * @package Erp\Dao
 * erpspu数据层
 */
class SpuDao extends BaseDao implements BaseDaoInterface
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
        $data = array(
            "spu_code" => $item['code'],
            "spu_name" => $item['name'],//商品名称
            "spu_type" => $item['type'],//分类：1商品；2外包装箱；3内包装纸袋；4赠品
            "spu_storetype" => $item['storetype'],//保存方式
            "spu_brand" => $item['brand'],//品牌
            "spu_unit" => $item['unit'],//计量单位
            "spu_sellmode" => $item['sellmode'],//售卖方式
            "spu_mark" => $item['mark'],
            "spu_img" => $item['img'],
        );

        return M("erpspu")->add($data) ? $item['code'] : false;
    }

    /**
     * @param $condition
     * @return mixed
     * 总条数
     */
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("erpspu")->alias("spu")->where($condition)->count();
    }

    public function queryByCode($code)
    {
        return M("erpspu")
            ->alias("spu")
            ->where(array("spu_code" => $code))->fetchSql(false)->find();

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
        return M("erpspu")
            ->alias("spu")
            ->where($condition)->order("spu_code desc")->limit("{$page},{$count}")->fetchSql(false)->select();

    }

    private function conditionFilter($cond)
    {
        $condition = array();
        if (isset($cond["code"]) && !empty($cond["code"])) {
            $condition["spu.spu_code"] = $cond["code"];
        }


        if (isset($cond["name"]) && !empty($cond["name"])) {
            $condition["spu.spu_name"] = array("like", '%' . $cond["name"] . "%");
        }

        if (isset($cond["mark"]) && !empty($cond["mark"])) {
            $condition["spu.spu_mark"] = array("like", '%' . $cond["mark"] . "%");
        }

        if (isset($cond["brand"]) && !empty($cond["brand"])) {
            $condition["spu.spu_brand"] = array("like", '%' . $cond["brand"] . "%");
        }

        if (isset($cond["type"]) && !empty($cond["type"])) {
            $condition["spu.spu_type"] = $cond["type"];
        }

        if (isset($cond["unit"]) && !empty($cond["unit"])) {
            $condition["spu.spu_unit"] = $cond["unit"];
        }
        return $condition;
    }

    function deleteByCode($code)
    {
        return M("Erpspu")->where(array("spu_code" => $code))->delete();
    }
}