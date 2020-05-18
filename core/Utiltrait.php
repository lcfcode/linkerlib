<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

trait Utiltrait
{
    /**
     * @return mixed
     * @author LCF
     * @date 2019/8/17 18:31
     * 全局配置的方法
     */
    public function config()
    {
        return $this->app()->config();
    }

    /**
     * @return \swap\linker\App
     * @user LCF
     * @date 2019/3/15 22:27
     * Linker的实例
     */
    public function app()
    {
        return \RegTree::get('app.application');
    }

    /**
     * @param $configKey
     * @param null $default
     * @return null
     * @author LCF
     * @date 2019/8/17 18:32
     * 获取配置的方法
     */
    public function getConfigValue($configKey, $default = null)
    {
        $config = $this->app()->getModuleConfig();
        if (isset($config[$configKey])) {
            return $config[$configKey];
        }
        $globalConfig = $this->config()['global.config'];
        if ($globalConfig[$configKey]) {
            return $globalConfig[$configKey];
        }
        return $default;
    }

    public function passwordEncrypt($pwd)
    {
        return strtolower(md5(md5(substr(md5(substr(md5($pwd), 0, -3)), 3))));
    }

    /**
     * @param $pwd
     * @return bool|string
     * @author LCF
     * @date 2019/8/17 21:02
     * 哈希密码加密
     */
    public function passwordHash($pwd)
    {
        $password = $this->passwordEncrypt($pwd);
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @param $pwd
     * @param $hash
     * @return bool
     * @author LCF
     * @date 2019/8/17 21:01
     * 哈希密码验证
     */
    public function passwordVerify($pwd, $hash)
    {
        $password = $this->passwordEncrypt($pwd);
        return password_verify($password, $hash);
    }

    /**
     * @return false|string
     * @deprecated 推荐使用getDate()
     * @author LCF
     * @date 2019/8/17 18:32
     * mysql的标准时间格式
     */
    public function getTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @return false|string
     * @author LCF
     * @date 2019/8/17 18:32
     * mysql的标准时间格式
     */
    public function getDate()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @param null $prefix
     * @return string
     * @author LCF
     * @date 2019/8/17 18:33
     * 返回uuid
     */
    public function uuid($prefix = null)
    {
        return $this->getUtils('Utils')->getUuid($prefix);
    }

    /**
     * @param $context
     * @param null $content
     * @return $this
     * @author LCF
     * @date 2019/8/17 18:33
     * 日志操作函数
     */
    public function logs($context, $content = null)
    {
        $this->getUtils('Utils')->logs($this->config()['run.logs.path'], $this->config()['request.log.file'], $context, $content);
        return $this;
    }

    /**
     * @param $e \Exception
     * @param string $name
     * @return $this
     * @author LCF
     * @date 2019/8/17 18:33
     * 异常日志函数
     */
    public function catchLog($e, $name = '')
    {
        if ($name) {
            $file = $name;
        } else {
            $file = $this->config()['request.log.file'] . 'Action-exception';
        }
        $str = '异常,信息如下：' . PHP_EOL;
        $str .= '    异常文件 : ' . $e->getFile() . PHP_EOL;
        $str .= '    异常行数 : ' . $e->getLine() . PHP_EOL;
        $str .= '    异常代码 : ' . $e->getCode() . PHP_EOL;
        $str .= '    异常信息 : ' . $e->getMessage() . PHP_EOL;
        $str .= '    异常数组 : ' . json_encode($e->getTrace(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $this->getUtils('Utils')->logs($this->config()['run.logs.path'], $file, $str);
        return $this;
    }

    public function getYMD($time = '')
    {
        if (empty($time)) {
            return date("Y-m-d", time());
        }
        return date("Y-m-d", strtotime($time));
    }

    /**
     * @param array $config
     * @return \redis
     * @user LCF
     * @date 2019/3/15 22:27
     * 获取redis
     */
    public function getRedis($config = [])
    {
        if (empty($config)) {
            $config = $this->config()['global.config']['redis'];
        }
        return $this->getUtils('RedisClass')->connect($config);
    }

    /**
     * @param string $name
     * @return \swap\utils\Utils
     * @user LCF
     * @date 2019/4/10 9:33
     */
    public function getUtils($name = 'Utils')
    {
        return $this->app()->getUtils($name);
    }

    /**
     * @return mixed
     * @author LCF
     * @date 2019/8/17 18:35
     * 当前工作目录
     */
    public function root()
    {
        return $this->config()['root.path'];
    }
}