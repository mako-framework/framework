<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use mako\tests\TestCase;
use mako\utility\exceptions\StatisticsException;
use mako\utility\Statistics;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class StatisticsTest extends TestCase
{
	/**
	 *
	 */
	public function testMean(): void
	{
		$this->assertSame(7, Statistics::mean([1, 3, 5, 7, 9, 11, 13]));
		$this->assertSame(6, Statistics::mean([1, 3, 5, 7, 9, 11]));
		$this->assertEqualsWithDelta(1.8666666666666665, Statistics::mean([-11, 5.5, -3.4, 7.1, -9, 22]), 0.000000000000001);
	}

	/**
	 *
	 */
	public function testMeanWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::mean([]);
	}

	/**
	 *
	 */
	public function testMedian(): void
	{
		$this->assertSame(7, Statistics::median([1, 3, 5, 7, 9, 11, 13]));
		$this->assertSame(6, Statistics::median([1, 3, 5, 7, 9, 11]));
		$this->assertSame(1.05, Statistics::median([-11, 5.5, -3.4, 7.1, -9, 22]));
	}

	/**
	 *
	 */
	public function testMedianWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::median([]);
	}

	/**
	 *
	 */
	public function testMode(): void
	{
		$this->assertSame(3, Statistics::mode([1, 3, 3, 3, 5, 7, 7, 9]));
		$this->assertSame(1, Statistics::mode([1, 1, -3, 3, 7, -9]));
		$this->assertSame('red', Statistics::mode(['red', 'green', 'blue', 'red']));
	}

	/**
	 *
	 */
	public function testModeWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::mode([]);
	}

	/**
	 *
	 */
	public function testMultimode(): void
	{
		$this->assertSame([3], Statistics::multimode([1, 3, 3, 3, 5, 7, 7, 9]));
		$this->assertSame([1], Statistics::multimode([1, 1, -3, 3, 7, -9]));
		$this->assertSame(['red', 'green'], Statistics::multimode(['red', 'green', 'blue', 'red', 'green']));
		$this->assertSame([1, 3], Statistics::multimode([1, 1, 3, 3, 7, -9]));
		$this->assertSame([1, 3, 7], Statistics::multimode([1, 1, 3, 3, 7, 7, -9]));
	}

	/**
	 *
	 */
	public function testMultimodeWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::multimode([]);
	}

	/**
	 *
	 */
	public function testMidrange(): void
	{
		$this->assertSame(7, Statistics::midrange([1, 3, 5, 7, 9, 11, 13]));
		$this->assertSame(6, Statistics::midrange([1, 3, 5, 7, 9, 11]));
		$this->assertSame(5.5, Statistics::midrange([-11, 5.5, -3.4, 7.1, -9, 22]));
	}

	/**
	 *
	 */
	public function testMidrangeWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::midrange([]);
	}

	/**
	 *
	 */
	public function testSampleVariance(): void
	{
		$this->assertSame(14, Statistics::sampleVariance([1, 3, 5, 7, 9, 11]));
		$this->assertEqualsWithDelta(0.4796666666666667, Statistics::sampleVariance([2, 2.5, 1.25, 3.1, 1.75, 2.8]), 0.000000000000001);
		$this->assertEqualsWithDelta(70.80333333333334, Statistics::sampleVariance([-11, 5.5, -3.4, 7.1]), 0.0000000000001);
		$this->assertEqualsWithDelta(1736.9166666666667, Statistics::sampleVariance([1, 30, 50, 100]), 0.000000000001);
	}

	/**
	 *
	 */
	public function testSampleVarianceWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::sampleVariance([]);
	}

	/**
	 *
	 */
	public function testPopulationVariance(): void
	{
		$this->assertEqualsWithDelta(11.666666666666666, Statistics::populationVariance([1, 3, 5, 7, 9, 11]), 0.000000000000001);
		$this->assertEqualsWithDelta(0.3997222222222222, Statistics::populationVariance([2, 2.5, 1.25, 3.1, 1.75, 2.8]), 0.000000000000001);
		$this->assertEqualsWithDelta(53.1025, Statistics::populationVariance([-11, 5.5, -3.4, 7.1]), 0.0001);
		$this->assertEqualsWithDelta(1302.6875, Statistics::populationVariance([1, 30, 50, 100]), 0.0001);
	}

	/**
	 *
	 */
	public function testPopulationVarianceWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::populationVariance([]);
	}

	/**
	 *
	 */
	public function testsampleStandardDeviation(): void
	{
		$this->assertEqualsWithDelta(3.7416573867739413, Statistics::sampleStandardDeviation([1, 3, 5, 7, 9, 11]), 0.0000000000000001);
		$this->assertEqualsWithDelta(0.6925797186365384, Statistics::sampleStandardDeviation([2, 2.5, 1.25, 3.1, 1.75, 2.8]), 0.0000000000000001);
		$this->assertEqualsWithDelta(8.414471660973929, Statistics::sampleStandardDeviation([-11, 5.5, -3.4, 7.1]), 0.00000000000001);
		$this->assertEqualsWithDelta(41.67633221226008, Statistics::sampleStandardDeviation([1, 30, 50, 100]), 0.00000000000001);
	}

	/**
	 *
	 */
	public function testSampleStandardDeviationWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::sampleStandardDeviation([]);
	}

	/**
	 *
	 */
	public function testPopulationStandardDeviation(): void
	{
		$this->assertEqualsWithDelta(3.415650255319866, Statistics::populationStandardDeviation([1, 3, 5, 7, 9, 11]), 0.000000000000001);
		$this->assertEqualsWithDelta(0.6322358912796886, Statistics::populationStandardDeviation([2, 2.5, 1.25, 3.1, 1.75, 2.8]), 0.0000000000000001);
		$this->assertEqualsWithDelta(7.287146217827662, Statistics::populationStandardDeviation([-11, 5.5, -3.4, 7.1]), 0.000000000000001);
		$this->assertEqualsWithDelta(36.09276243237694, Statistics::populationStandardDeviation([1, 30, 50, 100]), 0.00000000000001);
	}

	/**
	 *
	 */
	public function testPopulationStandardDeviationWithEmptyArray(): void
	{
		$this->expectException(StatisticsException::class);

		Statistics::populationStandardDeviation([]);
	}
}
