<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

class View
{
    private $context;

    public function __construct($data = [])
    {
        $this->context['data.volume.data'] = $data;//给页面返回数据的
        $this->context['data.volume.set'] = [
            'page' => null,
            'controller' => null,
            'layout' => null,
            'api' => false,
            'head' => true,
        ];
    }

    /**
     * @return $this
     * @author LCF
     * @date 2020/1/16 11:09
     * 关闭layout  不是使用
     */
    public function closeLayout()
    {
        $this->context['data.volume.set']['head'] = false;
        return $this;
    }

    /**
     * @param $page
     * @param null $controller
     * @return $this
     * @author LCF
     * @date
     *设置指定页面或者指定的控制器
     */
    public function setView($page, $controller = null)
    {
        $this->context['data.volume.set']['page'] = $page;
        $this->context['data.volume.set']['controller'] = $controller;
        return $this;
    }

    /**
     * @param $layout
     * @return $this
     * @author LCF
     * @date 2020/1/16 11:22
     * 设置置顶的共同头文件
     */
    public function setLayout($layout)
    {
        $this->context['data.volume.set']['layout'] = $layout;
        return $this;
    }

    /**
     * @return mixed
     * @author LCF
     * @date 2020/1/10 11:54
     * 此方法不能在控制器中使用
     */
    public function get()
    {
        return $this->context;
    }

    public function console($content)
    {
        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        echo "<script>console.group('debug.info');console.info({$content});console.groupEnd();</script>";
        return $this;
    }

    /**
     * @return $this
     * @author LCF
     * @date 2020/1/16 11:23
     * 设置为api
     */
    public function api()
    {
        $this->context['data.volume.set']['api'] = true;
        return $this;
    }
}