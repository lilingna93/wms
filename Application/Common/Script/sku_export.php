<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2018/12/19
 * Time: 14:21
 */
define('IS_MASTER', true);
define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

//在命令行中输入 chcp 65001 回车, 控制台会切换到新的代码页,新页面输出可为中文
$time = venus_script_begin("开始检测库存");

use Wms\Dao\SkuDao;

$warCode = "WA000001";
$skuModel = SkuDao::getInstance($warCode);
$skuInfoData = $skuModel->queryListByCondition(array("status"=>1), 0, 100000);
$data['数据'][] = array(
    "skucode","sku规格","sku单位","spucode","spu数量","spu名字","spu规格","spu单位","spu备注"
);
foreach ($skuInfoData as $skuInfoDatum) {
    $data['数据'][]=array(
      $skuInfoDatum['sku_code'],
      $skuInfoDatum['sku_norm'],
      $skuInfoDatum['sku_unit'],
      $skuInfoDatum['spu_code'],
      $skuInfoDatum['spu_count'],
      $skuInfoDatum['spu_name'],
      $skuInfoDatum['spu_norm'],
      $skuInfoDatum['spu_unit'],
      $skuInfoDatum['spu_mark'],
    );
}

$fileName = \Common\Service\ExcelService::getInstance()->exportExcel($data, '', "002", 1);
if (!empty($fileName)) {
    $time = venus_current_datetime();
    $newFname = "sku.xlsx";
    $moveFileRes = rename("/home/dev/venus/Public/files/002/$fileName", "/home/dev/venus/Public/files/sku/database/$newFname");
    if ($moveFileRes) {
        echo $newFname;
    } else {
        echo "移动失败" . "$newFname";
    }
} else {
    echo "生成文件失败";
    exit;
}