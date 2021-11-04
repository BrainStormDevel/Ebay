<?php

namespace BrainStorm\Ebay\Trading;

use BrainStorm\Ebay\EbayRequest;
use BrainStorm\Ebay\Request\WSAPI;
use BrainStorm\Ebay\Trading\Types;

class GetCategories
{
	protected $request;
	protected $ebayClient;

    public function __construct(EbayRequest $ebayClient)
    {
		$this->ebayClient = $ebayClient;
        $this->request = new WSAPI($ebayClient);
    }

    protected function makeNestedData($data)
    {
        //create a nested tree using the above structure
        $nested = array();
    
        //loop over each category
	foreach ($data as &$s) {
		if ($s['CategoryLevel'] == 1) {
			$cidlv1 = $s['CategoryID'];
			$nested[$cidlv1] = &$s;
		}
		else {
			if ($s['CategoryLevel'] == 2) {
				$cidlv2 = $s['CategoryID'];
				$pidlv1 = $s['CategoryParentID'];
				if ( !isset($nested[$cidlv1]['Children']) ) {
					$nested[$cidlv1]['Children'] = array();
				}
					
				$nested[$cidlv1]['Children'][] = &$s;
			}
		}
	}
        return json_encode($nested);
    }
    public function doRequest($refresh_token, bool $cached = false, int $expire = 86400)
    {
		$xml = new Types\GetCategoriesRequestType();
		$xml->ErrorLanguage = 'en_US';
		$xml->WarningLevel = 'High';
		$xml->DetailLevel[] = 'ReturnAll';
		$xml->ViewAllNodes = true;
		$response = $this->request->POST($refresh_token, 'GetCategories', $xml->torequestxml());
		$result = simplexml_load_string($response->getBody()->getContents());
		if ($result->Ack == 'Success') {
			$cachename = 'GetCategories'. $this->ebayClient->siteid;
			if (($cached) && ($this->ebayClient->cache->has($cachename))) {
				return $this->ebayClient->cache->get($cachename);
			}
			$allcategory = array();
			foreach ($result->CategoryArray->Category as $category){
				$CategoryID = (string) $category->CategoryID;
				$CategoryParentID = (string) $category->CategoryParentID;
				$allcategory[] = [
					'BestOfferEnabled' => (bool) $category->BestOfferEnabled,
					'AutoPayEnabled' => (bool) $category->AutoPayEnabled,
					'CategoryLevel' => (string) $category->CategoryLevel,
					'CategoryName' => (string) $category->CategoryName,
					'CategoryID' => $CategoryID,
					'CategoryParentID' => $CategoryParentID
				];						
			}
			$thisresponse = $this->makeNestedData($allcategory);
			if (($cached) && (!$this->ebayClient->cache->has($cachename))) {
				$this->ebayClient->cache->set($cachename, $thisresponse, $expire);
			}
			return $thisresponse;
		}
    }
}
