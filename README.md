# Running the tests

Running the tests will connect to the Algorithmia server and execute a variety of connections and exercises to validate the client. This necessitates providing an API KEY that will be used to connect and run the tests. This BaseTest will try to load the API KEY from a file called "apikey.txt" in the /tests directory. Just create that file and paste in your API KEY with nothing else in the file (just hit return at the end of your key).

You can run the tests by executing:
`./vendor/bin/phpunit -v tests`

Text

Json

Binary
        $bin_input = new Algorithmia\ByteArray(file_get_contents($bin_file));

timeout

$client->setOptions(['timeout' => 60]); or
$client->setOptions(['timeout' => 60])->pipe($input);