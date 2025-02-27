<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

class App
{
    private $config;
    private $objects = [];

    /**
     * Linker constructor.
     * @param string $env 全局配置文件
     */
    public function __construct($env = 'dev')
    {
        $config = $this->globalConfig($env . '.php');
        $config['root.path'] = \RegTree::root();
        $config['app.path'] = 'app';
        $config['run.debug'] = $env == 'dev' ? true : false;
        $this->handleException($config);
        $this->config = $this->route($config);
    }

    /**
     * @param $config
     * @author LCF
     * @date 2019/8/17 18:07
     * 异常处理操作
     */
    private function handleException($config)
    {
        $error = new Error();
        $error->init($config['logs']);
        $error->render($config['run.debug']);
    }

    /**
     * @return mixed
     * @author LCF
     * @date 2019/8/17 18:13
     * 获取全局配置文件
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * @param $config
     * @return array
     * @date 2019/8/17 18:13
     * 路由处理
     */
    private function route($config)
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        $index = strpos($requestUrl, '?');
        $uri = $index > 0 ? substr($requestUrl, 0, $index) : $requestUrl;
        $route = $config['default_route'];
        $routeArr = $uri ? explode('/', trim($uri, '/')) : [];
        $module = isset($routeArr[0]) && !empty($routeArr[0]) ? $routeArr[0] : $route['module'];
        $controller = isset($routeArr[1]) ? $routeArr[1] : $route['controller'];
        $action = isset($routeArr[2]) ? $routeArr[2] : $route['action'];

        $controller = ucfirst($controller);//请求重第一个字母为小写将它转为大写，类文件默认大写开头
        $config['request.url'] = '/' . $module . '/' . $controller . '/' . $action;
        $config['request.module'] = $module;
        $config['request.log.file'] = $module . '-' . $controller . '-' . $action;
        $config['request.route'] = ['module' => $module, 'controller' => $controller, 'action' => $action];
        return $config;
    }

    /**
     * @author LCF
     * @date 2019/8/17 18:14
     * 开始执行代码
     */
    public function run()
    {
        $html = $this->action($this->config());
        if (is_string($html)) {
            echo $html;
        } else {
            header('Content-Type:application/json;charset=UTF-8');
            echo json_encode($html, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @param $config
     * @param string $read
     * @return mixed
     * @author LCF
     * @date 2019/10/26 16:37
     * 返回db类
     */
    public function dbInstance($config, $read = 'read_write')
    {
        $ojbKey = $config['host'] . ':' . $config['user'] . ':' . $config['database'] . ':' . $read;
        if (isset($this->objects[$ojbKey])) {
            return $this->objects[$ojbKey];
        }
        switch ($config['drive']) {
            case 'mongo':
                $class = 'swap\\utils\\MongoClass';
                break;
            case 'mssql':
                $class = 'swap\\utils\\MssqlClass';
                break;
            default:
                $class = 'swap\\utils\\MysqliClass';
                break;
        }
        $this->objects[$ojbKey] = new $class($config);
        return $this->objects[$ojbKey];
    }

    /**
     * @param $name
     * @return mixed
     * @author LCF
     * @date 2019/8/17 18:22
     * 返回工具类的实例
     */
    public function getUtils($name = 'AllUtil')
    {
        $class = 'swap\\utils\\' . ucwords($name);
        if (isset($this->objects[$class])) {
            return $this->objects[$class];
        }
        $this->objects[$class] = new $class($this);
        return $this->objects[$class];
    }

    /**
     * @return array
     * @user LCF
     * @date 2019/5/23 21:36
     * 获取模块配置文件
     */
    public function getModuleConfig()
    {
        $config = $this->config();
        $module = $config['request.module'];
        $app = $config['app.path'];
        if (!isset($config['module_file'])) {
            return [];
        }
        $moduleConfig = $config['module_file'];
        $key = 'run.config.' . $module . $app . $moduleConfig;
        if (isset($this->objects[$key])) {
            return $this->objects[$key];
        }
        $moduleFile = $config['root.path'] . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $moduleConfig;
        if (is_file($moduleFile)) {
            $this->objects[$key] = require $moduleFile;
        } else {
            $this->objects[$key] = [];
        }
        return $this->objects[$key];
    }

    /**
     * @param $configFile
     * @return mixed
     * @author LCF
     * @date 2019/8/17 20:06
     * 读取全局配置文件
     */
    private function globalConfig($configFile)
    {
        $key = 'run.config.' . $configFile;
        if (isset($this->objects[$key])) {
            return $this->objects[$key];
        }
        try {
            $path = \RegTree::root() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
            if (is_file($path . $configFile)) {
                $this->objects[$key] = require $path . $configFile;;
                return $this->objects[$key];
            }
            exit('no file global.config [' . $configFile . ']');
        } catch (\Exception $e) {
            exit('no file global.config [' . $configFile . '] ,exception!');
        }
    }

    /**
     * @param $config
     * @return array|string|null
     * @author LCF
     * @date 2019/8/17 18:15
     * 执行模块文件函数反回数据方法
     */
    private function action($config)
    {
        $module = $config['request.route']['module'];
        $controller = $config['request.route']['controller'];
        $action = $config['request.route']['action'];
        $className = $config['app.path'] . '\\' . $module . '\\controller\\' . $controller . 'Controller';
        $functionName = $action . 'Action';
        $moduleObj = new $className($this);
        $before = $moduleObj->beforeRequest();
        if ($before instanceof ResponseHandler) {
            return $before->get();
        }

        $context = $moduleObj->$functionName();
        if (!($context instanceof View)) {
            return $context;
        }
        $transfer['before'] = $before;
        $transfer['after'] = $moduleObj->afterRequest();
        $data = $context->get();
        $transfer['data'] = $data['data.volume.data'];
        $set = $data['data.volume.set'];
        $views['default.path'] = $config['root.path'] . DIRECTORY_SEPARATOR . $config['app.path'] . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        $views['default.controller'] = $controller;
        $views['default.page'] = $action;

        if ($set['api'] === true) {
            return $transfer['data'];
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
        $pageObj = new Page($this, $transfer, $content, $layoutFile);
        return $pageObj->views($set['head']);
    }

    public function configAll($configKey = null, $default = null)
    {
        $config = $this->getModuleConfig();
        if (isset($config[$configKey])) {
            return $config[$configKey];
        }
        $configs = $this->config();
        if (isset($configs[$configKey])) {
            return $configs[$configKey];
        }
        if (true === $configKey) {
            return [
                'global.config' => $configs,
                'module.config' => $config,
            ];
        }
        if (empty($configKey)) {
            return array_merge($configs, $config);
        }
        return $default;
    }

    public function instance($class, ...$args)
    {
        if (isset($this->objects[$class])) {
            return $this->objects[$class];
        }
        $this->objects[$class] = new $class(...$args);
        return $this->objects[$class];
    }
}
