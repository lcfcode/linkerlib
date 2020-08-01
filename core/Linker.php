<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

class Linker
{
    /**
     * @param $config
     * @return array||null
     * @author LCF
     * @date 2019/8/17 18:15
     * 执行模块文件函数反回数据方法
     */
    public function action($config)
    {
        $module = $config['request.route']['module'];
        $controller = $config['request.route']['controller'];
        $action = $config['request.route']['action'];
        $className = $config['app.path'] . '\\' . $module . '\\controller\\' . $controller . 'Controller';
        $functionName = $action . 'Action';
        $moduleObj = new $className();
        $whole['before'] = $moduleObj->beforeRequest();
        $context = $moduleObj->$functionName();
        $whole['after'] = $moduleObj->afterRequest();
        if (!($context instanceof View)) {
            print_r($context);
            return null;
        }
        $data = $context->get();
        $whole['data'] = $data['data.volume.data'];
        $set = $data['data.volume.set'];
        $that['default.path'] = $config['root.path'] . DIRECTORY_SEPARATOR . $config['app.path'] . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        $that['default.controller'] = $controller;
        $that['default.page'] = $action;
        $all=['data' => $whole, 'views' => $that, 'set' => $set];








        return ['data' => $whole, 'views' => $that, 'set' => $set];
    }


    /**
     * @param $whole
     * @param $debugInfo
     * @throws \RuntimeException
     * @author LCF
     * @date 2019/8/17 18:16
     * 页面数据的加工
     */
    public function page($whole)
    {
        $transfer = $whole['data'];
        $set = $whole['set'];
        $views = $whole['views'];
        unset($whole);
        if ($set['api'] === true) {
            (new Page($transfer))->api();
            return;
        }
        $layoutFile = $views['default.path'] . 'Layout' . DIRECTORY_SEPARATOR . 'layout.phtml';
        if ($set['layout'] !== null) {
            $layoutFile = $views['default.path'] . 'Layout' . DIRECTORY_SEPARATOR . $set['layout'] . '.phtml';
        }
        //请求大小写规则跟url规则方法名一样
        $path = $views['default.path'] . $views['default.controller'];
        if ($set['controller'] !== null) {
            $path = $views['default.path'] . $set['controller'];
        }
        if ($set['page'] !== null) {
            $page = $set['page'];
        } else {
            $page = $views['default.page'];
        }
        $content = $path . DIRECTORY_SEPARATOR . $page . '.phtml';
        if (!is_file($content)) {
            throw new \RuntimeException($content . ':view is not found', 500);
        }
        if ($set['head'] === true) {
            if (!is_file($layoutFile)) {
                throw new \RuntimeException($layoutFile . ':view is not found', 500);
            }
        } else {
            $layoutFile = '';
        }
        $pageObj = new Page($transfer, $content, $layoutFile);
        $pageObj->views($set['head']);
    }

}