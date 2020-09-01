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
    private $app;

    public function __construct(App $app, $transfer, $content = '', $layout = '')
    {
        $this->app = $app;
        $this->data = $transfer['data'];
        $this->all = $transfer;
        $this->_content = $content;
        $this->_layout = $layout;
    }

    /**
     * @param bool $flag
     * @return false|string
     * @author LCF
     * @date
     */
    public function views($flag = true)
    {
        $data = $this->data;
        $all = $this->all;
        ob_start();
        ob_implicit_flush(0);
        $flag === true ? require $this->_layout : require $this->_content;
        return ob_get_clean();
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
     * @param null $action
     * @param null $controller
     * @return string
     * @author LCF
     * 获取url
     */
    public function url($action = null, $controller = null)
    {
        $config = $this->app->config()['request.route'];
        $module = $config['module'];
        if (empty($action)) {
            $action = $config['action'];
        }
        if (empty($controller)) {
            $controller = $config['controller'];
        }
        return '/' . $module . '/' . $controller . '/' . $action;
    }
}
