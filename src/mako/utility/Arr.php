<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use RuntimeException;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_rand;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function is_object;
use function strpos;
use function trim;

/**
 * Array helper.
 *
 * @author Frederic G. Østby
 */
class Arr
{
	/**
	 * Sets an array value using "dot notation".
	 *
	 * @param array  &$array Array you want to modify
	 * @param string $path   Array path
	 * @param mixed  $value  Value to set
	 */
	public static function set(array &$array, string $path, $value)
	{
		$segments = explode('.', $path);

		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if(!isset($array[$segment]) || !is_array($array[$segment]))
			{
				$array[$segment] = [];
			}

			$array =& $array[$segment];
		}

		$array[array_shift($segments)] = $value;
	}

	/**
	 * Search for an array value using "dot notation". Returns TRUE if the array key exists and FALSE if not.
	 *
	 * @param  array  $array Array we're goint to search
	 * @param  string $path  Array path
	 * @return bool
	 */
	public static function has(array $array, string $path): bool
	{
		$segments = explode('.', $path);

		foreach($segments as $segment)
		{
			if(!is_array($array) || !array_key_exists($segment, $array))
			{
				return false;
			}

			$array = $array[$segment];
		}

		return true;
	}

	/**
	 * Returns value from array using "dot notation".
	 *
	 * @param  array  $array   Array we're going to search
	 * @param  string $path    Array path
	 * @param  mixed  $default Default return value
	 * @return mixed
	 */
	public static function get(array $array, string $path, $default = null)
	{
		$segments = explode('.', $path);

		foreach($segments as $segment)
		{
			if(!is_array($array) || !array_key_exists($segment, $array))
			{
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * Deletes an array value using "dot notation".
	 *
	 * @param  array  &$array Array you want to modify
	 * @param  string $path   Array path
	 * @return bool
	 */
	public static function delete(array &$array, string $path): bool
	{
		$segments = explode('.', $path);

		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if(!isset($array[$segment]) || !is_array($array[$segment]))
			{
				return false;
			}

			$array =& $array[$segment];
		}

		unset($array[array_shift($segments)]);

		return true;
	}

	/**
	 * Returns a random value from an array.
	 *
	 * @param  array $array Array you want to pick a random value from
	 * @return mixed
	 */
	public static function random(array $array)
	{
		return $array[array_rand($array)];
	}

	/**
	 * Returns TRUE if the array is associative and FALSE if not.
	 *
	 * @param  array $array Array to check
	 * @return bool
	 */
	public static function isAssoc(array $array): bool
	{
		return count(array_filter(array_keys($array), 'is_string')) === count($array);
	}

	/**
	 * Returns the values from a single column of the input array, identified by the key.
	 *
	 * @param  array  $array Array to pluck from
	 * @param  string $key   Array key
	 * @return array
	 */
	public static function pluck(array $array, string $key): array
	{
		$plucked = [];

		foreach($array as $value)
		{
			$plucked[] = is_object($value) ? $value->$key : $value[$key];
		}

		return $plucked;
	}

	/**
	 * Expands a wildcard key to an array of "dot notation" keys.
	 *
	 * @param  array  $array Array
	 * @param  string $key   Wildcard key
	 * @return array
	 */
	public static function expandKey(array $array, string $key): array
	{
		if(strpos($key, '*') === false)
		{
			throw new RuntimeException('The key must contain at least one wildcard character.');
		}

		$keys = (array) $key;

		start:

		$expanded = [];

		foreach($keys as $key)
		{
			list($first, $remaining) = array_map('trim', explode('*', $key, 2), ['.', '.']);

			if(empty($first))
			{
				$value = $array;
			}
			else
			{
				if(is_array($value = static::get($array, $first)) === false)
				{
					continue;
				}
			}

			foreach(array_keys($value) as $key)
			{
				$expanded[] = trim($first . '.' . $key . '.' . $remaining, '.');
			}
		}

		if(strpos($remaining, '*') !== false)
		{
			$keys = $expanded;

			goto start;
		}

		return $expanded;
	}
}
