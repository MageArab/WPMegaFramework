<?php

namespace MageArab\MegaFramework\Utils;
use MageArab\MegaFramework\Traits;


/**
 * Object behaviour mixin.
 * @deprecated
 */
final class ObjectMixin
{
	use Traits\StaticClass;

    /**
     * @deprecated  use ObjectHelpers::getSuggestion()
     * @param array $possibilities
     * @param string $value
     * @return null|string
     */
	public static function getSuggestion(array $possibilities, string $value): ?string
	{
		trigger_error(__METHOD__ . '() has been renamed to MageArab\MegaFramework\Utils\ObjectHelpers::getSuggestion()', E_USER_DEPRECATED);
		return ObjectHelpers::getSuggestion($possibilities, $value);
	}


	public static function setExtensionMethod($class, $name, $callback)
	{
		trigger_error('Class MageArab\MegaFramework\Utils\ObjectMixin is deprecated', E_USER_DEPRECATED);
	}


	public static function getExtensionMethod($class, $name)
	{
		trigger_error('Class MageArab\MegaFramework\Utils\ObjectMixin is deprecated', E_USER_DEPRECATED);
	}
}
