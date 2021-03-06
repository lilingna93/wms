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

$time = venus_script_begin("开始上架货品");
//读取excel
vendor("PHPExcel.PHPExcel");
vendor("PHPExcel.Reader.Excel2007");
/*$arr = array(
    '1' => array('online_milk.xlsx'),
    '2' => array('online.xlsx'),
    '4' => array(''),
    '3' => array('online.xlsx'),
    '5' => array('online_milk.xlsx'),
);*/
$arr = array(
    '1' => array('Monday.xlsx'),
    '2' => array('Tuesday.xlsx'),
    '4' => array('Wednesday.xlsx'),
    '3' => array('Thursday.xlsx'),
    '5' => array('Friday.xlsx'),
);

$week = date('w');
foreach($arr as $key => $val){
    if($key == $week){
        foreach($val as $v){
            $filePath = C("FILE_TPLS").$v;
            //建立reader对象
            $PHPReader = new \PHPExcel_Reader_Excel2007();
            if(!$PHPReader->canRead($filePath)){
                $PHPReader = new \PHPExcel_Reader_Excel5();
                if(!$PHPReader->canRead($filePath)){
                    echo 'no Excel';
                    return ;
                }
            }
            //读取excel文件
            $PHPExcel = $PHPReader->load($filePath);
            $currentSheet = $PHPExcel->getSheet(0);
            $allRow = $currentSheet->getHighestRow();
            for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){
                for($colIndex='A';$colIndex<='A';$colIndex++){
                    $addr = $colIndex.$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText)    
                        $cell = $cell->__toString();        
                    $data[][]=$cell;
                }
            }
        }
    }
}
$waCode = 'WA000001';
foreach($data as $val){
    $skCode = $val[0];
    if($skCode == null || empty($skCode)){
        continue;
    }
    $skStatus = 1;
    $skuStatusUpd = SkuDao::getInstance($waCode)->updateStatusCodeByCode($skCode, $skStatus);
    if(!$skuStatusUpd){
        //修改状态未成功的记录日志
        echo '修改上线失败，sku_code = '.$skCode.'<br/>';
    }
}
$skuFilePath = C("FILE_SAVE_PATH")."sku/latestsku.txt";
if(file_exists($skuFilePath)){
    unlink($skuFilePath);
    S(C("SKU_VERSION_KEY"),null);
    
}
echo '----------上架完成---------';






