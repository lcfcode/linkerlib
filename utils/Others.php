<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\utils;

class Others
{
    public static function encrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return base64_encode($str);
    }

    public static function decrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }

    public static function imgPng($path, $imgInfo, $fileName = null)
    {
        if (empty($imgInfo)) {
            return '';
        }
        try {
            $dir = ltrim($path, '/\\');
            $str = substr($dir, 0, stripos($dir, '/') + 1);
            if (stristr($imgInfo, $str)) {
                return $imgInfo;
            }
            $dir = rtrim($dir, '/\\');
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . $dir;
            $base64Info = substr($imgInfo, strpos($imgInfo, ',') + 1);
            $imgInfo = base64_decode($base64Info);
            if (empty($fileName)) {
                $fileName = strtolower(md5(uniqid(mt_rand(), true))) . '.png';
            } else {
                $fileName = $fileName . '.png';
            }
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $imgUrl = '/' . $dir . '/' . $fileName;
            $path = $path . '/' . $fileName;
            file_put_contents($path, $imgInfo);
            return $imgUrl;
        } catch (\Exception $e) {
            throw new \Exception('图片base64解码异常：' . $e->getMessage(), $e->getCode());
        }
    }

    public static function sortByMultiCols($rowset, $args)
    {
        $sortArray = [];
        $sortRule = '';
        foreach ($args as $sortField => $sortDir) {
            foreach ($rowset as $offset => $row) {
                $sortArray[$sortField][$offset] = $row[$sortField];
            }
            $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
        }
        if (empty($sortArray) || empty($sortRule)) {
            return $rowset;
        }
        eval('array_multisort(' . $sortRule . '$rowset);');
        return $rowset;
    }

    public static function unlinkFile($fileName)
    {
        if (is_file($fileName)) {
            @unlink($fileName);
            return true;
        }
        return false;
    }

    public static function delDir($dirName)
    {
        if ($handle = opendir($dirName)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir($dirName . '/' . $item)) {
                        self::delDir($dirName . '/' . $item);
                    } else {
                        unlink($dirName . '/' . $item);
                    }
                }
            }
            closedir($handle);
            rmdir($dirName);
        }
    }

    public static function delFile($dirName)
    {
        if ($handle = opendir($dirName)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir($dirName . '/' . $item)) {
                        self::delDir($dirName . '/' . $item);
                    } else {
                        unlink($dirName . '/' . $item);
                    }
                }
            }
            closedir($handle);
        }
    }
}