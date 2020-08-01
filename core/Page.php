<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

class Page
{
    private $data;
    private $all;
    private $_content;
    private $_layout;

    public function __construct($transfer, $content = '', $layout = '')
    {
        $this->data = $transfer['data'];
        $this->all = $transfer;
        $this->_content = $content;
        $this->_layout = $layout;
    }

    /**
     * @param $_content
     * @param string $layout
     * @param bool $flag
     * @author LCF
     * @date
     * 处理页面的
     */
    public function views($flag = true)
    {
        $data = $this->data;
        $all = $this->all;
        $flag === true ? require $this->_layout : require $this->_content;
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

    public function api()
    {
        header('Content-Type:application/json;charset=UTF-8');
        echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}