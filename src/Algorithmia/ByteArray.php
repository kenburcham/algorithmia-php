<?php

namespace Algorithmia;

class ByteArray
{
    protected $data = '';

    public function __construct($in_data){
        $this->data = $in_data;
    }

    public function getData(){
        return $this->data;
    }
}