<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use mako\utility\exceptions\ArrException;
use stdClass;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_rand;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function str_contains;
use function trim;

/**
 * Array helper.
 */
class Arr
{
	/**
	 * Sets an array value using "dot notation".
	 */
	public static function set(array &$array, string $path, mixed $value): void
	{
		$segments = explode('.', $path);

		while (count($segments) > 1) {
			$segment = array_shift($segments);

			if (!isset($array[$segment]) || !is_array($array[$segment])) {
				$array[$segment] = [];
			}

			$array =& $array[$segment];
		}

		$array[array_shift($segments)] = $value;
	}

	/**
	 * Appends an array value using "dot notation".
	 */
	public static function append(array &$array, string $path, mixed $value): void
	{
		$segments = explode('.', $path);

		while (count($segments) > 1) {
			$segment = array_shift($segments);

			if (!isset($array[$segment]) || !is_array($array[$segment])) {
				$array[$segment] = [];
			}

			$array =& $array[$segment];
		}

		$array[array_shift($segments)][] = $value;
	}

	/**
	 * Search for an array value using "dot notation". Returns TRUE if the array key exists and FALSE if not.
	 */
	public static function has(array $array, string $path): bool
	{
		$segments = explode('.', $path);

		foreach ($segments as $segment) {
			if (!is_array($array) || !array_key_exists($segment, $array)) {
				return false;
			}

			$array = $array[$segment];
		}

		return true;
	}

	/**
	 * Returns value from array using "dot notation".
	 */
	public static function get(array $array, string $path, mixed $default = null): mixed
	{
		$segments = explode('.', $path);

		foreach ($segments as $segment) {
			if (!is_array($array) || !array_key_exists($segment, $array)) {
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * Deletes an array value using "dot notation".
	 */
	public static function delete(array &$array, string $path): bool
	{
		$segments = explode('.', $path);

		while (count($segments) > 1) {
			$segment = array_shift($segments);

			if (!isset($array[$segment]) || !is_array($array[$segment])) {
				return false;
			}

			$array =& $array[$segment];
		}

		unset($array[array_shift($segments)]);

		return true;
	}

	/**
	 * Returns a random value from an array.
	 */
	public static function random(array $array): mixed
	{
		return $array[array_rand($array)];
	}

	/**
	 * Returns TRUE if the array is associative and FALSE if not.
	 */
	public static function isAssoc(array $array): bool
	{
		return count(array_filter(array_keys($array), is_string(...))) === count($array);
	}

	/**
	 * Returns the values from a single column of the input array, identified by the key.
	 */
	public static function pluck(array $array, string $key): array
	{
		$plucked = [];

		foreach ($array as $value) {
			$plucked[] = is_object($value) ? $value->{$key} : $value[$key];
		}

		return $plucked;
	}

	/**
	 * Expands a wildcard key to an array of "dot notation" keys.
	 */
	public static function expandKey(array $array, string $key): array
	{
		if (str_contains($key, '*') === false) {
			throw new ArrException('The key must contain at least one wildcard character.');
		}

		$keys = (array) $key;

		start:

		$expanded = [];

		foreach ($keys as $key) {
			[$first, $remaining] = array_map(trim(...), explode('*', $key, 2), ['.', '.']);

			if (empty($first)) {
				$value = $array;
			}
			elseif (is_array($value = static::get($array, $first)) === false) {
				continue;
			}

			foreach (array_keys($value) as $key) {
				$expanded[] = trim("{$first}.{$key}.{$remaining}", '.');
			}
		}

		if (str_contains($remaining, '*')) {
			$keys = $expanded;

			goto start;
		}

		return $expanded;
	}

	/**
	 * Converts arrays to objects.
	 */
	public static function toObject(array $array): array|object
	{
		$resultArray = [];

		$resultObject = new stdClass;

		$isNumeric = $isAssociative = false;

		foreach ($array as $key => $value) {
			if (!$isNumeric) {
				$isNumeric = is_int($key);
			}

			if (!$isAssociative) {
				$isAssociative = is_string($key);
			}

			if ($isNumeric && $isAssociative) {
				throw new ArrException('Unable to convert an array containing a mix of integer and string keys to an object.');
			}

			if ($isNumeric) {
				$resultArray[$key] = is_array($value) ? static::toObject($value) : $value;
			}
			else {
				$resultObject->{$key} = is_array($value) ? static::toObject($value) : $value;
			}
		}

		return $isNumeric ? $resultArray : $resultObject;
	}
}
