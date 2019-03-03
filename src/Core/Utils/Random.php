<?php

namespace MageArab\MegaFramework\Utils;

use MageArab\MegaFramework\Exceptions;
use MageArab\MegaFramework\Traits;


/**
 * Secure random string generator.
 */
final class Random
{
	use Traits\StaticClass;

    /**
     * Generate random string.
     * @param int $length
     * @param string $charlist
     * @return string
     */
	public static function generate(int $length = 10, string $charlist = '0-9a-z'): string
	{
		$charlist = count_chars(preg_replace_callback('#.-.#', function (array $m): string {
			return implode('', range($m[0][0], $m[0][2]));
		}, $charlist), 3);
		$chLen = strlen($charlist);

		if ($length < 1) {
			throw new Exceptions\InvalidArgumentException('Length must be greater than zero.');
		} elseif ($chLen < 2) {
			throw new Exceptions\InvalidArgumentException('Character list must contain at least two chars.');
		}

		$res = '';
		for ($i = 0; $i < $length; $i++) {
            try {
                $res .= $charlist[random_int(0, $chLen - 1)];
            } catch (\Exception $e) {
            }
        }
		return $res;
	}
}
