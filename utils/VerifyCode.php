<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\utils;

class VerifyCode
{
    private $code;
    private $length;
    private $width;
    private $height;

    public function __construct($width = 120, $len = 4, $height = 0)
    {
        $this->length = $len;
        $this->width = $width;
        $this->height = $height;
        $this->code = $this->createCode($len);
    }

    /**
     * @return string
     * @user LCF
     * @date 2019/3/15 22:40
     * 获取验证码内容
     */
    public function getCode()
    {
        return $this->code;
    }

    private function createCode($len)
    {
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= mt_rand(0, 9);
        }
        return $str;
    }

    /**
     * @user LCF
     * @date 2019/3/15 22:39
     * 获取验证码图片
     */
    public function verifyPng()
    {
        $height = $this->height;
        $width = (int)($this->width);
        $length = (int)($this->length);
        $code = $this->code;
        if ($height == 0) {
            $height = floor($width / 3);
        }
        $img = imagecreate($width, $height);
        imagecolorallocate($img, 255, 255, 255);
        for ($i = 0; $i < $width * 2; $i++) {
            $pointColor = imagecolorallocate($img, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
            imagesetpixel($img, mt_rand(0, $width), mt_rand(0, $height), $pointColor);
        }
        for ($i = 0; $i < $length; $i++) {
            $linkColor = imagecolorallocate($img, mt_rand(0, 220), mt_rand(0, 200), mt_rand(0, 200));
            imageline($img, mt_rand(0, $width / 2), mt_rand(0, $height), mt_rand($width / 2, $width), mt_rand(0, $height), $linkColor);
        }
        for ($i = 0; $i < $length - 2; $i++) {
            $arcColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 200), mt_rand(0, 255));
            imagearc($img, mt_rand(-10, $width + 10), mt_rand(-10, $height + 10), mt_rand($height, $width), mt_rand($height, $width), mt_rand(40, 50), mt_rand(30, 40), $arcColor);
        }
        for ($i = 0; $i < $length; $i++) {
            $codeColor = imagecolorallocate($img, mt_rand(0, 200), mt_rand(0, 128), mt_rand(0, 200));
            $yRand = (int)$height / 3;
            $charX = (($i * $width) / $length) + mt_rand(($length - $i), $yRand);
            $charY = mt_rand(3, $yRand + $length);
            imagestring($img, 5, $charX, $charY, $code[$i], $codeColor);
        }
        ob_clean();
        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("Content-type:image/png;");
        imagepng($img);
        imagedestroy($img);
        return;
    }
}