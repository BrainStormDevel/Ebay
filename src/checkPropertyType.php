<?php

namespace BrainStorm\Ebay;

class checkPropertyType {

	public function __invoke($type)
	{
		if (\DTS\eBaySDK\Sdk::$STRICT_PROPERTY_TYPES) {
			return true;
		}
	
		switch ($type) {
			case 'integer':
			case 'string':
			case 'double':
			case 'boolean':
			case 'DateTime':
				return false;
			default:
				return true;
		}
	}
}