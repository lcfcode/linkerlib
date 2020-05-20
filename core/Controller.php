<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

use swap\utils\ApiTool;

abstract class Controller
{
    use Utiltrait;

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2019/8/17 18:25
     * 获取 $_GET 参数
     */
    public function get($key = '', $default = '')
    {
        if (empty($key)) {
            return $_GET;
        }
        if (isset($_GET[$key])) {
            return trim($_GET[$key]);
        }
        return $default;
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2019/8/17 18:25
     * 获取 $_POST 参数
     */
    public function post($key = '', $default = '')
    {
        if (empty($key)) {
            return $_POST;
        }
        if (isset($_POST[$key])) {
            return trim($_POST[$key]);
        }
        return $default;
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2020/4/30 17:11
     * 获取 $_REQUEST 参数
     */
    public function param($key = '', $default = '')
    {
        if (empty($key)) {
            return $_REQUEST;
        }
        if (isset($_REQUEST[$key])) {
            return trim($_REQUEST[$key]);
        }
        return $default;
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2020/4/30 17:11
     * 获取 $_FILES 参数
     */
    public function file($key = '', $default = '')
    {
        if (empty($key)) {
            return $_FILES;
        }
        if (isset($_FILES[$key])) {
            return trim($_FILES[$key]);
        }
        return $default;
    }

    /**
     * @return bool
     * @author LCF
     * @date 2019/8/17 18:25
     * 判断请求方式是否是post
     */
    public function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return true;
        }
        return false;
    }

    /**
     * @return null
     * @author LCF
     * @date 2019/8/17 18:27
     * 请求前执行的方法，需要重写
     */
    public function beforeRequest()
    {
        return null;
    }

    /**
     * @return null
     * @author LCF
     * @date 2019/8/17 18:27
     * 请求后执行的方法，需要重写
     */
    public function afterRequest()
    {
        return null;
    }

    /**
     * @param $code
     * @param string $msg
     * @param array $data
     * @return false|string
     * @author LCF
     * @date 2020/1/10 11:39
     */
    public function msg($code, $msg = '', $data = [])
    {
        return ApiTool::msg($code, $msg, $data);
    }
}