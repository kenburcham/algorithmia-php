<?php

final class ClientTest extends BaseTest
{
    //basic text algorithms
    const ALGORITHM_ECHO = "util/Echo/0.2.1";
    const ALGORITHM_HELLO = "demo/Hello/0.1.0";
    const ALGORITHM_SUMMARIZER = "nlp/Summarizer/0.1.7";

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

    public function testClientSetTimeout()
    {
        $client = $this->getClient();
        $client->setOptions(['timeout' => 900]);

        $this->assertEquals(900, $client->getOptions()['timeout']);
    }

    public function testAlgoSetTimeout()
    {
        $client = $this->getClient();
    
        $algo = $client->algo(self::ALGORITHM_ECHO);

        $string_to_test = "PHP-Test";

        $response = $algo->setOptions(['timeout' => 3])->pipe($string_to_test);

        $this->assertEquals($response->result, $string_to_test);
        $this->assertEquals(3, $client->getOptions()['timeout']);

        $client->setOptions(['timeout' => 120]); //reset to the default

    }


    public function testAlgoTimeoutException()
    {
        $client = $this->getClient();
    
        $algo = $client->algo(self::ALGORITHM_SUMMARIZER);

        $string_to_test = "A purely peer-to-peer version of electronic cash would allow online payments to be sent directly from one party to another without going through a financial institution. Digital signatures provide part of the solution, but the main benefits are lost if a trusted third party is still required to prevent double-spending. We propose a solution to the double-spending problem using a peer-to-peer network. The network timestamps transactions by hashing them into an ongoing chain of hash-based proof-of-work, forming a record that cannot be changed without redoing the proof-of-work. The longest chain not only serves as proof of the sequence of events witnessed, but proof that it came from the largest pool of CPU power. As long as a majority of CPU power is controlled by nodes that are not cooperating to attack the network, they'll generate the longest chain and outpace attackers. The network itself requires minimal structure. Messages are broadcast on a best effort basis, and nodes can leave and rejoin the network at will, accepting the longest proof-of-work chain as proof of what happened while they were gone.";
        
        $this->expectException(\Algorithmia\AlgoException::class);
        
        //this will almost certainly fail (causing the test to succeed) 
        // because it needs more than .05 second to finish...
        $response = $algo->setOptions(['timeout' => .05])->pipe($string_to_test);
        
        $client->setOptions(['timeout' => 120]); //reset to the default
    }

    public function testClientConstructorBaseURL() {

        $nonexistent_server = "https://aaaapppi.algorithmia.com/api/v777/algo/";

        $client = new Algorithmia\Client("ABC123", $nonexistent_server);
        
        $this->assertEquals("https://aaaapppi.algorithmia.com", $client->getOptions()['server']);

        $algo = $client->algo(self::ALGORITHM_SUMMARIZER);

        $string_to_test = "A purely peer-to-peer version of electronic cash would allow online payments to be sent directly from one party to another without going through a financial institution. Digital signatures provide part of the solution, but the main benefits are lost if a trusted third party is still required to prevent double-spending. We propose a solution to the double-spending problem using a peer-to-peer network. The network timestamps transactions by hashing them into an ongoing chain of hash-based proof-of-work, forming a record that cannot be changed without redoing the proof-of-work. The longest chain not only serves as proof of the sequence of events witnessed, but proof that it came from the largest pool of CPU power. As long as a majority of CPU power is controlled by nodes that are not cooperating to attack the network, they'll generate the longest chain and outpace attackers. The network itself requires minimal structure. Messages are broadcast on a best effort basis, and nodes can leave and rejoin the network at will, accepting the longest proof-of-work chain as proof of what happened while they were gone.";
        
        $this->expectException(\Algorithmia\AlgoException::class);
        $response = $algo->pipe($string_to_test); //should fail because the server doesn't exist.    
    }

    public function testSetOptionOutputVoid()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_HELLO);

        $string_to_test = "HAL 9001";

        $client->setOptions(['output' => 'void']); //indicates we should not wait for a result (returns a promise with request id upon resolve instead of result)
        $response = $algo->pipe($string_to_test); //or pipeAsync does the same thing

        $this->assertInstanceOf(\GuzzleHttp\Promise\Promise::class, $response);   

        $response->then(function($obj){
            $this->assertTrue(property_exists($obj, 'request_id')); 
            //echo $obj->request_id;    //gives you the algo request id handle
        });
    }

    public function testSetOptionOutputRaw()
    {
        $client = $this->getClient();
        $client->setOptions(['output' => 'raw']); //indicates we should just get the raw output

        $response = $client->algo('util/Echo')->pipe(['something' => 'something else']);

        $this->assertEquals('{"something":"something else"}', $response);   
    }

    // this is an important one! 
    public function testAsyncPipe()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_HELLO);

        $string_to_test = "HAL 9001";

        $response = $algo->pipeAsync($string_to_test);
        $this->assertInstanceOf(\GuzzleHttp\Promise\Promise::class, $response);   

        //optional: you can chain "then" function if you want to do something when the request comes back
        $promise = $response->then(function($algoresponse){
            //do something
            return $algoresponse;
        });

        //but you could just comment out above and do:
        // $algoresponse = $response->wait();  
            //  you can do this because $response is already a promise that will return an algoresponse
 
        $this->assertInstanceOf(\GuzzleHttp\Promise\Promise::class, $promise);   

        $algoresponse = $promise->wait(); //ok now waith for things to finish...
        //echo $algoresponse->result;

        $this->assertInstanceOf(\Algorithmia\AlgoResponse::class, $algoresponse);
        $this->assertEquals($algoresponse->result, "Hello HAL 9001"); //HAL 9001
        
    }

    public function testAlgoException(){
        $client = $this->getClient();
        
        $algo = $client->algo('demo/HelloX/0.1.0');

        $this->expectException(\Algorithmia\AlgoException::class);       
        $result = $algo->pipe("HelloX!");

    }


}