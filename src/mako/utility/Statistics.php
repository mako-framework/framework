<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use mako\utility\exceptions\StatisticsException;

use function array_count_values;
use function array_keys;
use function array_sum;
use function count;
use function max;
use function min;
use function sort;

/**
 * Class containing statistic helper methods.
 */
class Statistics
{
	/**
	 * Returns the mean of the numbers in the array.
	 */
	public static function mean(array $numbers): float|int
	{
		$count = count($numbers);

		if ($count === 0) {
			throw new StatisticsException('The array can not be empty.');
		}

		return array_sum($numbers) / $count;
	}

	/**
	 * Returns the median of the numbers in the array.
	 */
	public static function median(array $numbers): float|int
	{
		$count = count($numbers);

		if ($count === 0) {
			throw new StatisticsException('The array can not be empty.');
		}

		sort($numbers);

		$middle = (int) ($count / 2);

		if ($count % 2 === 0) {
			return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
		}

		return $numbers[$middle];
	}

	/**
	 * Returns the mode of the values in the array.
	 */
	public static function mode(array $values): mixed
	{
		if (empty($values)) {
			throw new StatisticsException('The array can not be empty.');
		}

		$frequency = array_count_values($values);

		return array_keys($frequency, max($frequency))[0];
	}

	/**
	 * Returns the midrange of the numbers in the array.
	 */
	public static function midrange(array $numbers): float|int
	{
		if (empty($numbers)) {
			throw new StatisticsException('The array can not be empty.');
		}

		return (min($numbers) + max($numbers)) / 2;
	}

	/**
	 * Returns the variance of the numbers in the array.
	 * This variant is used when the array represents a sample of the population (data-set).
	 */
	public static function sampleVariance(array $numbers): float|int
	{
		$count = count($numbers);

		if ($count === 0) {
			throw new StatisticsException('The array can not be empty.');
		}

		$mean = self::mean($numbers);

		$variance = 0;

		foreach ($numbers as $number) {
			$variance += ($number - $mean) ** 2;
		}

		return $variance / ($count - 1);
	}

	/**
	 * Returns the variance of the numbers in the array.
	 * This variant is used when the array represents the entire population (data-set).
	 */
	public static function populationVariance(array $numbers): float|int
	{
		$count = count($numbers);

		if ($count === 0) {
			throw new StatisticsException('The array can not be empty.');
		}

		$mean = self::mean($numbers);

		$variance = 0;

		foreach ($numbers as $number) {
			$variance += ($number - $mean) ** 2;
		}

		return $variance / $count;
	}
}
