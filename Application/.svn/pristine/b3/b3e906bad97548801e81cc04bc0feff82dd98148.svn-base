<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/7/18
 * Time: 12:01
 */

namespace Common\Service;


class PassportService {
    //保存类实例的静态成员变量
    private static $_instance;

    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private static $expire = 14400;

    /**
     * 构造函数
     * @access public
     */
    public function __construct() {

    }

    public static function login($data, $type = "wms") {
        $data["type"] = $type;
        return S(session_id(), $data, self::$expire) ? true : false;
    }

    public static function logout() {
        return S(session_id(), NULL) ? true : false;
    }

    public static function loginUser() {
        return S(session_id());
    }
}