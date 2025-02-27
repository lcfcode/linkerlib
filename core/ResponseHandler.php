<?php

namespace swap\core;


class ResponseHandler
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function get()
    {
        return $this->data;
    }
}
