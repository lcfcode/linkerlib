<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\linker;

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
        \RegTree::set('contr.application', $moduleObj);
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
        return ['data' => $whole, 'views' => $that, 'set' => $set];
    }


    /**
     * @param $whole
     * @param $debugInfo
     * @throws \Exception
     * @author LCF
     * @date 2019/8/17 18:16
     * 页面数据的加工
     */
    public function page($whole, $debugInfo)
    {
        $transfer = $whole['data'];
        $set = $whole['set'];
        $views = $whole['views'];
        unset($whole);
        if ($set['api'] === true) {
            (new Page($transfer))->api();
            return;
        }
        $layoutFile = $views['default.path'] . 'layout' . DIRECTORY_SEPARATOR . 'layout.phtml';
        if ($set['layout'] !== null) {
            $layoutFile = $views['default.path'] . 'layout' . DIRECTORY_SEPARATOR . $set['layout'] . '.phtml';
        }
        //请求大小写规则跟url规则方法名一样，处理这里控制器对应页面的文件夹第一个字母变小写以外
        $path = $views['default.path'] . lcfirst($views['default.controller']);
        if ($set['controller'] !== null) {
            $path = $views['default.path'] . lcfirst($set['controller']);
        }
        if ($set['page'] !== null) {
            $page = $set['page'];
        } else {
            $page = $views['default.page'];
        }
        $content = $path . DIRECTORY_SEPARATOR . $page . '.phtml';
        if (!is_file($content)) {
            throw new \Exception($content . ':view is not found', 500);
        }
        $v = $debugInfo['debug'] === true ? date('YmdHis') : date('YmdHis', filemtime($content));
        $pageObj = new Page($transfer, $v);
        if ($set['head'] === true) {
            if (!is_file($layoutFile)) {
                throw new \Exception($layoutFile . ':view is not found', 500);
            }
            $pageObj->views($content, $layoutFile);
        } else {
            $pageObj->views($content, $layoutFile, $set['head']);
        }
        $this->debugs($debugInfo);
    }

    /**
     * @param $debugInfo
     * @user LCF
     * @date 2019/6/2 16:10
     * 浏览器打印运行情况
     */
    private function debugs($debugInfo)
    {
        if ($debugInfo['debug'] === true) {
            $startTime = $debugInfo['start_time'];
            $startMemory = $debugInfo['start_memory'];
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            $runTime = number_format(($endTime - $startTime), 8) . ' 秒';
            $usedMemory = number_format((($endMemory - $startMemory) / 1024), 6) . ' KB';
            $fileLoadNum = (string)count(get_included_files()) . ' 个';
            echo "<script>console.group('run.info');console.info('运行时间:','{$runTime}');console.info('内存消耗:','{$usedMemory}');console.info('文件数量:','{$fileLoadNum}');console.groupEnd('debug.end');</script>";
        }
    }
}