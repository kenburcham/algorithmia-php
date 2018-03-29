<?php

use GuzzleHttp\ClientInterface;

class Algorithmia
{

    /**
     * Returns a newly created Algorithmia Client
     * @param string $in_key
     */
    public static function client($in_key = null) {
        return new Algorithmia\Client($in_key);
    }
}