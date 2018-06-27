Algorithmia Common Library (PHP)
================================

PHP client library for accessing the Algorithmia API
For API documentation, see the [PHPDocs](https://algorithmia.com/developers/clients/php/)

## Installation
Using the package manager [Composer](https://packagist.org), run:
```bash
composer require algorithmia/algorithmia
```

For non-Composer system, use the [source](https://github.com/algorithmiaio/algorithmia-php)

## Authentication
First, create an Algorithmia client and authenticate with your API key. You must replace YOUR_API_KEY with your personal key:

```PHP
require_once "vendor/autoload.php";

$client = Algorithmia::client('YOUR_API_KEY');
```

Note that you can also set the api key as an environment variable:

```php
//run the script with with:
php -dALGORITHMIA_API_KEY=ABC12345567483 myAI.php

//and then in myAI.php
$client = Algorithmia::client();

```

Now you're ready to call AI algorithms from your code. 

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
echo $response->metadata->duration; # 0.0002127 (just for example; this will vary, of course)
```

You can also call algorithms asynchronously and get back a promise:

```PHP
$algo = $client->algo('demo/Hello/0.1.1');
$async_promise = $algo->pipeAsync("HAL 9001");
$promise = $async_promise->then(function($server_response){
    //do something when the server returns a response
    return $server_response;
});
$response = $promise->wait(); //now lets wait for it to finish
echo $response->result;   #Hello HAL 9001

//or all at once... but generally you're wanting to do something when the response comes back like above...
$promise = $algo->pipeAsync("HAL 9001");
$response = $promise->wait(); //now lets wait for it to finish
echo $response->result;   #Hello HAL 9001

```



### JSON input/output

Call an algorithm with JSON input by passing in any object that can be serialized to JSON such as strings or arrays. 
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
$result = $client->algo("opencv/SmartThumbnail/0.1")->pipe($input)->result;
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
The Algorithmia client also provides a way to manage both Algorithmia hosted data
and data from Dropbox or S3 accounts that you've connected to you Algorithmia account.

### List items in a directory
Work with a directory by instantiating a `DataDirectory` object.

```PHP
$foo = $client->dir("data://.my/foo");

//now you can iterate files, folders or all items:

// List files in "foo"
foreach($foo->files() as $file){
    echo $file->getPath();
}

// List directories in "foo"
foreach ($foo->folders() as $dir){
    echo $dir->getPath();
}

// List everything in "foo"
foreach ($foo->list() as $item) {
    echo $item->getPath();
}

//Does it have this child folder?
$home = $client->dir("data://.my");
if($home->containsFolder("foo")) {...}

//or does a certain folder exist?
if($client->dir("data://.my/foo2")->exists()) { ... }

```


### Create directories
Create directories by instantiating a `DataDirectory` object and calling `create()`.

```PHP
$foo = $client->dir("data://.my/foo");
if(!$foo->exists()) {
    $foo->create();
}

//or just try to create it directly:
$client->dir("dropbox://mynewfolder")->create();

//note that the default permission is for only your own algorithms to view the directory. 
// if you want to let anyone view it:
$newdir = $client->dir("data://.my/mynewfolder")->create(ACL::ANYONE); 

//check the permission on a folder like so:
if($newdir->getReadAcl() == ACL::ANYONE) { ... }


```

### Upload files to a directory

Upload files by calling `put` on a `DataFile` object, 
or by calling `putFile` on a `DataDirectory` object.

```PHP
$foo = $client->dir("data://.my/foo");

//file.csv will be put into "foo" directory
$foo->putFile("/path/to/my/file.csv"); 

//you can also put a file directly to a folder with the name you want:
$client->file("data://.my/foo/my_new_file.txt")->put("/path/to/thefile.txt");

//put text directly into a new text file
$foo->file("sample.txt")->put("sample text information"); //write a new "sample.txt" in "foo" that has this text

//upload a binary file with a different name
$file = $client->file("data://.my/foo/binary_test.png")->putFile('/path/to/binary/file.png');
if($file->response->getStatusCode() !== 200) {...}; //you can also check the result of your action


```

Note: you can also instantiate a `DataFile` by either `$client->file('/path/to/file')` or `$client->dir('path')->file('filename')`


### Download contents of file

Download files by calling `getString`, `getBytes`, `getJson`, or `getFile` on a `DataFile` object:

```PHP
$foo_dir = $client->dir("data://.my/foo");
$file_content_text = $foo_dir->file("sample.txt")->getString();  # String object
$binary_content = $foo_dir->file("binary_file.jpg")->getBytes();  # Binary data
$json_object = $foo_dir->file("myfile.json")->getJson(); #Json object
$temp_file_name = $foo_dir->file("myfile.csv")->getFile();   # Download file to a temp file on the filesystem
$specified_file_name = $foo_dir->file("myfile.csv")->getFile('/path/to/file');   # Download file to a specified file location

$file_contents = file_get_contents($temp_file_name); //read the contents of the temp file you downloaded
```

Note: the `getFile()` method uses streams, so if you're getting large files, that's the way you'll want to do it to avoid memory issues.

### Delete files and directories

Delete files and directories by calling `delete` on their respective `DataFile` or `DataDirectory` object.
DataDirectories take an optional `force` parameter that indicates whether the directory should be deleted
if it contains files or other directories.

```PHP
$foo_dir = $client->dir("data://.my/foo");
$foo_dir->file("sample.txt")->delete(); 
$foo_dir->delete(); //will fail if the collection isn't empty
$foo_dir->delete(true); // true forces deleting the directory and its contents
```



### Directory permissions

Directory permissions may be set when creating a directory, or may be updated on already existing directories.

```PHP
$foo = $client->dir("data://.my/foo_public");

//create the foo_public directory if it doesn't exist
if(!$foo->exists()){
    $foo->create(ACL::ANYONE);
}

//check our permission
if($foo->getReadAcl() == ACL::ANYONE) { ... } //true

$client->dir("data://.my/foo_myalgos")->create(ACL::MY_ALGORITHMS);   
$client->dir("data://.my/foo_private")->create(ACL::FULLY_PRIVATE);   


```

# Running the tests

Running the tests will connect to the Algorithmia server and execute a variety of connections and exercises to validate the client. You'll need to provide your API KEY to run the tests in a file called "apikey.txt" in the /tests directory. Just create that file and paste in your API KEY with nothing else in the file.

Then you can run the tests by executing:
`./vendor/bin/phpunit -v tests`

Reading the tests is also a great way to see code examples.

# Enjoy!
