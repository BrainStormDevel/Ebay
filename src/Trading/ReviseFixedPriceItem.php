<?php

namespace BrainStorm\Ebay\Trading;

use BrainStorm\Ebay\EbayRequest;
use BrainStorm\Ebay\Request\WSAPI;

class ReviseFixedPriceItem
{
	protected $request;
	protected $ebayClient;

    public function __construct(EbayRequest $ebayClient)
    {
		$this->ebayClient = $ebayClient;
        $this->request = new WSAPI($ebayClient);
    }
	
    public function doRequest($refresh_token, string $body)
    {
		$response = $this->request->POST($refresh_token, 'ReviseFixedPriceItem', $body);
		$result = array();
		$i = 0;
		$to_obj = simplexml_load_string($response->getBody()->getContents());
		return $to_obj;
	}
}