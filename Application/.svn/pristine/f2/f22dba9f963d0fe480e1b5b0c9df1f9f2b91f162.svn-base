<?php
define('IS_MASTER', true);
define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Common\Service\ExcelService;
use Common\Service\PassportService;
use Erp\Dao\ShopordersDao;
use Erp\Dao\ShoporderdetailDao;
use Erp\Service\PrizeService;

//查询所有导入的订单，状态为1的订单【订单详情导入后将订单表状态改为1】
\Think\Log::write('开始日志记录----come in','zk0624-a');
$order = ShopordersDao::getInstance();
$detail = ShoporderdetailDao::getInstance();
$status = 0;
$data = $order->queryBystatus($status);
if(empty($data)){
    return array(false, '', '还没有要拆分的订单哦');
}
//循环查询每笔订单的信息，并进行拆分
foreach($data as $v){
    \Think\Log::write('订单数据'.json_encode($v),'zk0624-a');
    $message = $v['seller_message'];
    $message = explode(',', $message);
    $orderData['tradenum'] = $v['tradenum'];
    $orderData['logistics_status'] = 1;
    if(in_array('1', $message)){
        $orderData['order_id'] = $v['order_id'].'-a';
        $orderData['seller_message'] = '包装用防水袋';
        $orderData['merchant'] = '0.5KG米砖';
        $result = $detail->insideAdd($orderData);
    }elseif(in_array('2', $message)){
        $orderData['order_id'] = $v['order_id'].'-a';
        $orderData['seller_message'] = '包装用防水袋';
        $orderData['merchant'] = '食盐一袋';
        $result = $detail->insideAdd($orderData);
    }else{
        $arr100 = array();
        $arr200 = array();
        $sum = 0; 
        \Think\Log::write('商品sum----'.json_encode($sum),'zk0624-E');
        //将所有的货品整理在一起，再进行拆单
        //赠品货品
        /*for($i=0;$i<count($message);$i++){
            $number = $message[$i];
            $content = C('GIFT_RULE'.$number);
            \Think\Log::write('商品SKU'.json_encode($content),'zk0624-A');
            $merchant = $content['merchant'];
            //查询sku标识 100，直接发包裹，200，两个发一个， 300，其他 
            $skuData = $detail->queryBySku($merchant);
            if($skuData['sku_mark'] == 100){
                $arr100[]['sku_name'] = $skuData['sku_name'];
                $arr100[]['sku_number'] = 1;
            }elseif ($skuData['sku_mark'] == 200) {
                $arr200[]['sku_name'] = $skuData['sku_name'];
                $arr200[]['sku_number'] = 1;
            }        
        }*/
        //商品货品
        $goodsData = $detail->orderDetail($v['tradenum']);
        \Think\Log::write('商品货品'.json_encode($goodsData),'zk0619-b');
        for($i=0;$i<count($goodsData);$i++){
            \Think\Log::write('商品SKU'.json_encode($goodsData[$i]['merchant_code']),'zk0624-B');
            $skuData = $detail->queryBySku($goodsData[$i]['merchant_code']);
            if($skuData['sku_mark'] == 100){
                $arr100[$i]['sku_name'] = $skuData['sku_name'];
                $arr100[$i]['sku_number'] = $goodsData[$i]['number'];
            }elseif ($skuData['sku_mark'] == 200) {
                $arr200[$i]['sku_name'] = $skuData['sku_name'];
                $arr200[$i]['sku_number'] = $goodsData[$i]['number'];
            }
        }
        \Think\Log::write('数组100-'.json_encode($arr100),'zk0624-C');
        \Think\Log::write('数组200-'.json_encode($arr200),'zk0624-D');

        //开始拆单，拣货单分配
        //先拆100单
        
        for($i=0;$i<count($arr100);$i++){
            \Think\Log::write('数量100-'.json_encode($sum),'zk0619-h');
            \Think\Log::write('拆单100-'.json_encode($i),'zk0619-e');
            if($arr100[$i]['sku_number'] < 2){
                $orderData['order_id'] = $v['order_id'].'-'.getLetter($sum);
                if($i == 0){
                    $orderData['seller_message'] = $message;
                    $orderData['merchant'] = $arr100[$i]['sku_name'];
                    $result = $detail->insideAdd($orderData);     
                }else{
                    $orderData['seller_message'] = '';
                    $orderData['merchant'] = $arr100[$i]['sku_name'];
                    $result = $detail->insideAdd($orderData); 
                }
                $sum += 1;
            }else{
                for($j=0;$j<$arr100[$i]['sku_number'];$j++){
                    $orderData['order_id'] = $v['order_id'].'-'.getLetter($sum);
                    if($i == 0){
                        $orderData['seller_message'] = $message;
                        $orderData['merchant'] = $arr100[$i]['sku_name'];
                        $result = $detail->insideAdd($orderData);     
                    }else{
                        $orderData['seller_message'] = '';
                        $orderData['merchant'] = $arr100[$i]['sku_name'];
                        $result = $detail->insideAdd($orderData); 
                    }
                    $sum += 1;
                }
            }
        }
        \Think\Log::write('商品100----'.json_encode($sum),'zk0624-E100');
        //再拆200单
        //将200单按 数量降序排序得到新数组
        $messages = '';//拣货单明细
        $numbers = 0;
        $sums = 0;
        for($i=0;$i<count($arr200);$i++){
            $number200 = $arr200[$i]['sku_number'];
            if( $number200< 2){
                $numbers = $numbers + 1;
                if($numbers == 2){
                    $messages .= '--'.$arr200[$i]['sku_name'];
                    $orderData['merchant'] = $messages;
                    $orderData['order_id'] = $v['order_id'].'-'.getLetter($sums);
                    $result = $detail->insideAdd($orderData);
                    $sums = $sums + 1;  
                    $messages = '';  
                    $numbers = 0;   
                }else{
                    $messages .= $arr200[$i]['sku_name']; 
                    $numbers = 1;     
                }
            }else{
                $cc = floor( $number200 / 2 );
                for($j=0;$j<$cc;$j++){
                   $orderData['order_id'] = $v['order_id'].'-'.getLetter($sums);
                   $orderData['seller_message'] = '';
                   $orderData['merchant'] = $arr200[$i]['sku_name'];
                   $orderData['num'] = 2;
                   $result = $detail->insideAdd($orderData);
                   $sums = $sums + 1;       
                }
                $dd = $number200 % 2;
                if($dd == 1){
                    $numbers += 1;
                    if($numbers == 2){
                        $messages .= '--'.$arr200[$i]['sku_name'];
                        $orderData['order_id'] = $v['order_id'].'-'.getLetter($sums);
                        $orderData['seller_message'] = '';
                        $orderData['merchant'] = $messages;
                        $result = $detail->insideAdd($orderData);
                        $sums = $sums + 1;  
                        $messages = '';  
                        $numbers = 0;   
                    }else{
                        $messages = $arr200[$i]['sku_name'];
                        $numbers = $numbers + 1;
                        $orderData['order_id'] = $v['order_id'].'-'.getLetter($sums);
                        $orderData['seller_message'] = '';
                        $orderData['merchant'] = $messages;
                        $result = $detail->insideAdd($orderData);
                    }
                }
            }  
            \Think\Log::write('商品200----'.json_encode($sum),'zk0624-E200');                
        }
        if($numbers > 0 && $numbers < 2){
            $orderData['order_id'] = $v['order_id'].'-'.getLetter($sums);
            $orderData['seller_message'] = '';
            $orderData['merchant'] = $messages;
            $result = $detail->insideAdd($orderData);
        }
    }        
}
echo date('Y-m-d H:i:s').'拆单完成';

//字母数组
function getLetter($nu){
    $arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','aa','ab','ac','ad','ae','af','ag','ah','ai','aj','ak','al','am','an','ao','ap','aq','ar','as','at','au','av','aw','ax','ay','az','ba','bb','bc','bd','be','bf','bg','bh','bi','bj','bk','bl','bm','bn','bo','bp','bq','br','bs','bt','bu','bv','bw','bx','by','bz');
    return $arr[$nu];
}
//根据键值查找键名
function getNum($letter){
    $arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','aa','ab','ac','ad','ae','af','ag','ah','ai','aj','ak','al','am','an','ao','ap','aq','ar','as','at','au','av','aw','ax','ay','az','ba','bb','bc','bd','be','bf','bg','bh','bi','bj','bk','bl','bm','bn','bo','bp','bq','br','bs','bt','bu','bv','bw','bx','by','bz');
    return array_search($letter, $arr);
}   