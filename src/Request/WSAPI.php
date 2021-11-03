<?php

namespace BrainStorm\Ebay\Request;

use GuzzleHttp\Client;
use BrainStorm\Ebay\EbayRequest;


class WSAPI
{
	protected $client;
    protected $ebayClient;
	protected $version;
	protected $siteid;
    
    public function __construct(EbayRequest $ebayClient, $version = null, $siteid = null)
    {
        $this->ebayClient = $ebayClient;
		$this->version = ($version ? $version : $ebayClient->version);
		$this->siteid = ($siteid ? $siteid : $ebayClient->siteid);
        $this->client = $ebayClient->client;
    }
	public function POST($refresh_token, string $request, string $body) {
		return $this->client->request('POST', '/ws/api.dll', [
				'headers' => [
				'X-EBAY-API-SITEID' => $this->siteid,
				'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->version,
				'X-EBAY-API-CALL-NAME' => $request,
				'X-EBAY-API-IAF-TOKEN' => $this->ebayClient->getUserToken($refresh_token),
				'Content-Type' => 'text/xml; charset=UTF8'
				],
				'body' => $body
		]);
	}
}