<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/9/12
 * Time: 13:53
 */

/**
 * @param $reportData array 报表信息
 * @return array
 * 获取文件信息
 */
function get_file_data($reportData)
{
    return array(
        'repCode' => $reportData['repCode'],
        'name' => $reportData['name'],
        'warCode' => $reportData['warCode'],
        'warName' => $reportData['warName']
    );
}

/**
 * @param $file array 报表信息(包含warCode,repCode)
 * @param $status string 报表状态
 * @return array
 * 报表数据为空,修改数据
 */
function report_upt_data_null($file, $status)
{
    return array(
        "warCode" => $file['warCode'],
        "repCode" => $file['repCode'],
        "status" => $status
    );
}


/**
 * @param $file array 报表信息(包含warCode,repCode)
 * @param $fileName string 报表文件名称
 * @param $status string 报表状态
 * @return array
 * 报表数据不为空,修改数据
 */
function report_upt_data($file, $fileName, $status)
{
    return array(
        "warCode" => $file['warCode'],
        "repCode" => $file['repCode'],
        "fName" => $fileName,
        "status" => $status
    );
}