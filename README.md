[![Build Status](https://travis-ci.com/sroehrl/neoan3-curl.svg?branch=master)](https://travis-ci.com/sroehrl/neoan3-curl)
[![Maintainability](https://api.codeclimate.com/v1/badges/8c285839f1e0d4a2e485/maintainability)](https://codeclimate.com/github/sroehrl/neoan3-curl/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/8c285839f1e0d4a2e485/test_coverage)](https://codeclimate.com/github/sroehrl/neoan3-curl/test_coverage)
# Simple PHP curl wrapper

This lightweight curl wrapper facilitates most common needs and is designed for server-2-server communication for Oauth flows, 
APIs using JSON responses and alike.

## Installation

`composer require neoan3-apps/curl`

## Quick start

Json responses are automatically decoded to associative arrays.

```php
try{
    $comments = \Neoan3\Apps\Curl::get('https://jsonplaceholder.typicode.com/posts/1/comments');
} catch (CurlException $e){
    echo $e->getMessage();
}


/* output
* [
*   ['postId' => 1, 'name' => 'labore ...']
*   [...]
* ]
*
*/


```
## Simplified calls
### get($url, $array = [], $auth = false, $authType = 'Bearer')
_NOTE:_ $array is converted to GET parameters
### post($url, $array = [], $auth = false, $authType = 'Bearer')
### put($url, $array = [], $auth = false, $authType = 'Bearer')

These calls are most common and can either be used with or without authorization. If $auth is set, the methods assume a Baerer token.

## curling($url, $arrayOrBody, $header, $type = 'POST')

Custom call where the header is set manually as an array and the method defaulting to POST

## setResponseFormatVerbose()

The class defaults to "plain" output only containing the payload of the response. this method changes the behavior to return 
responses in the following format:

```PHP
[
    'headers' => $headers, // array
    'body' => $responseBody, // array
    'status' => $status // int (e.g.200)
];
```

When switching between output formats, use **setResponseFormatPlain()** to reset behavior.

## Exceptions

CurlException is only thrown if responses are 500 & above. This means that a 404 **is a valid call** and evaluation of usefulness should be done elsewhere.

_SECURITY_: While I am sure your vendor-folder is probably already protected, neoan3-curl protects the _log folder with an .htaccess file.
Should you not use Apache and your vendor folder is visible, please take steps to secure the folder vendor/neoan3-apps/curl/_log
