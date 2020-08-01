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
     * @param null $bindApp
     */
    public function __construct($env = 'dev', $bindApp = null)
    {
        $config = [
            'root.path' => \RegTree::root(),
            'app.path' => 'app',
            'run.env' => $env,
            'global.config' => $this->globalConfig($env . '.php'),
        ];
        $config['run.debug'] = $env == 'dev' ? true : false;
        $config['run.logs.path'] = $config['root.path'] . $config['global.config']['logs'];
        $config['request.bind'] = $bindApp;

        $this->handleException($config);
        $requestConf = $this->route($config['global.config']['default_route'], $bindApp);
        $this->config = array_merge($config, $requestConf);
        unset($config, $requestConf);
        $this->run();
    }

    /**
     * @param $config
     * @author LCF
     * @date 2019/8/17 18:07
     * 异常处理操作
     */
    private function handleException($config)
    {
        if (true === $config['run.debug'] && isset($config['global.config']['err_obj']) && class_exists($config['global.config']['err_obj'])) {
            $errClass = $config['global.config']['err_obj'];
            $error = new $errClass();
            if (!($error instanceof Error)) {
                unset($error);
                $error = new Error();
            }
        } else {
            $error = new Error();
        }
        $error->init($config['run.logs.path']);
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
     * @param $route
     * @param $bindApp
     * @return array
     * @date 2019/8/17 18:13
     * 路由处理
     */
    private function route($route, $bindApp)
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        $index = strpos($requestUrl, '?');
        $uri = $index > 0 ? substr($requestUrl, 0, $index) : $requestUrl;
        if (stripos($uri, 'index.php')) {
            $uri = str_replace('index.php', '', $uri);
        }
        $uri = trim($uri, '/');
        if (empty($uri)) {
            $module = $route['module'];
            $controller = $route['controller'];
            $action = $route['action'];
        } else {
            $routeArr = explode('/', trim($uri, '/'));
            if ($bindApp) {
                $module = $bindApp;
                $controller = isset($routeArr[0]) ? $routeArr[0] : $route['controller'];
                $action = isset($routeArr[1]) ? $routeArr[1] : $route['action'];
            } else {
                $module = isset($routeArr[0]) ? $routeArr[0] : $route['module'];
                $controller = isset($routeArr[1]) ? $routeArr[1] : $route['controller'];
                $action = isset($routeArr[2]) ? $routeArr[2] : $route['action'];
            }
        }
        $controller = ucfirst($controller);//请求重第一个字母为小写将它转为大写，类文件默认大写开头
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
        $linker = new Linker();
        //写入配置文件
        \RegTree::set('app.application', $this);
        $data = $linker->action($this->config());
        if ($data) {
            $linker->page($data);
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
    public function getUtils($name)
    {
        $class = 'swap\\utils\\' . ucwords($name);
        if (isset($this->objects[$class])) {
            return $this->objects[$class];
        }
        $this->objects[$class] = new $class();
        return $this->objects[$class];
    }

    /**
     * @return array|bool
     * @user LCF
     * @date 2019/5/23 21:36
     * 获取模块配置文件
     */
    public function getModuleConfig()
    {
        $config = $this->config();
        $module = $config['request.module'];
        $app = $config['app.path'];
        if (!isset($config['global.config']['module_file'])) {
            return [];
        }
        $moduleConfig = $config['global.config']['module_file'];
        $key = 'run.config.' . $module . $app . $moduleConfig;
        if (isset($this->objects[$key])) {
            return $this->objects[$key];
        }
        $moduleFile = $config['root.path'] . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $moduleConfig;
        if (is_file($moduleFile)) {
            $this->objects[$key] = require $moduleFile;
            return $this->objects[$key];
        }
        return trigger_error($module . ' module no file module.config [' . $moduleConfig . '] ', E_USER_ERROR);
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
                $config = $conf = require $path . $configFile;
                if (isset($conf['multi_profile']) && is_file($path . $conf['multi_profile'])) {
                    $profile = require $path . $conf['multi_profile'];
                    $config = array_merge($config, $profile);
                    unset($profile);
                }
                unset($conf);
                $this->objects[$key] = $config;
                return $this->objects[$key];
            }
            exit('no file global.config [' . $configFile . ']');
        } catch (\Exception $e) {
            exit('no file global.config [' . $configFile . '] ,exception!');
        }
    }
}