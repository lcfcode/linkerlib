<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

spl_autoload_register('RegTree::autoload', true, true);

class RegTree
{
    private static $classMap = [];
    private static $objects = [];
    private static $root = null;

    /**
     * @param $class
     * @return bool
     * @throws Exception
     * @date 2018/8/10 9:46
     * @功能 自动加载函数
     */
    public static function autoload($class)
    {
        if (class_exists($class, false)) {
            return true;
        }
        if (isset(self::$classMap[$class])) {
            return true;
        }
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $file = self::root() . DIRECTORY_SEPARATOR . $className . '.php';
        if (is_file($file)) {
            require $file;
            self::$classMap[$class] = $file;
            return true;
        }
        return false;
    }

    public static function root()
    {
        if (empty(self::$root)) {
            self::$root = dirname(__DIR__);
        }
        return self::$root;
    }

    public static function set($alias, $object)
    {
        self::$objects[$alias] = $object;
    }

    public static function get($alias)
    {
        if (isset(self::$objects[$alias])) {
            return self::$objects[$alias];
        }
        throw new \RuntimeException('没有找到对应的实例', 500);
    }

    public static function _unset($alias)
    {
        unset(self::$objects[$alias]);
    }

    public static function getAll()
    {
        return self::$objects;
    }
}