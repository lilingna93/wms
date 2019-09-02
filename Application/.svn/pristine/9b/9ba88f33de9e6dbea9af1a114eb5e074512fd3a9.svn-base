<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/7/12
 * Time: 10:39
 */
ini_set('memory_limit', '2096M');
define('APP_DIR', dirname(__FILE__) . '/../../../../../');
//define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';
vendor("PHPExcel");
echo venus_current_datetime() . PHP_EOL;

//在命令行中输入 chcp 65001 回车, 控制台会切换到新的代码页,新页面输出可为中文
$time = venus_script_begin("记录当前库存");

$wmsWarCode = "WA000001";
$fileName="goods_".(date('Ym01',time())).".json";
$filePath=C("FILE_SAVE_PATH").C("FILE_TYPE_NAME.COMMON")."/".$fileName;
$goodsCount=\Wms\Dao\GoodsDao::getInstance($wmsWarCode)->queryCountByCondition();
$goodsData=\Wms\Dao\GoodsDao::getInstance($wmsWarCode)->queryListByCondition(array(),0,$goodsCount);
$goodsDataArr=array();
$goodsDataArr[$wmsWarCode]['time']=venus_current_datetime();
foreach ($goodsData as $goodsDatum) {
    $goodsDataArr[$wmsWarCode][$goodsDatum["sku_code"]]=array(
      "goodsInit"=>$goodsDatum["goods_init"],
      "goodsCount"=>$goodsDatum["goods_count"],
      "skuInit"=>$goodsDatum["sku_init"],
      "skuCount"=>$goodsDatum["sku_count"],
    );
}

if(!empty($goodsDataArr)){
    if(file_exists($filePath)){
        $fileData=file_get_contents($filePath);
        if($fileData!=json_encode($goodsDataArr)){
            echo "data error";
        }else{
            echo "data success";
        }
    }else{
        echo $filePath.PHP_EOL;
        file_put_contents($filePath,json_encode($goodsDataArr),FILE_APPEND);
        $fileData=file_get_contents($filePath);
        if($fileData!=json_encode($goodsDataArr)){
            echo "put and get data error";
        }else{
            echo "put and get success";
        }
    }


}