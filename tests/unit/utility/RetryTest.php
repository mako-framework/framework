<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use InvalidArgumentException;
use mako\tests\TestCase;
use mako\utility\Retry;
use RuntimeException;
use Throwable;

/**
 * @group unit
 */
class RetryTest extends TestCase
{
	protected function getCallable(): object
	{
		return new class
		{
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

				if($this->attempts < 5)
				{
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

		$this->assertSame('neverFails', (new Retry([$callable, 'neverFails']))->execute());

		$this->assertSame(1, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testWithInvoke(): void
	{
		$callable = $this->getCallable();

		$this->assertSame('neverFails', (new Retry([$callable, 'neverFails']))());

		$this->assertSame(1, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testSuccessfulRetryWithConstructor(): void
	{
		$callable = $this->getCallable();

		$this->assertSame('failsFourTimes', (new Retry([$callable, 'failsFourTimes'], 5, 0))());

		$this->assertSame(5, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testSuccessfulRetryWithMethods(): void
	{
		$callable = $this->getCallable();

		$this->assertSame('failsFourTimes', (new Retry([$callable, 'failsFourTimes']))->setAttempts(5)->setWait(0)());

		$this->assertSame(5, $callable->getAttempts());
	}

	/**
	 *
	 */
	public function testUnsuccessfulRetryWithConstructor(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Failed 4 time(s)');

		try
		{
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes'], 4, 0))();

			$this->assertSame(4, $callable->getAttempts());
		}
		catch(RuntimeException $e)
		{
			throw $e;
		}
	}

	/**
	 *
	 */
	public function testUnsuccessfulRetryWithMethods(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Failed 4 time(s)');

		try
		{
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes']))->setAttempts(4)->SetWait(0)();

			$this->assertSame(4, $callable->getAttempts());
		}
		catch(RuntimeException $e)
		{
			throw $e;
		}
	}

	/**
	 *
	 */
	public function testDeciderWithConstructor(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Failed 1 time(s)');

		try
		{
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes'], 4, 0, false, function(Throwable $e)
			{
				return $e instanceof InvalidArgumentException;
			}))();

			$this->assertSame(1, $callable->getAttempts());
		}
		catch(RuntimeException $e)
		{
			throw $e;
		}
	}

	/**
	 *
	 */
	public function testDeciderWithMethod(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Failed 1 time(s)');

		try
		{
			$callable = $this->getCallable();

			(new Retry([$callable, 'failsFourTimes']))->setDecider(function(Throwable $e)
			{
				return $e instanceof InvalidArgumentException;
			})();

			$this->assertSame(1, $callable->getAttempts());
		}
		catch(RuntimeException $e)
		{
			throw $e;
		}
	}
}
