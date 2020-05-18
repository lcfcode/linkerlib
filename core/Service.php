<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

abstract class Service
{
    use Utiltrait;

    public function tableArrToString($table, $arrField, $prefix = null)
    {
        $str = '';
        foreach ($arrField as $key => $row) {
            if ($prefix) {
                $str .= ',' . $table . '.' . $row . ' as ' . $prefix . $row;
            } else {
                $str .= ',' . $table . '.' . $row;
            }
        }
        return substr($str, 1);
    }
}