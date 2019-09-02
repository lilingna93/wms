<?php
namespace Wms\Controller;
use Think\Controller\RpcController;

use Think\Exception;
use Wms\Dao\GoodsDao;
use Wms\Dao\GoodstoredDao;
use Wms\Dao\ReceiptDao;
use Wms\Dao\SkuDao;
use Wms\Dao\SpuDao;
use Wms\Dao\WarehouseDao;
use Common\Service\ExcelService;
use Wms\Service\AuthService;

class RemoteController extends RpcController{

    //正式数据接口
    public function request($param) {
        try {

            $api = $param["service"];
            $token = $param["token"];
            list($module, $class, $method) = venus_decode_api_request($api);
            $class = "{$module}\\Service\\{$class}Service";
            if(!isset($token) || !AuthService::getInstance()->remotelogin($token)){
                E("提醒:未知TOKEN", 1);
            }else{
                unset($param["token"]);
            }
            if (class_exists($class)) {
                
                list($success, $data, $message) = call_user_func(array(new $class(),$method),$param);
                return venus_encode_rpc_result($api, 0, "", $success, $data, $message);
            } else {
                E("提醒:未知API", 1);
            }
        } catch (Exception $e) {
            return venus_encode_rpc_result("", $e->getCode(), $e->getMessage(), false, "", "");
        }
    }
}
