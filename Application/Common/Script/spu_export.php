<?php
ini_set('memory_limit', '2028M');
//define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DIR', '/home/dev/venus/');//测试站运行脚本路径
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Common\Service\ExcelService;

$time = venus_script_begin("新发地sku数据");

$skuDataList = queryListToxinfadisku();
$header = array( "货品名称", "规格", "单位");
foreach ($skuDataList as $index => $spuItem) {
    $spuList = array(
        "skName" => $spuItem['sku_name'],
        "spNorm" => $spuItem['sku_norm'],
        "skUnit" => $spuItem['sku_unit'],
    );
    $spuBprice['新发地sku'][] = array(
        $spuList['skName'], $spuList['spNorm'], $spuList['skUnit']
    );
}
$fileName = ExcelService::getInstance()->exportExcel($spuBprice, '', "001");
if (!empty($fileName)) {
    $time = venus_current_datetime();
    $newFname = "xinfadispu.xlsx";
    $moveFileRes = rename("/home/dev/venus/Public/files/002/$fileName", "/home/dev/venus/Public/spufiles/$newFname");
    if ($moveFileRes) {
        echo $newFname;
    } else {
        echo "移动失败" . "$newFname";
    }
} else {
    echo "生成文件失败";
    exit;
}
function queryListToxinfadisku()
{
    return M("xinfadisku")->distinct(true)->field('*,sku_name,sku_norm')
            ->fetchSql(false)->select();
}








