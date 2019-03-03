<?php

namespace MageArab\MegaFramework\Utils;

use MageArab\MegaFramework\Exceptions;
use MageArab\MegaFramework\Traits;
use function is_array, is_object, is_string;


/**
 * PHP callable tools.
 */
final class Callback
{
	use Traits\StaticClass;

    /**
     * @param  string|object|callable $callable class, object, callable
     * @param string|null $method
     * @return \Closure
     * @deprecated use Closure::fromCallable()
     */
	public static function closure($callable, string $method = null): \Closure
	{
		try {
			return \Closure::fromCallable($method === null ? $callable : [$callable, $method]);
		} catch (\TypeError $e) {
			throw new Exceptions\InvalidArgumentException($e->getMessage());
		}
	}


    /**
     * Invokes callback.
     * @param $callable
     * @param array $args
     * @return mixed
     * @deprecated
     */
	public static function invoke($callable, ...$args)
	{
		trigger_error(__METHOD__ . '() is deprecated, use native invoking.', E_USER_DEPRECATED);
		self::check($callable);
		return $callable(...$args);
	}


    /**
     * Invokes callback with an array of parameters.
     * @param $callable
     * @param array $args
     * @return mixed
     * @deprecated
     */
	public static function invokeArgs($callable, array $args = [])
	{
		trigger_error(__METHOD__ . '() is deprecated, use native invoking.', E_USER_DEPRECATED);
		self::check($callable);
		return $callable(...$args);
	}


    /**
     * Invokes internal PHP function with own error handler.
     * @param string $function
     * @param array $args
     * @param callable $onError
     * @return mixed
     */
	public static function invokeSafe(string $function, array $args, callable $onError)
	{
		$prev = set_error_handler(function ($severity, $message, $file) use ($onError, &$prev, $function): ?bool {
			if ($file === __FILE__) {
				$msg = $message;
				if (ini_get('html_errors')) {
					$msg = html_entity_decode(strip_tags($msg));
				}
				$msg = preg_replace("#^$function\(.*?\): #", '', $msg);
				if ($onError($msg, $severity) !== false) {
					return null;
				}
			}
			return $prev ? $prev(...func_get_args()) : false;
		});

		try {
			return $function(...$args);
		} finally {
			restore_error_handler();
		}
	}


    /**
     * @param $callable
     * @param bool $syntax
     * @return callable
     */
	public static function check($callable, bool $syntax = false)
	{
		if (!is_callable($callable, $syntax)) {
			throw new Exceptions\InvalidArgumentException($syntax
				? 'Given value is not a callable type.'
				: sprintf("Callback '%s' is not callable.", self::toString($callable))
			);
		}
		return $callable;
	}


	public static function toString($callable): string
	{
		if ($callable instanceof \Closure) {
			$inner = self::unwrap($callable);
			return '{closure' . ($inner instanceof \Closure ? '}' : ' ' . self::toString($inner) . '}');
		} elseif (is_string($callable) && $callable[0] === "\0") {
			return '{lambda}';
		} else {
			is_callable(is_object($callable) ? [$callable, '__invoke'] : $callable, true, $textual);
			return $textual;
		}
	}


	public static function toReflection($callable): \ReflectionFunctionAbstract
	{
		if ($callable instanceof \Closure) {
			$callable = self::unwrap($callable);
		}

		if (is_string($callable) && strpos($callable, '::')) {
            try {
                return new \ReflectionMethod($callable);
            } catch (\ReflectionException $e) {
            }
        } elseif (is_array($callable)) {
            try {
                return new \ReflectionMethod($callable[0], $callable[1]);
            } catch (\ReflectionException $e) {
            }
        } elseif (is_object($callable) && !$callable instanceof \Closure) {
            try {
                return new \ReflectionMethod($callable, '__invoke');
            } catch (\ReflectionException $e) {
            }
        } else {
            try {
                return new \ReflectionFunction($callable);
            } catch (\ReflectionException $e) {
            }
        }
	}


	public static function isStatic(callable $callable): bool
	{
		return is_array($callable) ? is_string($callable[0]) : is_string($callable);
	}


    /**
     * Unwraps closure created by Closure::fromCallable()
     * @internal
     * @param \Closure $closure
     * @return callable
     */
	public static function unwrap(\Closure $closure): callable
	{
        try {
            $r = new \ReflectionFunction($closure);
        } catch (\ReflectionException $e) {
        }
        if (substr($r->getName(), -1) === '}') {
			return $closure;

		} elseif ($obj = $r->getClosureThis()) {
			return [$obj, $r->getName()];

		} elseif ($class = $r->getClosureScopeClass()) {
			return [$class->getName(), $r->getName()];

		} else {
			return $r->getName();
		}
	}
}
