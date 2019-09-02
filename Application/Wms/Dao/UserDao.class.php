<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 库存数据
 * Class UserDao
 * @package Wms\Dao
 */
class UserDao extends BaseDao implements BaseDaoInterface {
    private $dbname = "";

    function __construct() {
        $this->dbname = C("WMS_CLIENT_DBNAME");
    }

    //添加数据[name,phone,wxcode,token,infor,warcode]
    public function insert($item) {
        $code = venus_unique_code("U");
        $data = array(
            "user_code"     => $code,
            "user_name"     => $item["name"],
            "user_phone"    => $item["phone"],
            "user_wxcode"   => $item["wxcode"],
            "user_token"    => $item["token"],
            "user_infor"    => $item["infor"],
            "war_code"      => $this->warehousecode
        );
        return M("User")->add($data) ? $code : false;
    }

    //查询
    public function queryByWxCode($wxcode) {
        $condition = array("user_wxcode" => $wxcode);
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')->field('*,wor.war_code,user.war_code as warehousecode')
            ->join("LEFT JOIN {$this->dbname}.wms_worker wor ON wor.wor_token = user.user_token")
            ->join("LEFT JOIN {$this->dbname}.wms_warehouse war ON war.war_code = wor.war_code")
            ->where($condition)
            ->find();
    }

    //查询
    public function queryByToken($token) {
        $condition = array("user_token" => $token);
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')->field('*,wor.war_code,user.war_code as warehousecode')
            ->join("LEFT JOIN {$this->dbname}.wms_worker wor ON wor.wor_token = user.user_token")
            ->join("LEFT JOIN {$this->dbname}.wms_warehouse war ON war.war_code = wor.war_code")
            ->where($condition)
            ->find();
    }

    //查询
    public function queryByPhone($phone) {
        $condition = array("user_phone" => $phone);
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')->field('*,wor.war_code,user.war_code as warehousecode')
            ->join("LEFT JOIN {$this->dbname}.wms_worker wor ON wor.wor_token = user.user_token")
            ->join("LEFT JOIN {$this->dbname}.wms_warehouse war ON war.war_code = wor.war_code")
            ->where($condition)->fetchSql(false)->find();
    }

    //查询
    public function queryByCode($code) {
        $condition = array("user_code" => $code);
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')->field('*,wor.war_code,user.war_code as warehousecode')
            ->join("LEFT JOIN {$this->dbname}.wms_worker wor ON wor.wor_token = user.user_token")
            ->join("LEFT JOIN {$this->dbname}.wms_warehouse war ON war.war_code = wor.war_code")
            ->where($condition)->find();
    }

    //查询
    public function queryListByCondition($condition, $page = 0, $count = 100) {
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')->field('*,wor.war_code,user.war_code as warehousecode')
            ->join("LEFT JOIN {$this->dbname}.wms_worker wor ON wor.wor_token = user.user_token")
            ->join("LEFT JOIN {$this->dbname}.wms_warehouse war ON war.war_code = wor.war_code")
            ->where($condition)->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //总数
    public function queryCountByCondition($condition) {
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')
//            ->field('*,wor.war_code,user.war_code as warehousecode')
//            ->join("LEFT JOIN {$this->dbname}.wms_worker wor ON wor.wor_token = user.user_token")
//            ->join("LEFT JOIN {$this->dbname}.wms_warehouse war ON war.war_code = wor.war_code")
            ->where($condition)->fetchSql(false)->count();
    }

    public function releaseByWxCode($wxcode){
        $condition = array("user_wxcode" => $wxcode);
        $data = array("user_wxcode" => "");
        return M("User")->alias('user')->where($condition)->save($data);
    }

    //更新
    public function updateItemByCode($code, $item) {
        $data = array();
        if (isset($item["name"])) $data["user_name"] = $item["name"];
        if (isset($item["phone"])) $data["user_phone"] = $item["phone"];
        if (isset($item["token"])) $data["user_token"] = $item["token"];
        $condition = array("user_code" => $code);
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')->where($condition)->save($data);
    }
    //更新
    public function updateWxcodeByCode($code, $wxcode) {
        $condition = array("user_code" => $code);
        $condition = $this->conditionFilter($condition);
        $data = array("user_wxcode" => $wxcode,"timestamp"=>venus_current_datetime());
        return M("User")->alias('user')->where($condition)->fetchSql(false)->save($data);
    }

    public function removeByCode($code) {
        $condition = array( "user_code" => $code);
        $condition = $this->conditionFilter($condition);
        return M("User")->alias('user')->where($condition)->fetchSql(false)
            ->setField("user_status", "0");
    }

    private function conditionFilter($cond) {
        $condition = array("user_status"=>1);
        $condition = array_merge($cond,$condition);
        return $condition;
    }
}