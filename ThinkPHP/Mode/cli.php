<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP 普通模式定义
 */
return array(
    // 配置文件
    'config' => array(
        THINK_PATH . 'Conf/convention.php', // 系统惯例配置
        MODE_PATH . 'Cli/config.php',
        CONF_PATH . 'config' . CONF_EXT, // 应用公共配置
    ),

    // 别名定义
    'alias'  => array(
    ),

    // 函数和类文件
    'core'   => array(
        THINK_PATH . 'Common/functions.php',
        COMMON_PATH . 'Common/function.php',
    ),

);

