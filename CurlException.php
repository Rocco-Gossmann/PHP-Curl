<?php namespace rogoss\Curl;

class CurlException extends \Exception {
    const CURL_SETOPTS_ERROR = 1;
    const CURL_NO_URL = 2;

    /** @var string */
    public $sCurlError = "";
    
    /** @var string */
    public $iCurlErrorNo = 0;

    public function __construct($hCurl, $sMsg, $iCode) {
        parent::__construct($sMsg, $iCode);
        $this->sCurlError = curl_error($hCurl);
        $this->iCurlErrorNo = curl_errno($hCurl);
    }

}