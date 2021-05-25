<?php

namespace BrainStorm\Ebay;

use GuzzleHttp\Client;
use BrainStorm\Ebay\EbayRequest;

class Trading
{
    protected $client;
    protected $ebayClient;
    
    public function __construct(EbayRequest $ebayClient)
    {
        $this->ebayClient = $ebayClient;
        $this->client = new Client(['base_uri' => $ebayClient->url]);
    }
    public function GetCategories($refresh_token)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">    
		<ErrorLanguage>en_US</ErrorLanguage>
		<WarningLevel>High</WarningLevel>
		<DetailLevel>ReturnAll</DetailLevel>
		<ViewAllNodes>true</ViewAllNodes>
		</GetCategoriesRequest>';
        $response = $this->client->request('POST', '/ws/api.dll', [
            'headers' => [
            'X-EBAY-API-SITEID' => $this->ebayClient->siteid,
            'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->ebayClient->version,
            'X-EBAY-API-CALL-NAME' => 'GetCategories',
            'X-EBAY-API-IAF-TOKEN' => $this->ebayClient->getUserToken($refresh_token),
            'Content-Type' => 'text/xml; charset=UTF8'
            ],
            'body' => $xml
        ]);
        return json_encode(simplexml_load_string($response->getBody()->getContents()));
    }
}
