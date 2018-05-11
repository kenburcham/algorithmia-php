<?php

class Algorithmia
{

    /**
     * Returns a newly created Algorithmia Client
     * @param string $in_key The API key to use when making requests.
     */
    public static function client($in_key = null) {
        return new Algorithmia\Client($in_key);
    }

    /**
     * Returns a newly created Algorithm
     * @param string $in_algo The algorithm to call.
     */
    public static function algo($in_algo = null) {
        return new Algorithmia\Algorithm($in_algo, self::client());
    }
}