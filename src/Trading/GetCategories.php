<?php

namespace BrainStorm\Ebay\Trading;

use BrainStorm\Ebay\EbayRequest;
use BrainStorm\Ebay\Request\WSAPI;

class GetCategories
{
	protected $request;

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
        foreach ($data as &$category) {
            //is there is no children array, add it
            if (!isset($category['Children'])) {
                $category['Children'] = array();
            }
            //check if there is a matching parent
            if (isset($data[$category['CategoryParentID']])) {
                //add this under the parent as a child by reference
                if (!isset($data[$category['CategoryParentID']]['Children'])) {
                    $data[$category['CategoryParentID']]['Children'] = array();
                }
                $data[$category['CategoryParentID']]['Children'][$category['CategoryID']] = &$category;
            //else, no parent found, add at top level
            } else {
                $nested[$category['CategoryID']] = &$category;
            }
        }
        unset($category);
        return json_encode($nested);
    }
    public function GetCategories($refresh_token, bool $cached = false, int $expire = 86400)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">    
		<ErrorLanguage>en_US</ErrorLanguage>
		<WarningLevel>High</WarningLevel>
		<DetailLevel>ReturnAll</DetailLevel>
		<ViewAllNodes>true</ViewAllNodes>
		</GetCategoriesRequest>';
        /*$response = $this->client->request('POST', '/ws/api.dll', [
            'headers' => [
            'X-EBAY-API-SITEID' => $this->ebayClient->siteid,
            'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->ebayClient->version,
            'X-EBAY-API-CALL-NAME' => 'GetCategories',
            'X-EBAY-API-IAF-TOKEN' => $this->ebayClient->getUserToken($refresh_token),
            'Content-Type' => 'text/xml; charset=UTF8'
            ],
            'body' => $xml
        ]);*/
		$response = $this->request->POST($refresh_token, 'GetCategories', $xml);
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