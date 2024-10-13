# Rogoss PHP-CURL

a small Lib meant to make fetching data via PHP-Curl easy.

# Usage

```php
<?php
    require_once "path/to/the/clone/of/this/repo/Curl.php",

    use \rogoss\Curl\Curl;

    /** @var \rogoss\Curl\FetchResult $oFetch */
    $oFetch = Curl::GET()                       //<- define your Method: POST(), DELETE() and PUT() are also possible
        ->url("http://request.to.somewhere")    // <- define where to send the request to
        ->header("accept", "application/json")  // <- set the request header 
        ->header("x-custom-header", "hello world")   
        //...
        ->param("foo", "bar")       // <- add Request-Parameters
        ->param("fizz", "buzz")
        //...
        ->debug(true) // <- enable Debug-Output to see the Raw Data send between servers
        //...
        ->exec() // <- run the Request and Fetch the Response
    ;

    if(empty($oFetch)) {
        echo "request failed, make sure the server is reachable and the URL is not empty";
        exit; 
    }

    // Available Fields on FetchResult
    $oFetch->protokoll;  // HTTP-Protocol
    $oFetch->status;     // HTTP Response-Status (200 OK, 404 Not Found, etc.)
    $oFetch->statusText; // Default text connected to the status "OK" for 200, etc. .. 
    $oFetch->body;       // The Raw Response-Body in plain-text format
    $oFetch->headers;    // An Array containing all Response-Headers

 ```
