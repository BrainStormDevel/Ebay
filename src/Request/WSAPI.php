<?php

namespace BrainStorm\Ebay\Request;

use GuzzleHttp\Client;
use BrainStorm\Ebay\EbayRequest;


class WSAPI
{
	protected $client;
    public $ebayClient;
    
    public function __construct(EbayRequest $ebayClient)
    {
        $this->ebayClient = $ebayClient;
        $this->client = new Client(['base_uri' => $ebayClient->url]);
    }
	public function POST($refresh_token, string $request, string $body) {
		return $this->client->request('POST', '/ws/api.dll', [
				'headers' => [
				'X-EBAY-API-SITEID' => $this->ebayClient->siteid,
				'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->ebayClient->version,
				'X-EBAY-API-CALL-NAME' => $request,
				'X-EBAY-API-IAF-TOKEN' => $this->ebayClient->getUserToken($refresh_token),
				'Content-Type' => 'text/xml; charset=UTF8'
				],
				'body' => $body
		]);
	}
}