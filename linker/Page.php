<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\linker;

class Page
{
    public $data;
    public $all;
    public $version;

    public function __construct($transfer, $version = 1)
    {
        $this->data = $transfer['data'];
        $this->all = $transfer;
        $this->version = $version;
    }

    /**
     * @param $_content
     * @param string $layout
     * @param bool $flag
     * @author LCF
     * @date
     * 处理页面的
     */
    public function views($_content, $layout, $flag = true)
    {
        $data = $this->data;
        $all = $this->all;
        $flag === true ? require $layout : require $_content;
    }

    /**
     * @param string $key
     * @param null $default
     * @return null
     * @author LCF
     * @date 2019/8/17 18:18
     * 返回控制器吐出数据给前端方法
     */
    public function data($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->data;
        }
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @param null $default
     * @return null
     * @author LCF
     * @date 2019/8/17 18:18
     * 返回全部后台吐出来的数据
     */
    public function all($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->all;
        }
        return isset($this->all[$key]) ? $this->all[$key] : $default;
    }

    /**
     * @return false|mixed|string
     * @author LCF
     * @date 2019/8/17 18:19
     * 主要用于调试前端js和css版本控制的
     */
    public function v()
    {
        return $this->version;
    }

    /**
     * @author LCF
     * @date 2019/10/13 20:20
     * 主要用于调试前端js和css版本控制的
     */
    public function v2()
    {
        echo $this->version;
    }

    public function api()
    {
        header('Content-Type:application/json;charset=UTF-8');
        echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}