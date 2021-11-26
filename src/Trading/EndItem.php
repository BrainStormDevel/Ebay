<?php

namespace BrainStorm\Ebay\Trading;

use BrainStorm\Ebay\EbayRequest;
use BrainStorm\Ebay\Request\WSAPI;
use BrainStorm\Ebay\Trading\Types;

class EndItem
{
	protected $request;
	protected $ebayClient;

    public function __construct(EbayRequest $ebayClient)
    {
		$this->ebayClient = $ebayClient;
        $this->request = new WSAPI($ebayClient);
    }
	
    public function doRequest($refresh_token, string $id, string $comment)
    {
		$xml = new Types\EndItemRequestType();
		$xml->ErrorLanguage = 'en_US';
		$xml->WarningLevel = 'High';
		$xml->ItemID = $id;
		$xml->EndingReason = $comment;
		$response = $this->request->POST($refresh_token, 'EndItem', $xml->torequestxml());
		$result = array();
		$i = 0;
		$to_obj = simplexml_load_string($response->getBody()->getContents());
		return $to_obj;
	}
}