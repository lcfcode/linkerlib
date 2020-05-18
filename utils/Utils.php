<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */
namespace swap\utils;

class Utils
{
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
        //return strtolower(md5(uniqid($prefix . mt_rand(), true)));
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
    public function logs($dir, $file, $info, $content = null)
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
     * @param null $content
     * @user LCF
     * @date 2019/3/13 14:31
     * 打印信息到浏览器console栏
     */
    public function console($content)
    {
        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        echo "<script>console.group('debug.info');console.info({$content});console.groupEnd();</script>";
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
}
