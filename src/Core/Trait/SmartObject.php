<?php

namespace MageArab\MegaFramework\Traits;

use MageArab\MegaFramework\Exceptions;
use MageArab\MegaFramework\Utils;


/**
 * Strict class for better experience.
 * - 'did you mean' hints
 * - access to undeclared members throws exceptions
 * - support for @property annotations
 * - support for calling event handlers stored in $onEvent via onEvent()
 */
trait SmartObject
{

    /**
     * @param string $name
     * @param array $args
     * @throws \ReflectionException
     */
	public function __call(string $name, array $args)
	{
		$class = get_class($this);

		if (Utils\ObjectHelpers::hasProperty($class, $name) === 'event') { // calling event handlers
			if (is_iterable($this->$name)) {
				foreach ($this->$name as $handler) {
					$handler(...$args);
				}
			} elseif ($this->$name !== null) {
				throw new Exceptions\UnexpectedValueException("Property $class::$$name must be iterable or null, " . gettype($this->$name) . ' given.');
			}

		} else {
            Utils\ObjectHelpers::strictCall($class, $name);
		}
	}


    /**
     * @param string $name
     * @param array $args
     * @throws \ReflectionException
     */
	public static function __callStatic(string $name, array $args)
	{
        Utils\ObjectHelpers::strictStaticCall(get_called_class(), $name);
	}


    /**
     * @param string $name
     * @return mixed
     * @throws \ReflectionException
     */
	public function &__get(string $name)
	{
		$class = get_class($this);

		if ($prop = Utils\ObjectHelpers::getMagicProperties($class)[$name] ?? null) { // property getter
			if (!($prop & 0b0001)) {
				throw new Exceptions\MemberAccessException("Cannot read a write-only property $class::\$$name.");
			}
			$m = ($prop & 0b0010 ? 'get' : 'is') . $name;
			if ($prop & 0b0100) { // return by reference
				return $this->$m();
			} else {
				$val = $this->$m();
				return $val;
			}
		} else {
            Utils\ObjectHelpers::strictGet($class, $name);
		}
	}


    /**
     * @param string $name
     * @param $value
     * @return void
     * @throws \ReflectionException
     */
	public function __set(string $name, $value)
	{
		$class = get_class($this);

		if (Utils\ObjectHelpers::hasProperty($class, $name)) { // unsetted property
			$this->$name = $value;

		} elseif ($prop = Utils\ObjectHelpers::getMagicProperties($class)[$name] ?? null) { // property setter
			if (!($prop & 0b1000)) {
				throw new Exceptions\MemberAccessException("Cannot write to a read-only property $class::\$$name.");
			}
			$this->{'set' . $name}($value);

		} else {
            Utils\ObjectHelpers::strictSet($class, $name);
		}
	}


    /**
     * @param string $name
     * @return void
     */
	public function __unset(string $name)
	{
		$class = get_class($this);
		if (!Utils\ObjectHelpers::hasProperty($class, $name)) {
			throw new Exceptions\MemberAccessException("Cannot unset the property $class::\$$name.");
		}
	}


	public function __isset(string $name): bool
	{
        try {
            return isset(Utils\ObjectHelpers::getMagicProperties(get_class($this))[$name]);
        } catch (\ReflectionException $e) {
        }
    }
}
