<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\linker;

class App
{
    /**
     * @var array
     * 可以配置的入口配置
     */
    private $setConfig = [
        'err_obj' => null,//异常处理类 已经实例化过的
        'bind_app' => null,//绑定的模块
    ];
    private $config;
    private $objects = [];

    /**
     * Linker constructor.
     * @param string $env 全局配置文件
     * @param array $set 设置的配置，支持 $this->setConfig内的列表
     */
    public function __construct($env = 'dev', $set = [])
    {
        $this->setConfig = array_merge($this->setConfig, $set);
        $config = [
            'root.path' => \RegTree::root(),
            'app.path' => 'app',
            'run.env' => $env,
            'global.config' => $this->globalConfig($env . '.php'),
        ];
        $debug = $config['run.env'] == 'dev' ? true : false;
        $config['global.config']['debug'] = $debug;
        $logsPath = $config['global.config']['logs'];
        $config['run.logs.path'] = $logsPath;
        $this->handleException($debug, $logsPath, $this->setConfig['err_obj']);
        $this->config = $this->route($config);
        $this->run();
    }

    /**
     * @param $debug
     * @param $logPath
     * @param $error
     * @author LCF
     * @date 2019/8/17 18:07
     * 异常处理操作
     */
    private function handleException($debug, $logPath, $error)
    {
        if (!($error instanceof Error)) {
            $error = new Error();
        }
        $error->init($logPath);
        $error->render($debug);
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
     * @return mixed
     * @author LCF
     * @date 2019/8/17 18:13
     * 路由处理
     */
    private function route($config)
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        $index = strpos($requestUrl, '?');
        $uri = $index > 0 ? substr($requestUrl, 0, $index) : $requestUrl;
        if (stripos($uri, 'index.php')) {
            $uri = str_replace('index.php', '', $uri);
        }
        $uri = trim($uri, '/');
        $route = $config['global.config']['default_route'];
        if (empty($uri)) {
            $module = $route['module'];
            $controller = $route['controller'];
            $action = $route['action'];
        } else {
            $routeArr = explode('/', trim($uri, '/'));
            if ($this->setConfig['bind_app']) {
                $module = $this->setConfig['bind_app'];
                $controller = isset($routeArr[0]) ? $routeArr[0] : $route['controller'];
                $action = isset($routeArr[1]) ? $routeArr[1] : $route['action'];
            } else {
                $module = isset($routeArr[0]) ? $routeArr[0] : $route['module'];
                $controller = isset($routeArr[1]) ? $routeArr[1] : $route['controller'];
                $action = isset($routeArr[2]) ? $routeArr[2] : $route['action'];
            }
        }
        $controller = ucfirst($controller);//请求重第一个字母为小写将它转为大写，类文件默认大写开头
        $config['request.bind'] = $this->setConfig['bind_app'];
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
        $debugInfo = ['debug' => $this->config()['global.config']['debug'], 'start_time' => $_SERVER['REQUEST_TIME_FLOAT'], 'start_memory' => memory_get_usage()];
        $linker = new Linker();
        //写入配置文件
        \RegTree::set('app.application', $this);
        $data = $linker->action($this->config());
        if ($data) {
            $linker->page($data, $debugInfo);
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