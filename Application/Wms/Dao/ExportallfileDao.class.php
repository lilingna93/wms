<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 创建文件日志数据
 * Class ExportallfileDao
 * @package Wms\Dao
 */
class ExportallfileDao extends BaseDao implements BaseDaoInterface
{
    function __construct()
    {
    }

    /**
     * @param $data
     * @return mixed
     */
    public function insert($data)
    {
        return M("fileslog")->add($data);
    }

    public function insert_userdownloadlog($data)
    {
        return M("userdownloadlog")->add($data);
    }

    //查询
    public function queryListByCondition($condition, $page = 0, $count = 1000)
    {
        $condition = $this->conditionFilter($condition);
        return M("fileslog")->alias("f")
            ->where($condition)->order("id desc")
            ->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //总数
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("fileslog")->alias("f")->where($condition)->fetchSql(false)->count();
    }


}