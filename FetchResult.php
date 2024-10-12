<?php namespace rogoss\Curl;

class FetchResult {
	/** @var string */
	public $protokoll = "";

	/** @var int */
	public $status = 0;

	/** @var string */
	public $statusText = "";

	/** @var string */
	public $body = "";

	/** @var array */
	public $headers = [];

	public function __construct($r)
	{
		$aParts = explode("\n\n", str_replace("\r", "", $r), 2); 
		$this->body = $aParts[1] ?? "";
		$sHeaders = $aParts[0] ?? "";
		unset($aParts);

		$aHeaderLines = explode("\n", $sHeaders);

		$aStatusLine = explode(" ", $aHeaderLines[0], 3);
		$this->protokoll = $aStatusLine[0];
		$this->status = (int)$aStatusLine[1];
		$this->statusText = $aStatusLine[2];
		unset($aStatusLine, $aHeaderLines[0]);

		foreach($aHeaderLines as $sHeaderLine) {
			$aParts = explode(": ", $sHeaderLine);
			$this->headers[strtolower($aParts[0])] = $aParts[1];
			unset($aParts);
		}

	}
}
