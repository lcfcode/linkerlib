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
     * @var \swap\core\App
     */
    private static $app;

    public static function getApp()
    {
        if (!self::$app) {
            self::$app = \RegTree::get('app.application');
            if (!(self::$app instanceof \swap\core\App)) {
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
     * @return \swap\utils\AllUtil
     * @author LCF
     * @date 2020/4/30 17:07
     * 获取 utils 类的方法
     */
    public static function utils($name = 'AllUtil')
    {
        return self::getApp()->getUtils($name);
    }

    /**
     * @param $context
     * @param null $content
     * @param string $file
     * @return bool|int
     * @user LCF
     * @date 2020/8/1 18:21
     * 日志记录
     */
    public static function logs($context, $content = null, $file = '')
    {
        $file = $file ? $file : self::getApp()->config()['request.log.file'];
        return self::utils()->log(self::getApp()->config()['run.logs.path'], $file, $context, $content);
    }

}