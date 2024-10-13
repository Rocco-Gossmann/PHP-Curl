<?php

require_once __DIR__ . "/Curl.php";

use \PHPUnit\Framework\TestCase;
use \rogoss\Curl\Curl;

class Test_Curl extends TestCase
{

    const SERVERURL = "http://localhost:42069";

    protected static $iPID;

    public static function setUpBeforeClass(): void
    {
        $sCMD =  'php -S localhost:42069 -t .testserver/' . ' > /dev/null 2>&1 & echo $!; ';
        self::$iPID = exec($sCMD, $output);
        echo "\nServerProcess: ", self::$iPID, "\n";
        sleep(4);

    }

    protected function setUp(): void {
        error_reporting(E_ALL);
    }

    public static function tearDownAfterClass(): void
    {
        echo "\nkilling process ", self::$iPID;
        if (self::$iPID) exec("kill " . self::$iPID);
    }

    public function testRequest_Minimal()
    {

        $oResponse = Curl::GET()->url(self::SERVERURL . "/index.php")->exec();

        $this->_assertResponse($oResponse);
        $this->assertEquals("hello world", $oResponse->body, "server did not respond with the expected body");
    }

    public function testRequest_WithCustomHeaders()
    {

        $sTestHeader = "fing/bonkers";

        $oResponse = Curl::GET()->url(self::SERVERURL . "/headerecho.php")
            ->header("content-type", $sTestHeader)
            ->exec();
        $this->_assertResponse($oResponse);
        $this->assertEquals($sTestHeader, $oResponse->body, "the header did not reach the Server");
    }

    public function testGetRequest_WithRequestParam()
    {
        $oResponse = Curl::GET()->url(self::SERVERURL . "/get_payload_echo.php")
            ->param("foo", "bar")
            ->param("fizz", "buzz")
            ->exec();

        $this->_assertParamEcho($oResponse);
    }
    public function testGetRequest_WithRequestParamAppend()
    {
        $oResponse = Curl::GET()->url(self::SERVERURL . "/get_payload_echo.php?fizz=buzz")
            ->param("foo", "bar")
            ->exec();

        $this->_assertParamEcho($oResponse);
    }
    public function testPostRequest_WithParams()
    {
        $oResponse = Curl::POST()->url(self::SERVERURL . "/post_payload_echo.php")
            ->param("fizz", "buzz")
            ->param("foo", "bar")
            ->exec();

        $this->_assertParamEcho($oResponse);
    }

    public function testPostRequest_WithRequestBody()
    {
        $oResponse = Curl::POST()->url(self::SERVERURL . "/post_payload_echo.php")
            ->body("fizz=buzz&foo=bar")
            ->exec();

        $this->_assertParamEcho($oResponse);
    }

    public function testPostRequest_WithParamsAndRequestBody()
    {
        //                  Can't test for Notices anymore, therefor, the notice
        //           \/---- that was expected here must be suppressed :-(
        $oResponse = @Curl::POST()->url(self::SERVERURL . "/post_mixedpayload_echo.php")
            ->param("fizz", "buzz")
            ->body("foo=bar")
            ->exec();

        $this->_assertParamEcho($oResponse);
    }


    // BM: Private Helpers
    //==========================================================================
    private function _assertResponse(\rogoss\Curl\FetchResult $oResponse)
    {
        $this->assertNotEmpty($oResponse, "result was not expected to be empty");
        $this->assertEquals(200, $oResponse->status, "server did not respond with status");
    }

    private function _assertParamEcho(\rogoss\Curl\FetchResult $oResponse)
    {

        $this->_assertResponse($oResponse);
        $aJSONPayload = json_decode($oResponse->body, true);

        $this->assertIsArray($aJSONPayload, "response should have been in JSON");
        $this->assertArrayHasKey("foo", $aJSONPayload);
        $this->assertArrayHasKey("fizz", $aJSONPayload);
        $this->assertEquals("bar", $aJSONPayload['foo']);
        $this->assertEquals("buzz", $aJSONPayload['fizz']);
    }
}

