<?php
declare(strict_types=1);

final class ClientTest extends BaseTest
{
    //basic text algorithms
    const ALGORITHM_ECHO = "util/Echo/0.2.1";
    const ALGORITHM_HELLO = "demo/Hello/0.1.0";

    public function testCanGetClient()
    {
        $client = $this->getClient();
        $this->assertInstanceOf(Algorithmia\Client::class, $client);
    }

    public function testCanGetAlgorithmFromClient()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_ECHO);
        $this->assertInstanceOf(Algorithmia\Algorithm::class, $algo);
    }

    public function testCanGetAlgorithmFromStaticNoAPI()
    {
        $algo = Algorithmia::algo(self::ALGORITHM_ECHO);
        $this->assertInstanceOf(Algorithmia\Algorithm::class, $algo);
    }

    public function testCallEchoAlgorithm()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_ECHO);

        $string_to_test = "PHP-Test";

        $response = $algo->pipe($string_to_test);

        $this->assertEquals($response->result, $string_to_test);
    }

    public function testCallHelloAlgorithmMetadata()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_HELLO);

        $string_to_test = "HAL 9000";

        $response = $algo->pipe($string_to_test);

        $this->assertEquals($response->result, "Hello ".$string_to_test);  
        $this->assertEquals($response->metadata->content_type, 'text'); 
        $this->assertGreaterThan(0, $response->metadata->duration);
    }
}