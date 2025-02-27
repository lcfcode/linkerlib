<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\utils;

class ProjectTool
{
    
    public function imgPng($path, $imgInfo, $fileName = null)
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
            $path = $path . DIRECTORY_SEPARATOR . $fileName;
            file_put_contents($path, $imgInfo);
            return $imgUrl;
        } catch (\Exception $e) {
            throw new \Exception('图片base64解码异常：' . $e->getMessage(), $e->getCode());
        }
    }
}