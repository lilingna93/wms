<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

class ReportdownloadDao extends BaseDao implements BaseDaoInterface {

    function __construct() {
    }

    //查询
    /**
     * @param $data
     * @return mixed
     */
    public function insert($item) {
        $data = array(
            "file_name" => $item['fname'],
            "save_file_name" => $item['sfname'],
            "storage_catalogue" => $item['scatalogue'],//文件存放目录
            "subordinate_departments" => $item['sdepartments'],//所属部门：1.市场部 2.采购部 3.财务部 4.仓配部
            "timestamp" => venus_current_datetime(),
        );
        return M("fileslog")->add($data) ? true : false;
    }

    //用户下载记录
    public function insert_userdownloadlog($item)
    {
        $data = array(
            "fileslog_id" => $item['fid'],//文件id
            "username" => $item['uname'],
            "timestamp" => venus_current_datetime(),
        );
        return M("userdownloadlog")->add($data) ? true : false;
    }


    public function queryListByCondition($cond, $page = 0, $count = 100)
    {

        $condition['subordinate_departments'] = $cond['sdepartments'];
        return M("fileslog")->where($condition)->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    public function queryCountByCondition($cond)
    {
        $condition['subordinate_departments'] = $cond['sdepartments'];
        return M("fileslog")->where($condition)->fetchSql(false)->count();
    }

}