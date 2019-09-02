<?php
define('IS_MASTER', true);
define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\SpuDao; 
use Wms\Dao\SkuDao;
use Wms\Dao\GoodsDao;
use Common\Service\ExcelService;

$time = venus_script_begin("开始检测商品库存报警值");
$waCode = 'WA000001';
$goodsData = GoodsDao::getInstance($waCode)->queryAllListByCondition('', 0, 2000);
foreach($goodsData as $val){
    $sku_code = $val['sku_code'];
    $sku_name = $val['spu_name'];
    $count = $val['sku_count'];
    $firstWarn = $val['sku_warning_1'];
    $secondWarn = $val['sku_warning_2'];
    if($count < $secondWarn ){
        $text_warning_1 .= '编码：'.$sku_code.'--名称：'.$sku_name.'--当前库存量：'.$count.'<br/>';
    }elseif($count < $firstWarn){
        $text_warning_2 .= '编码：'.$sku_code.'--名称：'.$sku_name.'--当前库存量：'.$count.'<br/>';
    }
}

$date = date("Y-m-d");
$title = $date.',测试服务器商品监控预警报告';
$content = '库存严重不足：<br/>'.$text_warning_1.'<br/>-------------------------------<br/>库存不足：<br/>'.$text_warning_2;
$address = array(
    "kun.zhao@shijijiaming.com",
    /*"panjing@shijijiaming.com",
    "wangyantao@shijijiaming.com",
    "jinwei.cao@shijijiaming.com",
    "yu.gao@shijijiaming.com",
    "xiaolong.hu@shijijiaming.com",*/
);
if(strlen($text_warning_1) < 1 && strlen($text_warning_2) < 1){
    return;
}
sendMailer($title, $content, $address);
echo '----------商品预警监测完成---------';//二级






