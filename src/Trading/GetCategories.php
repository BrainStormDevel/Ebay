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