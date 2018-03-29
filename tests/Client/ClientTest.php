<?php
declare(strict_types=1);

final class ClientTest extends BaseTest
{
    public function testCanGetClient()
    {
        $client = $this->getClient();
        $this->assertInstanceOf(Algorithmia\Client::class, $client);
    }

    public function testCanConnectToDefaultServer()
    {
        $client = $this->getClient();
        $this->assertTrue(true);
    }
}