<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 工作账户数据
 * Class WorkerDao
 * @package Wms\Dao
 */
class WorkerDao extends BaseDao implements BaseDaoInterface
{
    //添加数据[fname,data,type,worcode]
    /**
     * @param $item
     * @return bool
     */
    public function insert($item)
    {
        $code = venus_unique_code("WO");
        $data = array(
            "wor_code" => $code,
            "wor_name" => $item["name"],
            "wor_rname" => $item["rname"],
            "wor_pwd" => $item["pwd"],
            "wor_token" => venus_gen_token($code),//$item["token"],
            "wor_phone" => $item["phone"],
            "wor_auth" => $item["auth"],
            "war_code" => $this->warehousecode,
        );
        return M("Worker")->add($data) ? $code : false;
    }
    //查询

    /**
     * @param $name
     * @param $pwd
     * @return mixed
     */
    public function queryByNameAndPassword($name, $pwd)
    {
        return M("Worker")->alias('wor')->field('wor_code,wor_name,wor_rname,wor_branch,wor_auth,wor_token,wor_phone,wor.war_code,war_name,war_address,war_postal,war_info')
            ->join("JOIN wms_warehouse war ON war.war_code = wor.war_code")
            ->where(array("wor_name" => $name, "wor_pwd" => $pwd, "wor_status" => 1))
            ->find();
    }
    //查询

    /**
     * @param $token
     * @return mixed
     */
    public function queryByToken($token)
    {
        return M("Worker")->alias('wor')->field('wor_code,wor_name,wor_rname,wor_auth,wor_token,wor_phone,wor.war_code,war_name,war_address,war_postal,war_info')
            ->join("JOIN wms_warehouse war ON war.war_code = wor.war_code")
            ->where(array("wor_token" => $token, "wor_status" => 1))
            ->find();
    }
    //查询

    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code)
    {
        return M("Worker")->alias('wor')->field('wor_code,wor_name,wor_rname,wor_auth,wor_token,wor_phone,wor.war_code,war_name,war_address,war_postal,war_info')
            ->join("JOIN wms_warehouse war ON war.war_code = wor.war_code")
            ->where(array("wor.war_code" => $this->warehousecode, "wor_code" => $code, "wor_status" => 1))
            ->find();
    }
    //查询

    /**
     * @param $cond
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByCondition($cond, $page = 0, $count = 100)
    {

        $condition = array("war_code" => $this->warehousecode, "wor_status" => 1);
        //E(M("Worker")->where($condition)->limit("{$page},{$count}")->fetchSql(true)->select(),1);
        return M("Worker")->where($condition)->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //总数

    /**
     * @param $cond
     * @return mixed
     */
    public function queryCountByCondition($cond)
    {
        $condition = array("war_code" => $this->warehousecode, "wor_status" => 1);
        return M("Worker")->where($condition)->fetchSql(false)->count();
    }

    //更新

    /**
     * @param $code
     * @param $auth
     * @return mixed
     */
    public function updateAuthByCode($code, $auth)
    {
        $condition = array("war_code" => $this->warehousecode, "wor_code" => $code);
        $auth = $this->authorityFilter($auth);
        return M("Worker")->where($condition)
            ->save(array("wor_auth" => $auth, "timestamp" => venus_current_datetime()));
    }

    //删除

    /**
     * @param $code
     * @param $item
     * @return mixed
     */
    public function updateItemByCode($code, $item)
    {
        $condition = array("war_code" => $this->warehousecode, "wor_code" => $code);
        $data = array();
        if (isset($item["name"])) $data["wor_name"] = $item["name"];
        if (isset($item["rname"])) $data["wor_rname"] = $item["rname"];
        if (isset($item["pwd"])) $data["wor_pwd"] = $item["pwd"];
        if (isset($item["token"])) $data["wor_token"] = $item["token"];
        if (isset($item["phone"])) $data["wor_phone"] = $item["phone"];
        if (isset($item["auth"])) {
            $data["wor_auth"] = $this->authorityFilter($item["auth"]);
        }
        return M("Worker")->where($condition)->save($data);
    }

    /**
     * @param $code
     * @return mixed
     */
    public function removeByCode($code)
    {
        $condition = array("war_code" => $this->warehousecode, "wor_code" => $code);
        return M("Worker")->where($condition)
            ->setField("wor_status", 0);
    }

    public function refreshTokenByCode($code)
    {
        $condition = array("war_code" => $this->warehousecode, "wor_code" => $code);
        return M("Worker")->where($condition)
            ->setField("wor_token", venus_gen_token($code));
    }

    private function authorityFilter($auth)
    {

        return $auth;
    }

}