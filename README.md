Algorithmia Common Library (PHP)
================================

PHP client library for accessing the Algorithmia API
For API documentation, see the [PHPDocs](https://algorithmia.com/docs/lang/PHP)

## Installation
Packaging is coming soon, but for now you can clone this repository and copy the Algorithmia folder into your project and point to it with your autoloader or add "use Algorithmia;" statements directly. You'll also need to run "composer update" to get the packages the client needs.

## Authentication
First, create an Algorithmia client and authenticate with your API key:

```PHP
use Algorithmia;

$api_key = '{{Your API key here}}';
$client = Algorithmia::client($api_key);
```


Now you're ready to call algorithms. 

## Calling algorithms

The following examples of calling algorithms are organized by type of input/output which vary between algorithms.

Note: a single algorithm may have different input and output types, or accept multiple types of input,
so consult the algorithm's description for usage examples specific to that algorithm.

### Text input/output

Call an algorithm with text input by simply passing a string into its `pipe` method.
If the algorithm output is text, then the `result` field of the response will be a string.

```PHP
$algo = $client->algo('demo/Hello/0.1.1');
$response = $algo->pipe("HAL 9000");
echo $response->result;    # Hello HAL 9000
echo $response->metadata->content_type;  # text
echo $response->metadata->duration; # 0.0002127
```

### JSON input/output

Call an algorithm with JSON input by simply passing in any object that can be serialized to JSON, such as strings or arrays. 
For algorithms that return JSON, the `result` field of the response will be the appropriate
deserialized type.

```PHP
$algo = $client->algo('WebPredict/ListAnagrams/0.1.0');
$result = $algo->pipe(["transformer", "terraforms", "retransform"])->result;
# -> ["transformer","retransform"]
```

### Binary input/output

Call an algorithm with binary input by passing the binary file contents wrapped in an Algorithmia\ByteArray into the `pipe` method.
Similarly, if the algorithm response is binary data, then the `result` field of the response
will be binary.

```PHP
$input = new Algorithmia\ByteArray(file_get_contents("/path/to/myimage.png"));
$result = $client->algo("opencv/SmartThumbnail/0.1")->pipe(input)->result;
# -> [binary byte sequence]

//if you want to write the result as a file:
file_put_contents('/path/to/destination/myimage_output.png', $result);
```

### Error handling

API errors and Algorithm exceptions will result in throwing an `AlgoException`:

```PHP
$client->algo('util/whoopsWrongAlgo')->pipe('Hello, world!')  
# Algorithmia\AlgoException: algorithm algo://util/whoopsWrongAlgo not found
```

### Request options

The client exposes options that can configure algorithm requests.
This includes support for changing the timeout or indicating that the API should include stdout in the response.

```PHP
$client->setOptions(['timeout' => 60]); //all subsequent calls to the client will have this new timeout
//or 
$response = $client->algo('util/echo')->setOptions(['timeout' => 60])->pipe($input); //set and call all in one fell swoop!

```

## Working with data
WIP

# Running the tests

Running the tests will connect to the Algorithmia server and execute a variety of connections and exercises to validate the client. You'll need to provide your API KEY to run the tests in a file called "apikey.txt" in the /tests directory. Just create that file and paste in your API KEY with nothing else in the file.

Then you can run the tests by executing:
`./vendor/bin/phpunit -v tests`

