<?php

declare(strict_types=1);

final class ClientACLTest extends BaseTest
{
    public function testWorldReadable()
    {
        $world_readable_acl_json = '{"read":["user:\/\/*"]}';
        $this->assertEquals($world_readable_acl_json, json_encode(\Algorithmia\ACL::getACLJson(\Algorithmia\ACL::ANYONE)));
        //in the Algorithmia namespace this is easier:   ACL::getACLJson(ACL::ANYONE)
    }

    public function testAlgoReadable()
    {
        $algo_acl_json = '{"read":["algo:\/\/.my\/*"]}';
        $this->assertEquals($algo_acl_json, json_encode(\Algorithmia\ACL::getACLJson(\Algorithmia\ACL::MY_ALGORITHMS)));
    }

    public function testPrivateReadable()
    {
        $private_acl_json = '{"read":[]}';
        $this->assertEquals($private_acl_json, json_encode(\Algorithmia\ACL::getACLJson(\Algorithmia\ACL::FULLY_PRIVATE)));
    }
}
