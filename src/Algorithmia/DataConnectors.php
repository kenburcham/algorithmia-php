<?php
namespace Algorithmia;


class DataConnectors {

    private static $connectors = array(
        "data",
        "s3",
        "dropbox",
    );
    
    public static function isValidConnector(string $in_connector){
        return in_array($in_connector, self::$connectors);
    }
}