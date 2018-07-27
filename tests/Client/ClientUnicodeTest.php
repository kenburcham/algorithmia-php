<?php

final class ClientUnicodeTest extends BaseTest
{
 
    public function testTextUnicode()
    {
        $telephone = "\u{260E}"; //unicode codepoint escape syntax (php7+)
 
        $client = $this->getClient();
        $result = $client->algo('util/Echo')->pipe($telephone);
        $this->assertEquals('text', $result->metadata->content_type);
        $this->assertEquals($telephone, $result->result);

        $result_uc = $client->algo('util/Echo')->pipe($result->result);
        $this->assertEquals('text', $result_uc->metadata->content_type);
        $this->assertEquals($telephone, $result_uc->result);
    }

    public function testJsonUnicode()
    {
        $telephone = "\u{260E}";
        $telephone_array = ["telephone" => $telephone];
 
        $client = $this->getClient();

        //$client->setOptions(['debug' => true]);

        $result = $client->algo('util/Echo')->pipe($telephone_array);

        $this->assertEquals('json', $result->metadata->content_type);
        $this->assertEquals($telephone, $result->result->telephone);

        $result_uc = $client->algo('util/Echo')->pipe($result->result);

        $this->assertEquals('json', $result_uc->metadata->content_type);
        $this->assertEquals($telephone, $result_uc->result->telephone);
        
    }

}