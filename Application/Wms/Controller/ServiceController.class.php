<?php

namespace Wms\Controller;

use Common\Service\PHPRpcService;
use Think\Controller;
use Think\Exception;
use Wms\Dao\GoodsDao;
use Wms\Dao\GoodstoredDao;
use Wms\Dao\ReceiptDao;
use Wms\Dao\SkuDao;
use Wms\Dao\SpuDao;
use Wms\Dao\WarehouseDao;
use Common\Service\ExcelService;
use Wms\Service\AuthService;
use Wms\Service\SkuService;

class ServiceController extends Controller
{

    //正式平台数据接口
    public function api()
    {
        try {

            header('Access-Control-Allow-Origin:*');
            $api = I("post.service");
            list($module, $class, $method) = venus_decode_api_request($api);
            $class = "{$module}\\Service\\{$class}Service";
            //调试
            $token = I("post.token");
            !empty($token) && AuthService::getInstance()->remotelogin($token, false);
            if (class_exists($class)) {
                list($success, $data, $message) = call_user_func(array(new $class(), $method));
                venus_encode_api_result($api, 0, "", $success, $data, $message);
            } else {
                E("提醒:未知API", 1);
            }
        } catch (Exception $e) {
            venus_encode_api_result("", $e->getCode(), $e->getMessage(), false, "", "");
        }
    }

    //正式小程序数据接口
    public function mapi()
    {
        try {
            $api = I("post.service");
            list($module, $class, $method) = venus_decode_api_request($api);
            $class = "{$module}\\Service\\{$class}Service";
            if (class_exists($class)) {
                list($success, $data, $message) = call_user_func(array(new $class(), $method));
                venus_encode_api_result($api, 0, "", $success, $data, $message, session_id());
            } else {
                E("提醒:未知API", 1);
            }
        } catch (Exception $e) {
            venus_encode_api_result("", $e->getCode(), $e->getMessage(), false, "", "", session_id());
        }
    }

    //正式平台文件下载接口
    public function file()
    {
        try {
            header('Access-Control-Allow-Origin:*');
            $fileName = I("post.fname");
            $typeName = I("post.tname");
            $saveName = I("post.sname");
            ExcelService::getInstance()->outPut($typeName, $fileName, $saveName);
        } catch (Exception $e) {
            venus_encode_api_result("", $e->getCode(), $e->getMessage(), false, "", "");
        }
    }

    //正式平台文件打包下载接口
    public function filezip()
    {
        try {
            header('Access-Control-Allow-Origin:*');
            header("Content-type:application/vnd.ms-excel;charset=gb2312");
            $fileName = I("post.fname");
            $typeName = I("post.tname");
            $saveName = I("post.sname");
            ExcelService::getInstance()->outPutZip($typeName, $fileName, $saveName);
        } catch (Exception $e) {
            venus_encode_api_result("", $e->getCode(), $e->getMessage(), false, "", "");
        }
    }


    public function napi()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            $api = $data["service"];
            $data = $data["data"];

            list($module, $class, $method) = venus_decode_api_request($api);
            $class = "{$module}\\Service\\{$class}Service";
            if (class_exists($class)) {
                list($success, $data, $message) = call_user_func(array(new $class(), $method), $data);
                venus_encode_napi_result($api, 0, "", $success, $data, $message);
            } else {
                E("提醒:未知API", 1);
            }
        } catch (Exception $e) {
            venus_encode_napi_result("", $e->getCode(), $e->getMessage(), false, "", "");
        }

//        $data = array(
//            "items"=>array(
//                array("name"=>"name1","value"=>"value1"),
//                array("name"=>"name2","value"=>"value2"),
//            ),
//        );
//        venus_encode_api_result("venus.demo.api.name", 0, "", 1, $data, "message");
    }

    public function testrpc()
    {
//        $result = PHPRpcService::getInstance()->request('bea43f5f7ad39e2d184cb16885043d42', "venus.wms.goods.goods.search", array(
//            "tCode" => "0",
//            "cgCode" => "0",
//            "spName" => ""
//        ));
//        var_dump($result);
//        exit();

//        $api = "venus.wms.accident.return.goods.handle.befo";
//        $param = array(
//            "oCode" => "O40118145151473",
//            "supCode" => "SU00000000000001",
//            "skCode" => "SK0000010",
//            "skCount" => "2.00",//退货sku数量
//            "count" => "30.00",//退货spu数量
//            "spCode" => "SP000010",
//            "skInit" => "9.00",//退货前sku数量
//        );
//        $param = array(
//            "oCode" => "O40118184845536",
//            "supCode" => "SU00000000000001",
//            "skCode" => "SK0000538",
//            "skCount" => "1.00",//退货sku数量
//            "count" => "10.00",//退货spu数量
//            "spCode" => "SP000538",
//            "skInit" => "3.00",//退货前sku数量
//        );
        //        $param = array(
//            "oCode" => "O40118175328910",
//            "supCode" => "SU00000000000002",
//            "skCode" => "SK0000620",
//            "skCount" => "1.00",//退货sku数量
//            "count" => "1.00",//退货spu数量
//            "spCode" => "SP000620",
//            "skInit" => "3.00",//退货前sku数量
//        );
//        $param = array(
//            "oCode" => "O40118175328910",
//            "supCode" => "SU00000000000003",
//            "skCode" => "SK0000797",
//            "skCount" => "2.00",//退货sku数量
//            "count" => "2.00",//退货spu数量
//            "spCode" => "SP000797",
//            "skInit" => "5.00",//退货前sku数量
//        );
//        $param = array(
//            "oCode" => "O40118184845536",
//            "supCode" => "SU31105145203401",
//            "skCode" => "SK0000347",
//            "skCount" => "1.00",//退货sku数量
//            "count" => "1.00",//退货spu数量
//            "spCode" => "SP000347",
//            "skInit" => "2.00",//退货前sku数量
//        );

        $api = "venus.wms.accident.return.goods.handle";
        $param = array(
            "oCode" => "O40118163535928",
            "ogCode" => "G40118163535723",
            "supCode" => "SU00000000000003",
            "skCode" => "SK0000983",
            "skCount" => "2.00",//退货sku数量
            "count" => "2.00",//退货spu数量
            "spCode" => "SP000983",
            "skInit" => "9.00",//退货前sku数量
        );
        $param = array(
            "oCode" => "O40118184845536",
            "ogCode" => "G40118184845316",
            "supCode" => "SU00000000000001",
            "skCode" => "SK0000297",
            "skCount" => "1.00",//退货sku数量
            "count" => "2.00",//退货spu数量
            "spCode" => "SP000297",
            "skInit" => "2.00",//退货前sku数量
        );

        $result = PHPRpcService::getInstance()->request('bea43f5f7ad39e2d184cb16885043d42', $api, $param);
        var_dump($result);
    }

    //小程序下载导出采购单（专用）
    public function externalApi()
    {
        try {

            header('Access-Control-Allow-Origin:*');
            $api = "venus.wms.".I("get.service");
            list($module, $class, $method) = venus_decode_api_request($api);
            $class = "{$module}\\Service\\{$class}Service";
            //调试
//            $token = I("post.token");
//            !empty($token) && AuthService::getInstance()->remotelogin($token, false);
            if (class_exists($class)) {
                list($success, $data, $message) = call_user_func(array(new $class(), $method));
                venus_encode_api_result($api, 0, "", $success, $data, $message);
            } else {
                E("提醒:未知API", 1);
            }
        } catch (Exception $e) {
            venus_encode_api_result("", $e->getCode(), $e->getMessage(), false, "", "");
        }
    }
}
