<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use InvalidArgumentException;
use mako\chrono\SleeperInterface;
use mako\tests\TestCase;
use mako\utility\Retry;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Throwable;

#[Group('unit')]
class RetryTest extends TestCase
{
	protected function getCallable(): object
	{
		return new class {
			protected $attempts = 0;

			public function getAttempts(): int
			{
				return $this->attempts;
			}

			public function neverFails(): string
			{
				$this->attempts++;

				return __FUNCTION__;
			}

			public function failsFourTimes(): string
			{
				$this->attempts++;

				if ($this->attempts < 5) {
					throw new RuntimeException("Failed {$this->attempts} time(s).");
				}

				return __FUNCTION__;
			}
		};
	}

	/**
	 *
	 */
	public function testWithExecute(): void
	{
		$callable = $this->getCallable();

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->times(0);

		$this->assertSame('neverFails', (new Retry([$callable, 'neverFails'], sleeper: $sleeper))->execute());

		$this->assertSame(1, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testWithInvoke(): void
	{
		$callable = $this->getCallable();

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->times(0);

		$this->assertSame('neverFails', (new Retry([$callable, 'neverFails'], sleeper: $sleeper))());

		$this->assertSame(1, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testSuccessfulRetryWithConstructor(): void
	{
		$callable = $this->getCallable();

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->with(50000)->times(4);

		$this->assertSame('failsFourTimes', (new Retry([$callable, 'failsFourTimes'], 5, sleeper: $sleeper))());

		$this->assertSame(5, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testSuccessfulRetryWithConstructorAndExponentialWait(): void
	{
		$callable = $this->getCallable();

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->with(25000)->times(1);
		$sleeper->shouldReceive('microSleep')->with(50000)->times(1);
		$sleeper->shouldReceive('microSleep')->with(100000)->times(1);
		$sleeper->shouldReceive('microSleep')->with(200000)->times(1);

		$this->assertSame('failsFourTimes', (new Retry([$callable, 'failsFourTimes'], 5, 25000, exponentialWait: true, sleeper: $sleeper))());

		$this->assertSame(5, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testSuccessfulRetryWithMethods(): void
	{
		$callable = $this->getCallable();

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->times(4)->with(25000);

		$this->assertSame('failsFourTimes', (new Retry([$callable, 'failsFourTimes'], sleeper: $sleeper))->setAttempts(5)->setWait(25000)());

		$this->assertSame(5, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testSuccessfulRetryWithMethodsAndExponentialWait(): void
	{
		$callable = $this->getCallable();

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->with(25000)->times(1);
		$sleeper->shouldReceive('microSleep')->with(50000)->times(1);
		$sleeper->shouldReceive('microSleep')->with(100000)->times(1);
		$sleeper->shouldReceive('microSleep')->with(200000)->times(1);

		$this->assertSame('failsFourTimes', (new Retry([$callable, 'failsFourTimes'], sleeper: $sleeper))->setAttempts(5)->exponentialWait()->setWait(25000)());

		$this->assertSame(5, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testUnsuccessfulRetryWithConstructor(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessageIs('Failed 4 time(s)');

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->times(3);

		try {
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes'], 4, sleeper: $sleeper))();

			$this->assertSame(4, $callable->getAttempts());
		}
		catch (RuntimeException $e) {
			throw $e;
		}
	}

	/**
	 *
	 */
	public function testUnsuccessfulRetryWithMethods(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessageIs('Failed 4 time(s)');

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->times(3);

		try {
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes'], sleeper: $sleeper))->setAttempts(4)->SetWait(25000)();

			$this->assertSame(4, $callable->getAttempts());
		}
		catch (RuntimeException $e) {
			throw $e;
		}
	}

	/**
	 *
	 */
	public function testDeciderWithConstructor(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessageIs('Failed 1 time(s)');

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->times(0);

		try {
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes'], 4, decider: function (Throwable $e) {
				return $e instanceof InvalidArgumentException;
			}, sleeper: $sleeper))();

			$this->assertSame(1, $callable->getAttempts());
		}
		catch (RuntimeException $e) {
			throw $e;
		}
	}

	/**
	 *
	 */
	public function testDeciderWithMethod(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessageIs('Failed 1 time(s)');

		$sleeper = Mockery::mock(SleeperInterface::class);

		$sleeper->shouldReceive('microSleep')->times(0);

		try {
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes'], sleeper: $sleeper))->setDecider(function (Throwable $e) {
				return $e instanceof InvalidArgumentException;
			})();

			$this->assertSame(1, $callable->getAttempts());
		}
		catch (RuntimeException $e) {
			throw $e;
		}
	}
}
