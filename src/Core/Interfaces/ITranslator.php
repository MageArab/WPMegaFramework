<?php

namespace MageArab\MegaFramework\Interfaces;

/**
 * Translator adapter.
 */
interface ITranslator
{

    /**
     * Translates the given string.
     * @param $message
     * @param array $parameters
     * @return string
     */
	function translate($message, ...$parameters): string;
}
