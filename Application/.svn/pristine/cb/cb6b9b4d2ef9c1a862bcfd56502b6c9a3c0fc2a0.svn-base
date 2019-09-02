<?php
//获取唯一ID
function venus_unique_code($prefix)
{
    $time = (date('y') - 15) . date("mdHis", time());
    $stamp = rand(100, 999);
    $code = $prefix . $time . $stamp;
    $key = "__UNIQUE_CODE__" . $prefix . substr($time, 10);
    $codelist = S($key);
    if ($codelist === false) {
        $codelist = array($code);
        S($key, $codelist, 6);
        return $code;
    } else if (!in_array($code, $codelist)) {
        array_push($codelist, $code);
        S($key, $codelist, 6);
        return $code;
    } else {
        return venus_unique_code($prefix);
    }
}

//异常
function venus_throw_exception($error, $description = "")
{
    E(C("EXCEPTION")[$error] . (!empty($description) ? ":" : "") . $description, $error);
}

//解析api提交参数
function venus_decode_api_request($api)
{
    if (isset($api)) {
        $pattern = '/venus\.(wms|oms)\.(\w*)\.([0-9,a-z.]*)/i';
        preg_match($pattern, $api, $arr);
        if (count($arr) == 4) {
            $module = ucwords($arr[1]);
            $class = ucwords($arr[2]);
            $method = preg_replace('/\./', '_', $arr[3]);
            return array($module, $class, $method);
        }
    }
    venus_throw_exception(1, "ERROR");
    return false;
}

//返回json结果
function venus_encode_api_result($api, $error, $msg, $success, $data, $message, $sess = "")
{
    echo json_encode(array(
        "service" => $api,
        "error" => $error,
        "data" => $data,
        "msg" => $msg,
        "sess" => $sess,
        "success" => $success,
        "message" => $message,
    ));
}

function venus_encode_rpc_result($api, $error, $msg, $success, $data, $message, $sess = "")
{
    return array(
        "service" => $api,
        "error" => $error,
        "data" => $data,
        "msg" => $msg,
        "sess" => $sess,
        "success" => $success,
        "message" => $message,
    );
}

//返回json结果
function venus_encode_napi_result($api, $error, $msg, $success, $data, $message, $sess = "")
{
    $data = empty($data) ? array() : $data;
    $result = array(
        "service" => $api,
        "error" => $error,
        "data" => $data,
        "msg" => $msg,
        "sess" => $sess,
        "success" => $success,
        "message" => $message,
    );
    echo empty($data) ? json_encode($result, JSON_FORCE_OBJECT) : json_encode($result);
}

//当前系统时间
function venus_current_datetime()
{
//    return "2018-10-23".date(" H:i:s", time());
    return date("Y-m-d H:i:s", time());
}

//启动事物
function venus_db_starttrans()
{
    M()->startTrans();
}

//提交事物
function venus_db_commit()
{
    M()->commit();
}

//回滚事物
function venus_db_rollback()
{
    M()->rollback();
}

//加密用户名
function venus_auth_password($pwd)
{
    return $pwd;
    return md5(C("AUTH_SECRET_KEY") . $pwd);
}

//生成当前用户新的token
function venus_gen_token($code)
{
    return md5(C("AUTH_SECRET_KEY") . $code . venus_current_datetime() . rand(10000, 99999));
}

/**
 * @param $code
 * @return mixed
 * 获取用户微信openid
 */
function venus_request_weixin_openid($code)
{
    $appId = C("WEIXIN_AUTH.AppID");
    $appSecret = C("WEIXIN_AUTH.AppSecret");
    $data = file_get_contents("https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code");
    $data = json_decode($data, 1);
    //$data["openid"] = md5("1");
    return $data["openid"];
}

//获取订单状态的说明
function venus_order_status_desc($status)
{
    return C("ORDER_STATUS")[$status];
}

//获取订单货品状态说明
function venus_ordergoods_status_desc($status)
{
    return C("ORDERGOODS_STATUS")[$status];
}

//获取退货类型说明
function venus_return_type_name($type)
{
    return C("ORDERGOODSRETURN_TYPE")[$type];
}

function venus_return_status_name($status)
{
    return C("ORDERGOODSRETURN_STATUS")[$status];
}

//获取订单状态的说明
function venus_rt_status_name($status)
{
    return C("RT_STATUS")[$status];
}

//获取SPU的状态说明
function venus_spu_status_desc($status)
{
    return C("SPU_STATUS")[$status];
}

//获取SPU的仓储方式说明
function venus_spu_storage_desc($type)
{
    return C("SPU_STORAGE")[$type];
}

//获取SPU的一级分类名称
function venus_spu_type_name($typecode)
{
    return C("SPU_TYPE_DICT")[$typecode];
}

//获取SPU的二级分类名称
function venus_spu_catalog_name($catelogcode)
{
    return C("SPU_SUBTYPE_DICT")[$catelogcode];
}

//入仓单状态说明
function venus_receipt_status_desc($status)
{
    return C("RECEIPT_STATUS")[$status];
}

//入仓单类型说明
function venus_receipt_type_desc($type)
{
    return C("RECEIPT_TYPE")[$type];
}

//货品批次说明
function venus_goodsbatch_status_desc($status)
{
    return C("GOODSBATCH_STATUS")[$status];
}

//出仓单状态说明
function venus_invoice_status_desc($status)
{
    return C("INVOICE_STATUS")[$status];
}

//出仓单状态说明
function venus_invoice_type_desc($status)
{
    return C("INVOICE_TYPE")[$status];
}

//工单状态说明
function venus_task_status_desc($status)
{
    return C("TASK_STATUS")[$status];
}

//工单类型说明
function venus_task_type_desc($status)
{
    return C("TASK_TYPE")[$status];
}

//报表状态说明
function venus_report_status_desc($status)
{
    return C("REPORT_STATUS")[$status];
}

//报表类型说明
function venus_report_type_desc($status)
{
    return C("REPORT_TYPE")[$status];
}

/**
 * @param $title 标题
 * @return mixed
 * 脚本开始
 */
function venus_script_begin($title)
{
    $time = microtime(true);
    $datetime = venus_current_datetime();
    echo PHP_EOL . "#################  {$title}  #################  " . PHP_EOL;
    echo "# Start {$datetime} " . PHP_EOL;
    return $time;
}

/**
 * @param $time 时间
 * 脚本结束
 */
function venus_script_finish($time)
{
    $dtime = microtime(true) - $time;
    echo "# Done [ $dtime ]" . PHP_EOL . PHP_EOL;

}

/**
 * @param $num 金额
 * @return bool|string
 * 将金额转换为大写
 */
function venus_money_format($num)
{
    if (!is_numeric($num)) {
        return false;
    }
    $rvalue = '';
    $num = explode('.', $num);//把整数和小数分开
    $rl = !isset($num['1']) ? '' : $num['1'];//小数部分的值
    $j = strlen($num[0]) % 3;//整数有多少位
    $sl = substr($num[0], 0, $j);//前面不满三位的数取出来
    $sr = substr($num[0], $j);//后面的满三位的数取出来
    $i = 0;
    while ($i <= strlen($sr)) {
        $rvalue = $rvalue . ',' . substr($sr, $i, 3);//三位三位取出再合并，按逗号隔开
        $i = $i + 3;
    }
    $rvalue = $sl . $rvalue;
    $rvalue = substr($rvalue, 0, strlen($rvalue) - 1);//去掉最后一个逗号
    $rvalue = explode(',', $rvalue);//分解成数组
    if ($rvalue[0] == 0 && $num[0] != 0) {
        array_shift($rvalue);//如果第一个元素为0，删除第一个元素
    }
    $rv = $rvalue[0];//前面不满三位的数
    for ($i = 1; $i < count($rvalue); $i++) {
        $rv = $rv . ',' . $rvalue[$i];
    }
    if (!empty($rl)) {
        $rvalue = $rv . '.' . $rl;//小数不为空，整数和小数合并
    } else {
        $rvalue = $rv;//小数为空，只有整数
    }

    return "￥" . $rvalue;
}

/**
 *数字金额转换成中文大写金额的函数
 *String Int $num 要转换的小写数字或小写字符串
 *return 大写字母
 *小数位为两位
 **/
function venus_money_amount_in_words($num)
{
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "金额太大，请检查";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $arr = explode('.', $num);
        $num = $arr[0];
        //结束循环
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    } else {
        if (intval($num)) {
            return $c . "整";
        } else {
            return $c;
        }

    }
}

/**
 * @param $year年份
 * @param $month月份
 * @return string
 * 根据年份和月份查询天数
 */
function get_days_by_year_and_month($year, $month)
{
    //首先判断闰年
    if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 !== 0)) {
        $rday = 29;
    } else {
        $rday = 28;
    }

    if ($month == 2) {
        $days = $rday;
    } else {
        //判断是大月（31），还是小月（30）
        $days = (($month - 1) % 7 % 2) ? 30 : 31;
    }
    return $days;

}

/**
 * @param $sprice 销售价
 * @param $count 数量
 * @param $percent 利润率
 * @return float
 * 计算总价
 */
function venus_calculate_sku_price_by_spu($sprice, $count, $proprice)
{
//    $profit = bcmul($sprice, $percent, 6);
    $totalprofit = bcmul($proprice, $count, 4);//每一个货品的总利润
    $totalsprice = bcmul($sprice, $count, 4);//每一个货品的总销售价
    $totalprice = bcadd($totalsprice, $totalprofit, 4);//订单总价
    return $totalprice;
}

/**
 * @param $title邮件标题信息
 * @param $content邮件内容
 * @param array $address邮箱地址
 * @param array $attachment附件地址数组键名保存的名字
 * @return bool
 */
function sendMailer($title, $content, $address = array(), $attachment = array())
{
    if (empty($address)) {
        $address = array(
            "hui.wang@shijijiaming.com",
            "lingna.li@shijijiaming.com",
            "guofang.liu@shijijiaming.com"
        );
    }
    Vendor("PHPMailer.src.Exception");
    Vendor("PHPMailer.src.POP3");
    Vendor("PHPMailer.src.SMTP");
    Vendor("PHPMailer.src.OAuth");
    Vendor("PHPMailer.src.PHPMailer");
    try {
        $mail = new \PHPMailer(true);
        if (C('MAIL_SMTP')) {
            $mail->IsSMTP();  //启动SMTP
        }

        $mail->Host = C('MAIL_HOST'); //SMTP服务器地址
        $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用SMTP认证
        $mail->Port = 465;    //网易为25
        $mail->Username = C('MAIL_USERNAME');//邮箱名称
        $mail->Password = C('MAIL_PASSWORD');//邮箱密码
        $mail->SMTPSecure = C('MAIL_SECURE');//发件人地址
        $mail->CharSet = C('MAIL_CHARSET');//邮件头部信息
        $mail->From = C('MAIL_USERNAME');//发件人是谁
        foreach ($address as $val) {
            $mail->AddAddress($val);
        }
        if (is_array($attachment)) { // 添加附件
            foreach ($attachment as $saveName => $file) {//要保存的名字->文件路径
                is_file($file) && $mail->AddAttachment($file, $saveName);
            }

        }

        $mail->FromName = C("MAIL_FROMNAME");
        $mail->isHTML(C('MAIL_ISHTML'));//是否是HTML字样
        $mail->Subject = $title;// 邮件标题信息
        $mail->Body = $content;//邮件内容
        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; //当邮件不支持html时备用显示
        $mail->WordWrap = 80; // 设置每行字符串的长度
        // 发送邮件
        if (!$mail->Send()) {
            return FALSE;
        } else {
            return TRUE;
        }
    }catch(\Exception $e) {
        $e->getMessage();
    }
}

/**
 * @param $repType
 * @return string
 * 根据报表类型得到所在位置目录
 */
function report_type_to_dir($repType)
{
    if ($repType == 2 || $repType == 14) {
        return "010";//采购单，入仓单
    } elseif ($repType == 4 || $repType == 16) {
        return "020";//申购单，出仓单
    } elseif ($repType == 6) {
        return "011";//入库汇总
    } elseif ($repType == 8) {
        return "021";//出库汇总
    } elseif ($repType == 10) {
        return "030";//库存汇总
    } else {
        return "040";//台账登记表
    }
}

/**
 * @param $totalCount int 总条数
 * @param $pageCurrent int 当前页数
 * @param int $pageSize 每页条数
 * @return array  page limit第一个参数，pSize真实每页条数(limit第二个参数),pageSize每页条数
 * 获取limit的两个参数和每页条数
 */
function pageLimit($totalCount, $pageCurrent, $pageSize = 100)
{
    $pCount = ($pageCurrent + 1) * $pageSize;
    if ($pCount <= $totalCount) {
        $pSize = $pageSize;
        $page = $pageCurrent * $pSize;
    } else {
        $pSize = $totalCount - $pageCurrent * $pageSize;
        $page = $pageCurrent * $pageSize;
    }
    return array("page" => $page, "pSize" => $pSize, "pageSize" => $pageSize);
}

//获取星期方法
function get_week($date)
{
    $date_str = date('Y-m-d', strtotime($date));
    $arr = explode("-", $date_str);
    $year = $arr[0];//参数赋值 年
    $month = sprintf('%02d', $arr[1]);//月，输出2位整型，不够2位右对齐
    $day = sprintf('%02d', $arr[2]);//日，输出2位整型，不够2位右对齐
    $hour = $minute = $second = 0;//时分秒默认赋值为0；
    $strap = mktime($hour, $minute, $second, $month, $day, $year);
    $number_wk = date("w", $strap);
    $weekArr = array("星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
    return $weekArr[$number_wk];
}