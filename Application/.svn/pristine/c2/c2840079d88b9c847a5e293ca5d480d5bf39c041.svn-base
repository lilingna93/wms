<?php
namespace Common\Service;

class PHPRpcService {
    //保存类实例的静态成员变量
    private static $_instance;

    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    private $client;

    /**
     * 构造函数
     * @access public
     */
    public function __construct() {
        Vendor('phpRPC.phprpc_client');
        $this->client = new \PHPRPC_Client();
        $this->client->useService(C('WMS_REMOTE_SERVICE'));
    }

    public function request($token,$api,$data) {
        return $this->client->request(array(
            "token"=>$token,//token
            "service"=>$api,           //api
            "data"=>$data,
        ));
    }


}