<?php
/**
 * Created by PhpStorm.
 * User: lingna
 * Date: 2019/5/14
 * Time: 17:27
 */

namespace Erp\Service;

use Common\Service\ExcelService;
use Erp\Dao\ExchangeDao;
use Erp\Dao\PrizeDao;
use Erp\Dao\ShopordersDao;

class PrizeService
{
    /**
     * @return array
     * 获取抽奖转盘图片名称
     */
    public function get_prize_image()
    {
        $prizeDao = PrizeDao::getInstance();
        $prizeId = 1;
        $prizeData = $prizeDao->queryById($prizeId);
//        $edition = "https://wms.shijijiaming.cn/static/erpimage/" . $prizeData['prize_edition'];
        $edition = "https://dev.shijijiaming.cn/static/erpimage/" . $prizeData['prize_edition'];
        return array(true, array("img" => $edition), "");
    }

    /**
     * @return array
     * 导入转盘图片
     */
    public function prize_insert_image()
    {
        $data = $_POST['data'];
        $date = date('Ymd');//得到当前时间,如;20070705163148
        $fileName = $_FILES['file']['name'];//得到上传文件的名字
        $name = explode('.', $fileName);//将文件名以'.'分割得到后缀名,得到一个数组
        $imgName = $date . '.' . $name[1];
        $imgSaveName = date('YmdHis') . '.' . $name[1];
        $newPath = C("IMAGE_SAVE_PATH") . "erp/" . $imgSaveName;//得到一个新的文件为'20070705163148.jpg',即新的路径
        $oldPath = $_FILES['file']['tmp_name'];//临时文件夹,即以前的路径
        $reNameRes = rename($oldPath, $newPath);
        if (file_exists(C("IMAGE_WEB_PATH") . "erpimage/" . $imgName)) {
            unlink(C("IMAGE_WEB_PATH") . "erpimage/" . $imgName);
        }
        $copyRes = copy($newPath, C("IMAGE_WEB_PATH") . "erpimage/" . $imgName);
        $prizeDao = PrizeDao::getInstance();
        $updateImgEdition = $prizeDao->updateEdition($imgName);
        if (!$reNameRes) return array(false, "", "rename");
        if (!$copyRes) return array(false, "", "copy");
        if (!$updateImgEdition) return array(false, "", "img");
        return array($reNameRes && $copyRes && $updateImgEdition, array(), $reNameRes && $copyRes && $updateImgEdition ? "导入转盘图片成功" : "导入转盘图片失败");
    }

    /**
     * @return array
     * 展示奖品列表
     */
    public function prize_list()
    {
        $prizeDao = PrizeDao::getInstance();
        $prizeListData = $prizeDao->queryList();
        $data = array();
        foreach ($prizeListData as $key => $prizeListDatum) {
            $data[$key] = array(
                "num" => $prizeListDatum['id'],
                "name" => $prizeListDatum['prize_item'],
                "count" => $prizeListDatum['prize_count'],
                "rate" => floatval(bcmul($prizeListDatum['winning_rate'], 100, 2)),
            );
        }
        return array(true, $data, "获取奖品列表成功");
    }

    /**
     * @return array
     * 修改中奖概率
     */
    public function update_winning_rate()
    {
        $post = $_POST['data'];
        $rateNum = $post['rate'];
        $num = $post['num'];
        if (empty($num)) return array(false, array(), "序号不能为空");
        if ($rateNum == null) return array(false, array(), "中奖概率不能为空");
        $rate = floatval(bcdiv($rateNum, 100, 2));
        $prizeDao = PrizeDao::getInstance();
        $isSuccess = true;
        venus_db_starttrans();
        $isSuccess = $isSuccess && $prizeDao->updateWinningRateById($num, $rate);
        if (!$isSuccess) $message = "修改奖品数量失败";
        $issetPrizeRate = $prizeDao->queryListByCondition(array("issetRateGt" => 0));
        if ($rateNum == 0 && empty($issetPrizeRate)) {
            $isSuccess = $isSuccess && false;
            $message = "至少有一个中奖概率大于0";
        }
        $prizeListData = $prizeDao->queryListDesc();
        $prizeRate = array();
        foreach ($prizeListData as $key => $prizeListDatum) {
            if ($prizeListDatum['winning_rate'] != 0 && $prizeListDatum['prize_count'] != 0) {
                $prizeRate[$prizeListDatum['id']] = floatval(bcmul($prizeListDatum['winning_rate'], 100, 2));
            }
        }
        if (empty($prizeRate)) {
            $isSuccess = $isSuccess && false;
            $message = "奖项设置规则不正确";
        }
        if ($isSuccess) {
            venus_db_commit();
            return array(true, array(), "修改奖品数量成功");
        } else {
            venus_db_rollback();
            return array(false, array(), $message);
        }
    }

    /**
     * @return array
     * 修改奖品数量
     */
    public function update_prize_count()
    {
        $post = $_POST['data'];
        $count = $post['count'];
        $num = $post['num'];
        if (empty($num)) return array(false, array(), "序号不能为空");
        if ($count == null) return array(false, array(), "奖品数量不能为空");
        $prizeDao = PrizeDao::getInstance();
        $clause = array(
            "issetNumGt" => "0",
        );
        $isSuccess = true;
        venus_db_starttrans();
        $isSuccess = $isSuccess && $prizeDao->updateCountById($num, $count);
        if (!$isSuccess) $message = "修改奖品数量失败";
        $issetPrizeNum = $prizeDao->queryListByCondition($clause);
        if ($count == 0 && empty($issetPrizeNum)) {
            $isSuccess = $isSuccess && false;
            $message = "至少有一个奖品数量大于0";
        }
        $prizeListData = $prizeDao->queryListDesc();
        $prizeRate = array();
        foreach ($prizeListData as $key => $prizeListDatum) {
            if ($prizeListDatum['winning_rate'] != 0 && $prizeListDatum['prize_count'] != 0) {
                $prizeRate[$prizeListDatum['id']] = floatval(bcmul($prizeListDatum['winning_rate'], 100, 2));
            }
        }
        if (empty($prizeRate)) {
            $isSuccess = $isSuccess && false;
            $message = "奖项设置规则不正确";
        }
        if ($isSuccess) {
            venus_db_commit();
            return array(true, array(), "修改奖品数量成功");
        } else {
            venus_db_rollback();
            return array(false, array(), $message);
        }
    }

    /**
     * @return array
     *创建兑奖记录
     */
    public function create_exchange_log()
    {
        $post = $_POST['data'];
        $user = $post['user'];//客户名称
        $iphone = $post['iphone'];//客户手机号
        $address = $post['address'];//客户地址
        $name = $post['name'];//奖项名称
        $edition = $post['edition'];//奖项版本

        $shopOrdersDao = ShopordersDao::getInstance();
        $exchangeDao = ExchangeDao::getInstance();

        $clauseShopOrders = array(
            "buyer_mobile" => $iphone
        );
        $shopOrdersData = $shopOrdersDao->queryBySearch($clauseShopOrders, 0, 1000000);
        $number = $this->get_exchange_number($iphone);
        if ($shopOrdersData['total'] > 0 && $number <= 0) {
            return array(false, array(), "已无可兑奖次数");
        } else {
            if ($shopOrdersData['total'] > 0 && $number > 0) {
                $isExchange = true;
            } else {
                $isExchange = false;
            }
            $insertExchangeData = array(
                "iphone" => $iphone,
                "user" => $user,
                "address" => $address,
                "item" => $name,
                "isExchange" => $isExchange ? 1 : 2,
                "edition" => $edition,
            );
            $insertExchangeRes = $exchangeDao->insert($insertExchangeData);
            if (!empty($insertExchangeRes)) {
                if ($isExchange) {
                    return array($isExchange, array(), "领取奖品成功,请耐心等待发货");
                } else {
                    return array(false, array(), "您无购买记录，无法兑奖");
                }
            } else {
                return array(false, array(), "请重新尝试领取奖品");
            }

        }


    }

    /**
     * @return array
     * 搜索展示兑奖记录
     */
    public
    function search_exchange_list()
    {
        $post = $_POST['data'];
        $iphone = $post['iphone'];
        $sctime = $post['sctime'];
        $ectime = $post['ectime'];
        $isExchange = $post['isExchange'];
        $item = $post['item'];
        $pageCurrent = $post['pageCurrent'];//当前页数

        $clause = array();
        if (!empty($iphone)) $clause['iphone'] = $iphone;
        if (!empty($sctime)) $clause['sctime'] = $sctime;
        if (!empty($ectime)) $clause['ectime'] = $ectime;
        if (!empty($item)) $clause['item'] = $item;
        if (!empty($isExchange)) $clause['isExchange'] = $isExchange;
        if (empty($pageCurrent)) $pageCurrent = 0;
        $exchangeDao = ExchangeDao::getInstance();
        $totalCount = $exchangeDao->queryCountByCondition($clause);//符合条件的出仓单个数
        $pageLimit = pageLimit($totalCount, $pageCurrent, 10);//获取分页信息

        $exchangeListData = $exchangeDao->queryListByCondition($clause, $pageLimit['page'], $pageLimit['pSize']);
        $data = array(
            "pageCurrent" => $pageCurrent,//当前页数
            "pageSize" => $pageLimit['pageSize'],//每页条数
            "totalCount" => $totalCount,//总条数
        );
        foreach ($exchangeListData as $key => $exchangeListDatum) {
            $data['list'][$key] = array(
                "seq" => bcmul($pageCurrent, $pageLimit['pageSize']) + $key + 1,
                "num" => $exchangeListDatum['id'],
                "time" => $exchangeListDatum['ex_ctime'],
                "name" => $exchangeListDatum['prize_item'],
                "user" => $exchangeListDatum['ex_user'],
                "iphone" => $exchangeListDatum['ex_iphone'],
                "address" => $exchangeListDatum['ex_address'],
                "edition" => $exchangeListDatum['prize_edition'],
                "isExchange" => $exchangeListDatum['is_exchange'] == 1 ? true : false,
            );
        }

        return array(true, $data, "展示兑奖记录成功");
    }

    /**
     * @return array
     * 导出表格
     */
    public
    function export_exchange_list()
    {
        $clause = array();
        $exchangeDao = ExchangeDao::getInstance();
        $totalCount = $exchangeDao->queryCountByCondition($clause);//符合条件的出仓单个数

        $exchangeListData = $exchangeDao->queryListByCondition($clause, 0, $totalCount);
        $excelData = array();
        $header = array(
            "序号",
            "抽奖时间",
            "奖品",
            "收件人",
            "手机号",
            "收货地址",
            "验真",
            "奖品版本",
        );
        foreach ($exchangeListData as $key => $exchangeListDatum) {
            $excelData['兑奖记录'][] = array(
                $key + 1,
                $exchangeListDatum['ex_ctime'],
                $exchangeListDatum['prize_item'],
                $exchangeListDatum['ex_user'],
                " " . $exchangeListDatum['ex_iphone'] . " ",
                $exchangeListDatum['ex_address'],
                $exchangeListDatum['is_exchange'] == 1 ? "已通过" : "已拒绝",
                $exchangeListDatum['prize_edition'],
            );
        }

        $fileName = ExcelService::getInstance()->exportExcel($excelData, $header, "001");
        if (!empty($fileName)) {
            return array(true, array("sname" => "兑奖记录.xlsx", "tname" => "001", "fname" => $fileName), "导出兑奖记录表格成功");
        } else {
            return array(false, array(), "导出兑奖记录表格失败");
        }
    }


    /**
     * @return array
     * 获取奖品
     */
    public
    function get_prize()
    {
        $prizeDao = PrizeDao::getInstance();
        $prizeListData = $prizeDao->queryListDesc();
        $prizeRate = array();
        foreach ($prizeListData as $key => $prizeListDatum) {
            if ($prizeListDatum['winning_rate'] != 0 && $prizeListDatum['prize_count'] != 0) {
                $prizeRate[$prizeListDatum['id']] = floatval(bcmul($prizeListDatum['winning_rate'], 100, 2));
            }
        }

        arsort($prizeRate);
        $prizeId = $this->get_rand($prizeRate); //根据概率获取奖项id
        $prizeData = $prizeDao->queryById($prizeId);
        $name = $prizeData['prize_item'];
        $edition = $prizeData['prize_edition'];
        $data = array(
            "num" => $prizeId,
            "name" => $name,
            "edition" => $edition,//奖项版本
        );
        $message = "恭喜您获得" . $name;
        return array(true, $data, $message);
    }

    /**
     * @param $iphone
     * @return float
     * 可兑奖次数
     */
    private
    function get_exchange_number($iphone)
    {
        $shopOrdersDao = ShopordersDao::getInstance();
        $clause = array(
            "buyer_mobile" => $iphone
        );
        $shopOrdersData = $shopOrdersDao->queryBySearch($clause, 0, 1000000);
        $clause = array(
            'iphone' => $iphone
        );
        $exchangeDao = ExchangeDao::getInstance();
        $totalExCount = $exchangeDao->queryCountByCondition($clause);//符合条件的出仓单个数
        $number = floatval(bcsub($shopOrdersData['total'], $totalExCount, 2));
        return $number;
    }

    //计算中奖概率
    private
    function get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum); //返回随机整数
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
}