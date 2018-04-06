<?php
declare(strict_types=1);

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

        //file_put_contents($this->testDir . '/opencv_output.png', $response->result);

        $this->assertEquals('binary',$response->metadata->content_type);
        
    }
}