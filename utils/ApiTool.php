<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\utils;

class ApiTool
{
    public static function success($data = [], $code = 1, $msg = '成功')
    {
        return self::outJson($code, $msg, $data);
    }

    public static function fail($code = 0, $data = [], $msg = '失败')
    {
        return self::outJson($code, $msg, $data);
    }

    public static function verifyFail($code = -1, $data = [], $msg = '参数错误')
    {
        return self::outJson($code, $msg, $data);
    }

    public static function except($code = -2, $data = [], $msg = '异常')
    {
        return self::outJson($code, $msg, $data);
    }

    public static function msg($code, $msg, $data = [])
    {
        return self::outJson($code, $msg, $data);
    }

    public static function fastcgi($code = 1, $msg = '成功', $data = [])
    {
        echo self::outJson($code, $msg, $data);
    }

    public static function outJson($code, $msg, $data)
    {
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}