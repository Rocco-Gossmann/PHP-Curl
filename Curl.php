<?php namespace rogoss\Curl;

require_once __DIR__ . "/FetchResult.php";

class Curl {

    // BM: Builders
    //==========================================================================

    public static function GET() : static {
        return new static("GET");
    }

    public static function POST() : static {
        return new static("POST");
    }

    public static function PUT() : static {
        return new static("PUT");
    }

    public static function DELETE() : static {
        return new static("DELETE");
    }


    // BM: Props/Methods
    //==========================================================================

    protected $oHandler;

    private string $sRequestMethod = "GET";
    private string $sURL = "";

    private bool $bDebugEnabled = false;
    private array $aHeaders = [];

    private array $aRequestparams = [];

    protected function __construct($sMethod) {
        $this->oHandler = curl_init();        
        $this->setCurlOpt("Method", CURLOPT_CUSTOMREQUEST, $sMethod);
        $this->sRequestMethod = $sMethod;
    }
    /**
     * Set the URL to which to send the Request
     * @param string $sURL = URL to send the Request to
     * @return static = builder-pattern return
     */
    public function url($sURL) : static {
        $this->setCurlOpt("URL", CURLOPT_URL, $sURL);
        $this->sURL = $sURL;
        return $this;
    }

    /**
     * Sets the Request-Body / Get-Params to the given array
     * @param array $aPostFields = your Request-Body
     * @return static = builder-pattern return
     */
    public function postFields(array $aPostFields) : static {
        $this->aRequestparams = $aPostFields;
        return $this;
    }

    public function param($sParamName, $mParamValue) : static {
        $this->aRequestparams[$sParamName] = $mParamValue;
        return $this;
    }

    public function debug($bEnabled) : static {
        $this->bDebugEnabled = $bEnabled == true;
        return $this;
    }

    public function header(string $sHeaderName, string $sHeaderValue) : static {
        $sNormalizedHeader = strtolower(trim($sHeaderName));
        $sNormalizedHeaderValue = str_replace(["\r\n", "\n"], " ", $sHeaderValue);
       
        if(strcmp($sNormalizedHeaderValue, $sHeaderValue)) 
            trigger_error("header values should not contain any line breaks", E_USER_WARNING);

        if(strcmp($sNormalizedHeader, $sHeaderName)) 
            trigger_error("given header names should be lower case and not contain whitespaces at beginning and end", E_USER_WARNING);

        $this->aHeaders[$sNormalizedHeader] = $sNormalizedHeaderValue;

        return $this;
    }

    /**
     * adds a CurlOpt to the current Curl-Request
     * @param int $cUrlOpt = one of the CURLOPT_ - constants
     * @param mixed $mValue = the value to set for that function
     * @return static = builder-pattern return
     */
    public function customCurlOpt($cUrlOpt, $mValue) : static {
        $this->setCurlOpt("CustomCurlOpt", $cUrlOpt , $mValue);
        return $this;
    }

	function exec() : FetchResult|null {

        if(empty($this->sURL))
            throw new CurlException($this->oHandler, "missing URL", CurlException::CURL_NO_URL);

        $this->setCurlOpt("exec", CURLOPT_RETURNTRANSFER, 1);
        $this->setCurlOpt("exec", CURLOPT_SSL_VERIFYPEER, 0);
        $this->setCurlOpt("exec", CURLOPT_HEADER, 1);
        
        if($this->bDebugEnabled)
            $this->setCurlOpt("exec", CURLOPT_VERBOSE, true);
        
        $this->setCurlOpt("exec", CURLOPT_HTTPHEADER, $aHeaders = array_map(
			fn($i, $e) => "$i: $e", 
			array_keys($this->aHeaders), 
			array_values($this->aHeaders)
		));	
        self::log("headers:", $aHeaders);

        $url = $this->sURL;
          
        $body = implode("&", array_map(
			fn($i, $e) => urlencode($i) . "=" . urlencode($e), 
			array_keys($this->aRequestparams), 
			array_values($this->aRequestparams)
		));	
        
        if($this->sRequestMethod == "POST") {
            $this->setCurlOpt("exec", CURLOPT_POSTFIELDS, $body);      

        } else {
            $url .= ((strpos($url, "?") === false) ? "?" : "&")
                . $body;

            $body = "";

            $this->setCurlOpt("exec", CURLOPT_URL, $url);
        }

		$this->log("Call: ", strtoupper($this->sRequestMethod) . " " . $url . "\n\nHeaders:\n" . implode("\n", $aHeaders) . "\n\nBody:", $body);

		if($this->bDebugEnabled) {
			$streamVerboseHandle = fopen('php://temp', 'w+');
			curl_setopt($this->oHandler, CURLOPT_STDERR, $streamVerboseHandle);
		}
		$result = curl_exec($this->oHandler);

		if($this->bDebugEnabled) {
			rewind($streamVerboseHandle);
			$verboseLog = stream_get_contents($streamVerboseHandle);
			$this->log("Verbose information:", $verboseLog);
		}

        if($result === false) return null;

		return new FetchResult($result);
	}


    // BM: Private Helpers
    //==========================================================================
    private function setCurlOpt($sOptName, $cOpt, $mValue) {
        if(!curl_setopt($this->oHandler, $cOpt, $mValue) || curl_errno($this->oHandler)) 
            throw new CurlException($this->oHandler, "failed ot set a curl_setopt '{$sOptName}' ", CurlException::CURL_SETOPTS_ERROR);
    }

	private function log() {
		if($this->bDebugEnabled) 
            foreach(func_get_args() as $mArgs) 
    			echo "[",__CLASS__," Debug] ", var_export($mArgs, true), "<br />\n";
	}
}