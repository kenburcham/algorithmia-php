<?php

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

    public function testDefaultDirectoryPermissions(){
        $client = $this->getClient();
        $newdir = $client->dir("data://.my/fooNew2ACL");
        
        if($newdir->exists())
            $newdir->delete(true); //because we need the permissions to be default!

        $newdir->create(); 
        $this->assertTrue($newdir->exists());

        $this->assertEquals($newdir->getReadAcl(), \Algorithmia\ACL::DEFAULT_PERMISSION);

        $newdir->delete();
    }

    public function testPublicDirectoryPermissions(){
        $client = $this->getClient();
        $newdir = $client->dir("data://.my/fooNew2ACL")->create(\Algorithmia\ACL::ANYONE); 
        $this->assertTrue($newdir->exists());

        $this->assertEquals($newdir->getReadAcl(), \Algorithmia\ACL::ANYONE);

        $newdir->delete();
    }
}
