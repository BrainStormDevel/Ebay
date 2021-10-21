<?php

namespace BrainStorm\Ebay\Trading;

use BrainStorm\Ebay\EbayRequest;
use BrainStorm\Ebay\Request\WSAPI;

class GetCategorySpecifics
{
	protected $request;
	protected $ebayClient;

    public function __construct(EbayRequest $ebayClient)
    {
		$this->ebayClient = $ebayClient;
        $this->request = new WSAPI($ebayClient);
    }

    public function GetCategorySpecifics($refresh_token, string $id, bool $cached = false, int $expire = 86400)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
		<GetCategorySpecificsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		<WarningLevel>High</WarningLevel>
		<CategorySpecific>
			<!--Enter the CategoryID for which you want the Specifics-->
		<CategoryID>'. $id .'</CategoryID>
		</CategorySpecific>
		</GetCategorySpecificsRequest>';
		$response = $this->request->POST($refresh_token, 'GetCategorySpecifics', $xml);
		$cachename = 'GetCategorySpecifics'. $id . $this->ebayClient->siteid;
		if (($cached) && $this->ebayClient->cache->has($cachename)) {
			return $this->ebayClient->cache->get($cachename);
		}
		$result = array();
		$i = 0;
		$to_obj = simplexml_load_string($response->getBody()->getContents());
		foreach($to_obj->Recommendations->NameRecommendation as $category){
			$name = (string) $category->Name;
			if ($name != 'NULL') {
				$result[$i]['Name'] = $name;
				foreach($category->ValueRecommendation as $values){
						$result[$i]['ValueRecommendation'][] = (string) $values->Value;
				}
				$result[$i]['ValidationRules'] = [
					'ValueType' => (string) $category->ValidationRules->ValueType,
					'MaxValues' => (string) $category->ValidationRules->MaxValues,
					'SelectionMode' => (string) $category->ValidationRules->SelectionMode,
					'UsageConstraint' => (string) $category->ValidationRules->UsageConstraint
				];
				$i++;
			}
		}
		$result = json_encode($result);
		if (($cached) && (!$this->ebayClient->cache->has($cachename))) {
			$this->ebayClient->cache->set($cachename, $result, $expire);
		}
		return $result;
    }
}