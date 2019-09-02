<?php
ini_set('memory_limit', '356M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
//define('APP_DIR', '/home/dev/venus/');//测试站运行脚本路径
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\SpuDao;
use Wms\Dao\SkuDao;
use Wms\Dao\SupplierDao;
use Common\Service\ExcelService;

$time = venus_script_begin("抓取新发地SKU数据");

function get_td_array($table) {
    $table = preg_replace("'<table[^>]*?>'si","",$table);
    $table = preg_replace("'<tr[^>]*?>'si","",$table);
    $table = preg_replace("'<td[^>]*?>'si","",$table);
    $table = str_replace("</tr>","{tr}",$table);
    $table = str_replace("</td>","{td}",$table);
    //去掉 HTML 标记
    /*$table = preg_replace("'<[/!]*?[^<>]*?>'si","",$table);*/
    //去掉空白字符
    $table = preg_replace("'([rn])[s]+'","",$table);
    $table = str_replace(" ","",$table);
    $table = str_replace(" ","",$table);
    $table = explode('{tr}', $table);
    array_pop($table);
    foreach ($table as $key=>$tr) {
        $td = explode('{td}', $tr);
        array_pop($td);
        $td_array[] = $td;
    }
    return $td_array;
}

$spName = array("猪肠","猪肚","猪肺","猪肝","猪口条","猪心",
    "猪腰子","羊肚","羊肺","羊肝","牛百叶","牛黄喉","鲢鱼","鲫鱼","多宝鱼",
    "桂鱼","淡水鲈鱼","黑鱼","武昌鱼","猪皮","肥膘肉","鲶鱼","羊蝎子","羊蝎子（精品）",
    "羊小腿（羊棒）","羊小腿（羊棒）","羊尾巴油","猪板油","茼蒿","韭菜","香菜","茴香",
    "韭黄","韭菜苔","蒜黄","油菜","盖菜","香芹","芥兰","青蒜","苦菊","蒿子秆","苋菜",
    "苋菜","油麦菜","团生菜","木耳菜","菊花菜","空心菜","穿心莲","鸡毛菜","鸡毛菜",
    "豌豆苗","娃娃菜","娃娃菜","小白菜","小白菜","奶白菜","菠菜","菠菜","茄子","茄子","新土豆",
    "大白菜","尖椒","线椒","彩椒","杭椒","小米椒","美人椒","秋葵","葱","番茄","圣女果","圣女果（千禧）","黄瓜","苦瓜","凉瓜","丝瓜","黄金瓜（金蜜）","南瓜","西葫芦","冬瓜",
    "扁豆（精）","豇豆","豆王","荷兰豆","樱桃萝卜","白萝卜","胡萝卜","卞萝卜","青萝卜","心里美",
    "黄葱头","红葱头","鲜玉米","鲜玉米","绿豆芽","黄豆芽","芹菜","西芹","绿菜花","菜花","散菜花",
    "芦笋","芋头","蒜苗","蒜米","大蒜","藕","红薯（新粤）","紫薯","铁棍山药","毛豆","小葱","香菇",
    "杏鲍菇","金针菇","金针菇","口蘑","新土豆","雪里红","紫甘蓝","黄心菜","茭白","香椿","松花蛋",
    "国光苹果","芦柑","甜王","南美、菲律宾香蕉","国产香蕉","鸭梨","晓蜜","柠檬","火龙果","白玉菇",
    "百合","香水菠萝","散叶生菜","冬笋","冬枣","西州密","紫菜苔","红心蜜柚","淘汰鸡（净膛）","乌鸡（净膛）","仔鸡（净膛）","水仙芒果","猕猴桃（徐香）","牛骨","库尔勒香梨","牛舌","去骨羊前腿","去骨羊前腿","羊排骨","羊腩","前臀尖（瘦）","后臀尖（瘦）","前肘","护心肉",
    "通脊","纯排骨","五花肉（瘦）","红提葡萄","红颜草莓","白玉菇","鸡胸","阳光玫瑰葡萄","牛前腱",
    "牛林","前臀尖（肥）","后臀尖（肥）","纯腔骨","冰糖橙","菠菜","泰国山竹","红心火龙果",
    "东北香瓜","猪蹄","腔排骨","木瓜","泰国香蕉","荠菜","羊角蜜","棒骨","山楂","银芽","荔枝王",
    "百香果","牛蹄筋","鱼腥草叶","生鸭蛋","黄油桃","油桃（精品）","伊丽莎白","木耳菜","蚕豆仁");


// for($i=1; $i<=12117; $i++){
// $url = 'http://www.xinfadi.com.cn/marketanalysis/0/list/'.$i.'.shtml';
foreach($spName as $val) {
    $url = "http://www.xinfadi.com.cn/marketanalysis/0/list/1.shtml?prodname=" . $val . "&begintime=&endtime=";
    $html = file_get_contents($url);
    $arr = get_td_array($html);

    unset($arr[0]);
    unset($arr[1]);

    $newArray = array();
    foreach ($arr as $val) {
        $skuName = trim($val[0]);
        $skuNorm = stripslashes($val[4]);

        $cond = array(
            "sku_name" => $skuName,
            "sku_norm" => $skuNorm,
            "timestamp" => $val[6],
        );
        $selectSku = queryAllList($cond);
        if(empty($selectSku)){
            $newArray = array(
                "sku_name" => $skuName,
                "minimum_price" => $val[1],
                "average_price" => $val[2],
                "maximum_price" => $val[3],
                "sku_norm" => $skuNorm,
                "sku_unit" => $val[5],
                "timestamp" => $val[6],
            );
            $insertsql = insert($newArray);
        }

    }
}

function insert($item) {
    $data = array(
        "sku_name" => $item['sku_name'],
        "minimum_price" => $item['minimum_price'],
        "average_price" => $item['average_price'],//文件存放目录
        "maximum_price" => $item['maximum_price'],//所属部门：1.市场部 2.采购部 3.财务部 4.仓配部
        "sku_norm" => $item['sku_norm'],
        "sku_unit" => $item['sku_unit'],
        "timestamp" => $item['timestamp'],
    );
    return M("primaryxfdsku")->add($data) ? true : false;
}

function queryAllList($cond) {
    if(isset($cond['sku_name'])){
        $condition['sku_name']  = $cond['sku_name'];
    }
    if(isset($cond['sku_norm'])){
        $condition['sku_norm']  = $cond['sku_norm'];
    }
    if(isset($cond['timestamp'])){
        $condition['timestamp']  = $cond['timestamp'];
    }
    return M("primaryxfdsku")
        ->where($condition)->fetchSql(false)->select();
}

venus_script_finish($time);
exit();

