<?php

final class ClientBinaryTest extends BaseTest
{
    //binary algorithms to test
    const ALGORITHM_SMARTTHUMBNAIL = "opencv/SmartThumbnail/0.1";
    const SMARTTHUMBNAIL_IMAGE = "opencv_example.png";

    
    public function testBinaryInBinaryOut_SmartThumbnail()
    {
        $client = $this->getClient();
        
        $algo = $client->algo(self::ALGORITHM_SMARTTHUMBNAIL);

        $bin_file = $this->testDir . '/'. self::SMARTTHUMBNAIL_IMAGE;

        $bin_input = new Algorithmia\ByteArray(file_get_contents($bin_file));

        $response = $algo->pipe($bin_input);

        //if you want to manually view the result, uncomment this line.
        //file_put_contents($this->testDir . '/opencv_output.png', $response->result);

        $this->assertEquals('binary',$response->metadata->content_type);
        
    }


    public function testStringInBinaryOut_SmartThumbnail()
    {
        $client = $this->getClient();
        
        $algo = $client->algo(self::ALGORITHM_SMARTTHUMBNAIL);

        $str_input = "data://opencv/temp_zeryx/1.png";

        $response = $algo->pipe($str_input);

        //if you want to manually view the result, uncomment this line.
        //file_put_contents($this->testDir . '/opencv_output.png', $response->result);

        $this->assertEquals('binary',$response->metadata->content_type);
        
    }

    /* WIP
    public function testArrayInBinaryOut_SmartThumbnail()
    {
        $client = $this->getClient();
        
        $algo = $client->algo(self::ALGORITHM_SMARTTHUMBNAIL);

        $bin_file = $this->testDir . '/'. self::SMARTTHUMBNAIL_IMAGE;

        $bin_input = array(
            'input' => "[]", //json_encode(file_get_contents($bin_file)),
            'thumbnailWidth' => 200,
            'thumbnailHeight' => 200
        );

        $bin_input = "data://opencv/temp_zeryx/1.png";

        $response = $algo->pipe($bin_input);

        //if you want to manually view the result, uncomment this line.
        //file_put_contents($this->testDir . '/opencv_output.png', $response->result);

        $this->assertEquals('binary',$response->metadata->content_type);
        
    }*/
}