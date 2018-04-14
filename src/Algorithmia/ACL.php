<?php

namespace Algorithmia;

class ACL {
    const READABLE_BY_ANYONE = ["user://*"];
    const READABLE_BY_YOUR_ALGORITHMS = ["algo://.my/*"];
    const FULLY_PRIVATE = [];
    

    /**
     * Call with: ACL::getACLJson(ACL::READABLE_BY_ANYONE)
     */
    public static function getACLJson(array $in_type_array){
        $aclJson = ["acl" => ["read" => $in_type_array]];
        return json_encode($aclJson);
    }
}