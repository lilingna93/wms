<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/30
 * Time: 10:32
 */

namespace Erp\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

class SkuDao extends BaseDao implements BaseDaoInterface
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
            "sku_code" => $item['code'],
            "sku_name" => $item['name'],//商品名称
            "sku_norm" => $item['norm'],//规格
            "sku_weight" => $item['weight'],//重量
            "sku_volume" => $item['volume'],//体积
            "sku_unit" => $item['unit'],//计量单位
            "spu_code" => $item['spuCode'],
            "spu_count" => $item['spuCount'],
            "sku_img" => $item['img'],//图片
            "sku_mark" => $item['mark'],
            "sku_from" => $item['from'],
            "sku_inner" => $item['inner'],
            "sku_outer" => $item['outer'],
        );

        return M("erpsku")->add($data);
    }


    public function queryByCode($code)
    {
        return M("erpsku")->alias("sku")
            ->join("left join `wms_erpspu` spu on spu.spu_code=sku.spu_code")
            ->where(array("sku_code" => $code))->fetchSql(false)->find();

    }

    /**
     * @param $condition
     * @return mixed
     * 总条数
     */
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("erpsku")->alias("sku")
            ->join("left join `wms_erpspu` spu on spu.spu_code=sku.spu_code")
            ->where($condition)->count();
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
        return M("erpsku")->alias("sku")
            ->join("left join `wms_erpspu` spu on spu.spu_code=sku.spu_code")
            ->where($condition)->order("sku_code desc")->limit("{$page},{$count}")->fetchSql(false)->select();

    }

    private function conditionFilter($cond)
    {
        $condition = array();
        if (isset($cond["code"]) && !empty($cond["code"])) {
            $condition["sku.sku_code"] = $cond["code"];
        }

        if (isset($cond["outer"]) && !empty($cond["outer"])) {
            $condition["sku.sku_outer"] = $cond["outer"];
        }

        if (isset($cond["inner"]) && !empty($cond["inner"])) {
            $condition["sku.sku_inner"] = $cond["inner"];
        }

        if (isset($cond["name"]) && !empty($cond["name"])) {
            $condition["sku.sku_name"] = array("like", '%' . $cond["name"] . "%");
        }

        if (isset($cond["spucode"]) && !empty($cond["spucode"])) {
            $condition["sku.spu_code"] = $cond["spucode"];
        }

        if (isset($cond["sputype"]) && !empty($cond["sputype"])) {
            $condition["spu.spu_type"] = $cond["sputype"];
        }

        if (isset($cond["spuname"]) && !empty($cond["spuname"])) {
            $condition["spu.spu_name"] = array("like", '%' . $cond["spuname"] . "%");
        }

        if (isset($cond["mark"]) && !empty($cond["mark"])) {
            $condition["sku.sku_mark"] = array("like", '%' . $cond["mark"] . "%");
        }
        return $condition;
    }

    function updateByCode($data, $code)
    {
        return M("Erpsku")->alias('sku')->where(array("sku_code" => $code))->save($data);
    }


    function deleteByCode($code)
    {
        return M("Erpsku")->where(array("sku_code" => $code))->delete();
    }

    function deleteBySpuCode($code)
    {
        return M("Erpsku")->where(array("spu_code" => $code))->delete();
    }
}