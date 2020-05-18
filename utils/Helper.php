<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\utils;

/**
 * @property $app \swap\linker\Linker
 */
class Helper
{
    /**
     * @var \swap\linker\App
     */
    private static $app;

    public static function getApp()
    {
        if (!self::$app) {
            self::$app = \RegTree::get('app.application');
            if (!(self::$app instanceof \swap\linker\App)) {
                trigger_error('获取不到 App 对象', E_USER_ERROR);
            }
        }
        return self::$app;
    }

    /**
     * @param null $configKey
     * @param null $default
     * @return array|mixed|null
     * @author LCF
     * @date 2020/4/27 11:31
     * 返回配置文件方法
     */
    public static function config($configKey = null, $default = null)
    {
        $config = self::getApp()->getModuleConfig();
        if (isset($config[$configKey])) {
            return $config[$configKey];
        }
        $globalConfig = self::getApp()->config();
        $configs = $globalConfig['global.config'];
        if (isset($configs[$configKey])) {
            return $configs[$configKey];
        }
        if (true === $configKey) {
            $globalConfig['module.config'] = $config;
            return $globalConfig;
        }
        if (empty($configKey)) {
            return array_merge($configs, $config);
        }
        return $default;
    }

    /**
     * @param string $name
     * @return \swap\utils\Utils
     * @author LCF
     * @date 2020/4/30 17:07
     * 获取 utils 类的方法
     */
    public static function utils($name = 'Utils')
    {
        return self::getApp()->getUtils($name);
    }

    /**
     * @return \swap\core\Controller
     * @author LCF
     * @date 2020/4/30 17:25
     */
    public static function that()
    {
        return \RegTree::get('contr.application');
    }

    public static function getUrl()
    {
        $config = self::config(true);
        return '/' . $config['request.route']['module'] . '/' . $config['request.route']['controller'] . '/' . $config['request.route']['action'];
    }
}