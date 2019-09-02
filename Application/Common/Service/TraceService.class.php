<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/7/18
 * Time: 17:16
 */

namespace Common\Service;


use Wms\Dao\InvoiceDao;
use Wms\Dao\ReceiptDao;
use Wms\Dao\SkuDao;
use Wms\Dao\TaskDao;
use Wms\Dao\TraceDao;

class TraceService
{
    //保存类实例的静态成员变量
    private static $_instance;
    private static $traceModel;

    private function __construct()
    {
        self::$traceModel = new TraceDao();
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function get_trace_code()
    {
        $code = self::$traceModel->insert();
        return $code;
    }

    public function get_trace($traceCode)
    {
        //调用model里面的方法，获取轨迹信息
        return self::$traceModel->queryDataByCode($traceCode);
    }


    /**
     * @param $warCode仓库编号
     * @param $recCode入仓单编号
     * @return mixed
     * 通过入仓单编号获取轨迹信息
     */
    public function query_data_by_reccode($warCode, $recCode)
    {
        $traceCode = $this->get_trace_code_by_reccode($warCode, $recCode);
        $trace = TraceDao::getInstance($warCode)->queryDataByCode($traceCode);
        return json_decode($trace, true);
    }

    /**
     * @param $warCode仓库编号
     * @param $recCode入仓单编号
     * @param $traceMark轨迹信息
     * @return mixed
     * 通过入仓单编号添加轨迹数据
     */
    public function update_trace_data_by_reccode($warCode, $recCode, $traceMark)
    {
        $traceCode = $this->get_trace_code_by_reccode($warCode, $recCode);
        return $this->update_trace_data($warCode, $traceCode, $recCode, $traceMark);
    }

    /**
     * @param $warCode仓库编号
     * @param $recCode入仓单编号
     * @return mixed
     * 通过入仓单编号获取轨迹编号
     */
    public function get_trace_code_by_reccode($warCode, $recCode)
    {
        return ReceiptDao::getInstance($warCode)->queryByCode($recCode)['trace_code'];
    }

    /**
     * @param $warCode
     * @param $recCode
     * @return mixed
     * 通过出仓单编号获取轨迹信息
     */
    public function query_data_by_invcode($warCode, $invCode)
    {
        $traceCode = $this->get_trace_code_by_invcode($warCode, $invCode);
        $trace = TraceDao::getInstance($warCode)->queryDataByCode($traceCode);
        return json_decode($trace, true);
    }

    /**
     * @param $warCode
     * @param $invCode
     * @param $traceMark
     * @return mixed
     * 通过出仓单编号添加轨迹数据
     */
    public function update_trace_data_by_invcode($warCode, $invCode, $traceMark)
    {
        $traceCode = $this->get_trace_code_by_invcode($warCode, $invCode);
        return $this->update_trace_data($warCode, $traceCode, $invCode, $traceMark);
    }

    /**
     * @param $warCode仓库编号
     * @param $invCode出仓单编号
     * @return mixed
     * 通过出仓单编号获取轨迹编号
     */
    public function get_trace_code_by_invcode($warCode, $invCode)
    {
        return InvoiceDao::getInstance($warCode)->queryByCode($invCode)['trace_code'];
    }


    /**
     * @param $warCode仓库编号
     * @param $traceCode轨迹编号
     * @param $ocode轨迹记录的编号
     * @param $traceMark详细信息
     * @return mixed
     * 添加轨迹记录
     */
    public function update_trace_data($warCode, $traceCode, $ocode, $traceMark)
    {
        $data = array(
            "stime" => venus_current_datetime(),
            "code" => $ocode,
            "mark" => $traceMark
        );
        $traceData = TraceDao::getInstance($warCode)->queryDataByCode($traceCode);
        $traceDataArr = json_decode($traceData, true);
        $traceDataArr[] = $data;
        $uptData['trace_data'] = json_encode($traceDataArr);
        return TraceDao::getInstance($warCode)->updateDataByCode($traceCode, $uptData);
    }


    /**
     * @param $warCode仓库编号
     * @param $tCode轨迹编号
     * @param $code记录的编号
     * @param string $isFinish完成的时间
     * @return mixed
     */
    public function update_trace_data_status($warCode, $tCode, $code, $isFinish = '')
    {
        $traceData = TraceDao::getInstance($warCode)->queryDataByCode($tCode);
        $traceDataArr = json_decode($traceData, true);
        $uptDataArr = $traceDataArr;

        foreach ($traceDataArr as $k => $v) {

            if ($v['code'] == $code) {
                if (!empty($isFinish)) {
                    $uptDataArr[$k]['etime'] = venus_current_datetime();
                }
            }
        }

        $uptData['trace_data'] = json_encode($uptDataArr);
        return TraceDao::getInstance($warCode)->updateDataByCode($tCode, $uptData);
    }


    private function __clone()
    {

    }
}