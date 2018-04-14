<?php

declare(strict_types=1);

final class ClientACLTest extends BaseTest
{
    public function testWorldReadable()
    {
        $world_readable_acl_json = '{"acl":{"read":["user:\/\/*"]}}';
        $this->assertEquals($world_readable_acl_json, \Algorithmia\ACL::getACLJson(\Algorithmia\ACL::READABLE_BY_ANYONE));
        //in the Algorithmia namespace this is easier:   ACL::getACLJson(ACL::READABLE_BY_ANYONE)
    }

    public function testAlgoReadable()
    {
        $algo_acl_json = '{"acl":{"read":["algo:\/\/.my\/*"]}}';
        $this->assertEquals($algo_acl_json, \Algorithmia\ACL::getACLJson(\Algorithmia\ACL::READABLE_BY_YOUR_ALGORITHMS));
    }

    public function testPrivateReadable()
    {
        $private_acl_json = '{"acl":{"read":[]}}';
        $this->assertEquals($private_acl_json, \Algorithmia\ACL::getACLJson(\Algorithmia\ACL::FULLY_PRIVATE));
    }
}
