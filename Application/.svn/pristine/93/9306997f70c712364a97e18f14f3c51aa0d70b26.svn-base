<?php

namespace Wms\Service;

use Common\Service\PassportService;
use Common\Service\PHPRpcService;
use Wms\Dao\ProfitDao;
use Wms\Dao\SkuDao;
use Wms\Dao\WarehouseDao;

class SkuService {

    public $waCode;
    function __construct()
    {
//        $workerData = PassportService::getInstance()->loginUser();
//        if(empty($workerData)){
//            venus_throw_exception(110);
//        }
        $this->waCode = 'WA000001';//$this->waCode = $workerData["war_code"];
    }

    //1.SKU搜索
    public function sku_search() {

        $spName = $_POST['data']['spName'];
        $spType = $_POST['data']['spType'];
        $spSubtype = $_POST['data']['spSubtype'];
        $skStatus = $_POST['data']['skStatus'];
        $pageCurrent = $_POST['data']['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数


        if(!empty($spName) && preg_match ("/^[a-z]/i", $spName)){
            $condition['abname'] = $spName;
        }
        if (!empty($spName) && !preg_match ("/^[a-z]/i", $spName)) {//SPU名称
            $condition['%name%'] = $spName;
        }

        if (!empty($spType)) {//一级分类编号
            $condition['type'] = $spType;
        }

        if (!empty($spSubtype)) {//状态（上、下线）
            $condition['subtype'] = $spSubtype;
        }

        if (!empty($skStatus)) {//客户仓库
            $condition['status'] = $skStatus;
        }

        //当前页码
        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $SkuDao = SkuDao::getInstance($this->waCode);
        $totalCount = $SkuDao->queryCountByCondition($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $skuDataList = $SkuDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);
        
        if (empty($skuDataList)) {
            $skuList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $skuList["list"] = array();
        } else {
            $skuList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($skuDataList as $index => $skuItem) {
                if(!empty($skuItem['sku_mark'])){
                  $skMark = "(".$skuItem['sku_mark'].")";
                }
                $skuList["list"][$index] = array(
                        "skCode" => $skuItem['sku_code'],//SKU编号
                        "spCode" => $skuItem['spu_code'],//所属SPU编码
                        "spName" => $skuItem['spu_name'].$skMark,//SPU货品名称
                        "spCount" => $skuItem['spu_count'],//规格数量
                        "skUnit" => $skuItem['sku_unit'],//规格单位
                        "skNorm" => $skuItem['sku_norm'],//规格
                        "skStatus" => $skuItem['sku_status']//状态（上、下线）
                );
            }
        }
        return array(true, $skuList, "");
    }

    //2.所选sku设为上线
    public function status_online() {

        $skCode = $_POST['data']['skCode'];
        $skStatus = $_POST['data']['skStatus'];

        if (empty($skCode)) {
            venus_throw_exception(1, "货品编号不能为空");
            return false;
        }

        if (empty($skStatus)) {
            venus_throw_exception(1, "货品状态不能为空");
            return false;
        }

        $skuStatusUpd = SkuDao::getInstance($this->waCode)->updateStatusCodeByCode($skCode, $skStatus);
        if ($skuStatusUpd) {
            $this->release_latestsku();
            $success = true;
            $message = "所选sku上线成功";
        } else {
            $success = false;
            $message = "所选sku上线失败";
        }
        return array($success, "", $message);
    }

    //3.所选sku设为下线
    public function status_offline() {

        $skCode = $_POST['data']['skCode'];
        $skStatus = $_POST['data']['skStatus'];

        if (empty($skCode)) {
            venus_throw_exception(1, "货品编号不能为空");
            return false;
        }

        if (empty($skStatus)) {
            venus_throw_exception(1, "状态不能为空");
            return false;
        }

        $skuStatusUpd = SkuDao::getInstance($this->waCode)->updateStatusCodeByCode($skCode, $skStatus);
        if ($skuStatusUpd) {
            $this->release_latestsku();
            $success = true;
            $message = "所选sku下线成功";
        } else {
            $success = false;
            $message = "所选sku下线失败";
        }
        return array($success, "", $message);
    }


    //释放最新的sku数据
    public function release_latestsku(){
        //$skuFilePath = "/home/dev/venus/Public/files/sku/latestsku.txt";
        $skuFilePath = C("FILE_SAVE_PATH")."sku/latestsku.txt";
        if(file_exists($skuFilePath)){
            unlink($skuFilePath);
            S(C("SKU_VERSION_KEY"),null);
        }
    }

    //获取最新的sku数据
    public function latestsku(){
        $userData = PassportService::loginUser();
        if(empty($userData)){
            venus_throw_exception(110);
        }
        $isExternalUser = $userData["user_is_external"];
        if($isExternalUser=="2"){
            return $this->externalsku();
        }

        $warcode = $userData['warehousecode'];
        //$skuFilePath = "/home/dev/venus/Public/files/sku/latestsku.txt";
        $skuFilePath = C("FILE_SAVE_PATH")."sku/latestsku.txt";
        if(file_exists($skuFilePath)){
            $skuData = file_get_contents($skuFilePath);
            return array(true, $skuData,  "SKU数据已经存在");
        }

        //重新生成相应文件
        $typedict = C("SPU_TYPE_DICT");
        $subtypedict = C("SPU_SUBTYPE_DICT");
        $condition=array("status"=>1);
        $skuList = SkuDao::getInstance($warcode)->queryListByCondition($condition,0,3000);
        $dict = array();
        $map = array();
        foreach ($skuList as $skuItem){
            $type       = $skuItem["spu_type"];     //一级类型
            $typename   = $typedict[$type];         //一级名称
            $subtype    = $skuItem["spu_subtype"];  //二级类型
            $subtypename = $subtypedict[$subtype];  //二级名称
            $skucode    = $skuItem["sku_code"];     //spu编号

            $profit     = $skuItem["profit_price"];  //利润价格！！
            $sprice     = $skuItem["spu_sprice"];   //销售价
            $count      = $skuItem["spu_count"];    //所含spu数量，1,0.1,0.01，因为价格和利润都是以spu价格计算的

            $totalprice = venus_calculate_sku_price_by_spu($sprice,$count,$profit);
            $totalprice = ($totalprice==intval($totalprice))?intval($totalprice):round($totalprice,2);
            $mark = $skuItem["spu_mark"];
            if(!isset($dict[$skucode])){
                $dict[$skucode] = array(
                    "spName"    =>  $skuItem["spu_name"].(!empty($mark)?"[{$mark}]":""),
                    "spAbName"  =>  $skuItem["spu_abname"],
                    "skBrand"   =>  $skuItem["spu_brand"],
                    "skCode"    =>  $skuItem["sku_code"],//
                    "skNorm"    =>  $skuItem["sku_norm"],//规格数据
                    "skUnit"    =>  $skuItem["sku_unit"],
                    "skCunit"    =>  $skuItem["spu_cunit"],
                    "skTotalPrice"  =>  $totalprice,
                    "skImg"  =>  (empty($skuItem["spu_img"])?"_":$skuItem["spu_code"]).".jpg?_=".C("SKU_IMG_VERSION"),
                );
            }
            if(!isset($map[$type])){
                $map[$type] = array(
                    "tCode"=>$type,
                    "tName"=>$typename,
                    "{$type}"=>array(
                        "0"=>array(
                            "cName"=>"全部",
                            "cCode"=>0,
                            "list"=>array()
                        )
                    )
                );
            }
            $map[$type][$type]["0"]["list"][] = $skucode;

            if(!isset($map[$type][$type][$subtype])){
                $map[$type][$type][$subtype] = array(
                    "cName"=>"{$subtypename}",
                    "cCode"=>"{$subtype}",
                    "list"=>array()
                );
            }
            $map[$type][$type][$subtype]["list"][] = $skucode;
        }
        $list = array_values($map);
        foreach ($list as $index=>$item ){
            $key = $item["tCode"];
            $list[$index][$key] = array_values($item[$key]);
        }
        $skuData = json_encode(array("R"=>$list, "D"=>$dict));
        file_put_contents($skuFilePath, $skuData);
        S(C("SKU_VERSION_KEY"),md5($skuData),3600*24*365);
        return array(true,$skuData,"");

    }


    //获取最新的sku数据
    public function externalsku(){
        $userData = PassportService::loginUser();
        if(empty($userData)){
            venus_throw_exception(110);
        }
        $warcode = $userData['warehousecode'];
        $exwarehousecode = $userData['war_code'];

        $skuFilePath = C("FILE_SAVE_PATH")."sku/externalsku.{$exwarehousecode}.txt";
        if(file_exists($skuFilePath)){
            $skuData = file_get_contents($skuFilePath);
            return array(true, $skuData,  "SKU数据已经存在");
        }

        //重新生成相应文件
        $typedict = C("SPU_TYPE_DICT");
        $subtypedict = C("SPU_SUBTYPE_DICT");
        //$condition=array("status"=>1);
        //$skuList = SkuDao::getInstance($warcode)->queryListByCondition($condition,0,3000);
        $sql = "SELECT SP.*,SK.*,SE.spu_eprice FROM wms_skuexternal SE JOIN wms_spu SP ON SP.spu_code = SE.spu_code JOIN wms_sku SK ON SK.sku_code = SE.sku_code WHERE SE.sku_status = 1 AND SE.war_code ='{$exwarehousecode}' ORDER BY SP.spu_subtype ASC LIMIT 3000";
        $skuList = M()->query($sql);

        $dict = array();
        $map = array();
        foreach ($skuList as $skuItem){
            $type       = $skuItem["spu_type"];     //一级类型
            $typename   = $typedict[$type];         //一级名称
            $subtype    = $skuItem["spu_subtype"];  //二级类型
            $subtypename = $subtypedict[$subtype];  //二级名称
            $skucode    = $skuItem["sku_code"];     //spu编号

            $profit     = 0;//$skuItem["profit_price"];  //利润价格！！
            $sprice     = $skuItem["spu_eprice"];//$skuItem["spu_sprice"];   //销售价
            $count      = $skuItem["spu_count"];    //所含spu数量，1,0.1,0.01，因为价格和利润都是以spu价格计算的

            $totalprice = venus_calculate_sku_price_by_spu($sprice,$count,$profit);
            $totalprice = ($totalprice==intval($totalprice))?intval($totalprice):round($totalprice,2);
            $mark = $skuItem["spu_mark"];
            if(!isset($dict[$skucode])){
                $dict[$skucode] = array(
                    "spName"    =>  $skuItem["spu_name"].(!empty($mark)?"[{$mark}]":""),
                    "spAbName"  =>  $skuItem["spu_abname"],
                    "skBrand"   =>  $skuItem["spu_brand"],
                    "skCode"    =>  $skuItem["sku_code"],//
                    "skNorm"    =>  $skuItem["sku_norm"],//规格数据
                    "skUnit"    =>  $skuItem["sku_unit"],
                    "skCunit"    =>  $skuItem["spu_cunit"],
                    "skTotalPrice"  =>  $totalprice,
                    "skImg"  =>  (empty($skuItem["spu_img"])?"_":$skuItem["spu_code"]).".jpg?_=".C("SKU_IMG_VERSION"),
                );
            }
            if(!isset($map[$type])){
                $map[$type] = array(
                    "tCode"=>$type,
                    "tName"=>$typename,
                    "{$type}"=>array(
                        "0"=>array(
                            "cName"=>"全部",
                            "cCode"=>0,
                            "list"=>array()
                        )
                    )
                );
            }
            $map[$type][$type]["0"]["list"][] = $skucode;

            if(!isset($map[$type][$type][$subtype])){
                $map[$type][$type][$subtype] = array(
                    "cName"=>"{$subtypename}",
                    "cCode"=>"{$subtype}",
                    "list"=>array()
                );
            }
            $map[$type][$type][$subtype]["list"][] = $skucode;
        }
        $list = array_values($map);
        foreach ($list as $index=>$item ){
            $key = $item["tCode"];
            $list[$index][$key] = array_values($item[$key]);
        }
        $skuData = json_encode(array("R"=>$list, "D"=>$dict));
        file_put_contents($skuFilePath, $skuData);
        S("EXTERNAL_SKU_VERSION.{$exwarehousecode}",md5($skuData),3600*24*365);
        return array(true,$skuData,"");

    }

    public function latestminisku(){

        $post = json_decode($_POST['data'], true);
        $version = $post["version"];
        if(empty($version)){
            $result = PHPRpcService::getInstance()->request(md5(venus_current_datetime()), "venus.wms.sku.latestsku", array());
            return array(true,$result["data"],"");
        }else{
            //$result = PHPRpcService::getInstance()->request(md5(venus_current_datetime()), "venus.wms.sku.latestminisku", array());
            //return array(true,$result["data"],"");
            //$data = '{"R":[{"tCode":"102","tName":"\u7c73\u9762\u7cae\u6cb9"},{"tCode":"104","tName":"\u8c03\u5473\u5e72\u8d27"},{"tCode":"106","tName":"\u9152\u6c34\u996e\u6599"},{"tCode":"108","tName":"\u732a\u725b\u7f8a\u8089"},{"tCode":"110","tName":"\u9e21\u9e2d\u79bd\u86cb"},{"tCode":"112","tName":"\u6c34\u4ea7\u51bb\u8d27"},{"tCode":"114","tName":"\u4f11\u95f2\u98df\u54c1"},{"tCode":"116","tName":"\u852c\u83dc\u74dc\u679c"}],"D":[]}';
            $data = '{
    "R": [
        {
            "tCode": "102",
            "tName": "米面粮油"
        },
        {
            "tCode": "104",
            "tName": "调味干货"
        },
        {
            "tCode": "106",
            "tName": "酒水饮料"
        },
        {
            "tCode": "108",
            "tName": "猪牛羊肉"
        },
        {
            "tCode": "110",
            "tName": "鸡鸭禽蛋"
        },
        {
            "tCode": "112",
            "tName": "水产冻货"
        },
        {
            "tCode": "114",
            "tName": "休闲食品"
        },
        {
            "tCode": "116",
            "tName": "蔬菜瓜果"
        },
        {
            "tCode": "118",
            "tName": "日常物料"
        }
    ],
    "D": []
}';
            return array(true,$data,"");
        }
    }

}



