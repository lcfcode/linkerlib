<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

abstract class Service
{
    use Utiltrait;

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

}