<?php

namespace BrainStorm\Ebay;

class common {
	
	const VERSION = '18.0.0';

    /**
     * @var bool Controls if the SDK should enforce strict types
     * when values are assigned to property classes.
     */
    public static $STRICT_PROPERTY_TYPES = true;

	public static function checkPropertyType($type)
	{
		if (self::$STRICT_PROPERTY_TYPES) {
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