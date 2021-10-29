<?php

namespace BrainStorm\Ebay\Trading;

use BrainStorm\Ebay\EbayRequest;
use BrainStorm\Ebay\Request\WSAPI;
use BrainStorm\Ebay\Trading\Types;

class GetCategorySpecifics
{
	protected $request;
	protected $ebayClient;

    public function __construct(EbayRequest $ebayClient)
    {
		$this->ebayClient = $ebayClient;
        $this->request = new WSAPI($ebayClient);
    }

    public function doRequest($refresh_token, string $id, bool $cached = false, int $expire = 86400)
    {
		$xml = new Types\GetCategorySpecificsRequestType();
		$xml->ErrorLanguage = 'en_US';
		$xml->WarningLevel = 'High';
		$xml->CategoryID[] = $id;		
		$response = $this->request->POST($refresh_token, 'GetCategorySpecifics', $xml->torequestxml());
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