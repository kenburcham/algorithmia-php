<?php

namespace Algorithmia;

class ACL {
    const ANYONE = "user://*"; //["user://*"];
    const MY_ALGORITHMS = "algo://.my/*"; //["algo://.my/*"];
    const FULLY_PRIVATE = ""; //[];

    const DEFAULT_PERMISSION = self::MY_ALGORITHMS;

    /**
     * Call with: ACL::getACLJson(ACL::ANYONE)
     * @param string One of the valid ACL constants
     * @return array Array of "read" + the ACL string wrapped in an array.
     */
    public static function getACLJson($in_type){
        $acl_type_array = ($in_type == self::FULLY_PRIVATE) ? [] : [$in_type];
        $aclJson = ["read" => $acl_type_array];
        return $aclJson; //json_encode($aclJson);
    }

    

}