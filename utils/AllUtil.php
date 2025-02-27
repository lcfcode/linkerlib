<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\utils;

class AllUtil
{
    private $app;

    /**
     * @param $app
     */
    public function __construct(\swap\core\App $app)
    {
        $this->app = $app;
    }

    /**
     * @return mixed
     * @author LCF
     * @date
     * 全局配置
     */
    public function config()
    {
        return $this->app->config();
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
        $config = $this->app->getModuleConfig();
        if (isset($config[$configKey])) {
            return $config[$configKey];
        }
        $globalConfig = $this->config();
        if ($globalConfig[$configKey]) {
            return $globalConfig[$configKey];
        }
        return $default;
    }

    /**
     * @param $pwd
     * @return string
     * @author LCF
     * @date
     * 不常用的密码处理
     */
    public function passwordEncrypt($pwd)
    {
        return strtolower(md5(substr(md5($pwd), 0, -3)));
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
     * @param $context
     * @param null $content
     * @param string $file
     * @return bool|int
     * @author LCF
     * @date
     * 日常日志操作处理
     */
    public function logs($context, $content = null, $file = '')
    {
        $file = $file ? $file : $this->config()['request.log.file'];
        return $this->log($this->config()['logs'], $file, $context, $content);
    }

    /**
     * @param $e
     * @param string $name
     * @return bool|int
     * @author LCF
     * @date
     * 异常日志函数
     */
    public function catchLog($e, $name = '')
    {
        $file = $name ? $name : $this->config()['request.log.file'] . 'Action-exception';
        $log['异常,信息如下'] = [
            '异常文件' => $e->getFile(),
            '异常行数' => $e->getLine(),
            '异常代码' => $e->getCode(),
            '异常信息' => $e->getMessage(),
            '异常数组' => $e->getTrace(),
        ];
        return $this->log($this->config()['logs'], $file, $log);
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
        return $this->app->getUtils('RedisClass')->connect($config);
    }

    /**
     * @return mixed
     * @author LCF
     * @date 2019/8/17 18:35
     * 根目录
     */
    public function root()
    {
        return $this->config()['root.path'];
    }

    /**
     * @param $cookieKey
     * @return bool
     * @user LCF
     * @date 2019/3/13 14:28
     * 清空cookie
     */
    public function unsetCookieValue($cookieKey)
    {
        $result = self::setCookieValue($cookieKey, []);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @param $cookieKey
     * @return string
     * @user LCF
     * @date 2019/3/13 14:28
     * 获取cookie
     */
    public function getCookieValue($cookieKey)
    {
        $cookieInfo = isset($_COOKIE[$cookieKey]) ? $_COOKIE[$cookieKey] : '';
        if (empty($cookieInfo)) {
            return '';
        }
        return json_decode($cookieInfo, true);
    }

    /**
     * @param $cookieKey
     * @param $cookieArr
     * @param int $expires
     * @param string $dir
     * @return bool
     * @user LCF
     * @date 2019/3/13 14:29
     * 设置cookie
     */
    public function setCookieValue($cookieKey, $cookieArr, $expires = 604800, $dir = '/')
    {
        $serialize = json_encode($cookieArr);
        $result = setcookie($cookieKey, $serialize, time() + $expires, $dir);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @param $key
     * @param $value
     * @user LCF
     * @date 2019/3/13 14:29
     * 设置session
     */
    public function setSessionValue($key, $value)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @user LCF
     * @date 2019/3/13 14:29
     * 获取session
     */
    public function getSessionValue($key, $default = '')
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * @param $key
     * @user LCF
     * @date 2019/3/13 14:29
     * 清楚session
     */
    public function unsetSessionValue($key)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @user LCF
     * @date 2019/3/13 14:30
     * 设置session过期时间
     */
    public function setValAndExpire($key, $value, $expire)
    {
        ini_set('session.gc_maxlifetime', $expire);
        session_set_cookie_params($expire);
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION[$key] = $value;
    }

    /**
     * @param null $prefix
     * @return string
     * @user LCF
     * @date 2019/3/13 14:30
     * 获取uuid
     */
    public function getUuid($prefix = null)
    {
        return strtolower(md5(uniqid($prefix . php_uname('n') . mt_rand(), true)));
    }

    /**
     * @param $dir
     * @param $file
     * @param $info
     * @param null $content
     * @return bool|int
     * @user LCF
     * @date 2019/3/13 14:30
     * 日志记录
     */
    public function log($dir, $file, $info, $content = null)
    {
        $dir = rtrim($dir, "/\\");
        $dir = $dir . DIRECTORY_SEPARATOR . date('Ym') . DIRECTORY_SEPARATOR . date('d');
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                trigger_error('日志目录没有创建文件夹权限', E_USER_ERROR);
            }
        }
        $context = json_encode([
                'log_date' => '[' . date('Y-m-d H:i:s') . '][' . microtime() . ']',
                'log_info' => $info,
                'log_content' => $content,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
//        $files = preg_replace('/(::|\\\\|\/)/', '-', $file);
        $files = strtr($file, ['\\' => '-', '::' => '-', '/' => '-']);
        $fileName = $dir . DIRECTORY_SEPARATOR . $files . '.log';
        $put = @file_put_contents($fileName, $context, FILE_APPEND | LOCK_EX);
        if (false === $put) {
            trigger_error('日志目录没有写入文件权限', E_USER_ERROR);
        }
        return $put;
    }

    /**
     * @return array|false|string
     * @user LCF
     * @date 2019/3/13 14:32
     * 获取ip
     */
    public function getIp()
    {
        $defaultIp = '0.0.0.0';
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), $defaultIp)) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), $defaultIp)) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), $defaultIp)) {
                    $ip = getenv("REMOTE_ADDR");
                } else {
                    if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $defaultIp)) {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else {
                        $ip = $defaultIp;
                    }
                }
            }
        }
        return $ip;
    }

    /**
     * @return string
     * @user LCF
     * @date 2019/3/13 14:32
     */
    public function getHost()
    {
        return "http://" . $_SERVER['HTTP_HOST'];
    }

    /**
     * @param $url
     * @param int $timeOut
     * @param int $connectTimeOut
     * @return array
     * @user LCF
     * @date 2019/4/10 23:42
     */
    public function httpGet($url, $timeOut = 5, $connectTimeOut = 5)
    {
        $oCurl = curl_init();
        if (stripos($url, "http://") !== FALSE || stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        // 设置编码
//        $header = ['Content-Type:application/json;charset=UTF-8'];
//        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $connectTimeOut);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $error = curl_error($oCurl);
        curl_close($oCurl);
        if (intval($aStatus ["http_code"]) == 200) {
            return ['status' => true, 'content' => $sContent, 'code' => 200,];
        }
        return ['status' => false, 'content' => json_encode(["error" => $error, "url" => $url]), 'code' => $aStatus ["http_code"],];
    }

    /**
     * @param $url
     * @param $param
     * @param int $timeOut
     * @param int $connectTimeOut
     * @return array
     * @user LCF
     * @date 2019/4/10 23:42
     */
    public function httpPost($url, $param, $timeOut = 5, $connectTimeOut = 5)
    {
        $oCurl = curl_init();
        if (stripos($url, "http://") !== FALSE || stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST [] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $connectTimeOut);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $error = curl_error($oCurl);
        curl_close($oCurl);
        if (intval($aStatus ["http_code"]) == 200) {
            return ['status' => true, 'content' => $sContent, 'code' => 200,];
        }
        return ['status' => false, 'content' => json_encode(["error" => $error, "url" => $url]), 'code' => $aStatus ["http_code"],];
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2019/8/17 18:25
     * 获取 $_GET 参数
     */
    public function get($key = '', $default = '')
    {
        if (empty($key)) {
            return $_GET;
        }
        if (isset($_GET[$key])) {
            return trim($_GET[$key]);
        }
        return $default;
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2019/8/17 18:25
     * 获取 $_POST 参数
     */
    public function post($key = '', $default = '')
    {
        if (empty($key)) {
            return $_POST;
        }
        if (isset($_POST[$key])) {
            return trim($_POST[$key]);
        }
        return $default;
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2020/4/30 17:11
     * 获取 $_REQUEST 参数
     */
    public function param($key = '', $default = '')
    {
        if (empty($key)) {
            return $_REQUEST;
        }
        if (isset($_REQUEST[$key])) {
            return trim($_REQUEST[$key]);
        }
        return $default;
    }

    /**
     * @param $key
     * @param string $default
     * @return string|array
     * @author LCF
     * @date 2020/4/30 17:11
     * 获取 $_FILES 参数
     */
    public function file($key = '', $default = '')
    {
        if (empty($key)) {
            return $_FILES;
        }
        if (isset($_FILES[$key])) {
            return trim($_FILES[$key]);
        }
        return $default;
    }

    /**
     * @return bool
     * @author LCF
     * @date 2019/8/17 18:25
     * 判断请求方式是否是post
     */
    public function isPost()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            return true;
        }
        return false;
    }

}
