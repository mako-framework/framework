<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use Throwable;

use function usleep;

/**
 * Helper class that allows you to retry a callable a set number of times if it fails.
 */
class Retry
{
	/**
	 * The callable.
	 *
	 * @var callable
	 */
	protected $callable;

	/**
	 * Callable that decides whether or not we should retry.
	 *
	 * @var callable|null
	 */
	protected $decider;

	/**
	 * Constructor.
	 *
	 * @param callable      $callable        The callable
	 * @param int           $attempts        The number of attempts
	 * @param int           $wait            The time we want to want to wait between each attempt in microseconds
	 * @param bool          $exponentialWait Should the time between each attempt increase exponentially?
	 * @param callable|null $decider         Callable that decides whether or not we should retry
	 */
	public function __construct(
		callable $callable,
		protected int $attempts = 5,
		protected int $wait = 50000,
		protected bool $exponentialWait = false,
		?callable $decider = null
	)
	{
		$this->callable = $callable;

		$this->decider = $decider;
	}

	/**
	 * Sets the number of attempts.
	 *
	 * @param  int                 $attempts The maximum number of attempts
	 * @return \mako\utility\Retry
	 */
	public function setAttempts(int $attempts): Retry
	{
		$this->attempts = $attempts;

		return $this;
	}

	/**
	 * Sets the time we want to want to wait between each attempt in microseconds.
	 *
	 * @param  int                 $wait The time we want to want to wait between each attempt in microseconds
	 * @return \mako\utility\Retry
	 */
	public function setWait(int $wait): Retry
	{
		$this->wait = $wait;

		return $this;
	}

	/**
	 * Enables exponential waiting.
	 *
	 * @return \mako\utility\Retry
	 */
	public function exponentialWait(): Retry
	{
		$this->exponentialWait = true;

		return $this;
	}

	/**
	 * Sets the decider that decides whether or not we should retry.
	 *
	 * @param  callable            $decider Callable that decides whether or not we should retry
	 * @return \mako\utility\Retry
	 */
	public function setDecider(callable $decider): Retry
	{
		$this->decider = $decider;

		return $this;
	}

	/**
	 * Returns the number of microseconds we should wait for.
	 *
	 * @param  int $attempts Number of attempts
	 * @return int
	 */
	protected function calculateWait(int $attempts): int
	{
		if($this->exponentialWait)
		{
			return (2 ** ($attempts - 1)) * $this->wait;
		}

		return $this->wait;
	}

	/**
	 * Executes and returns the return value of the callable.
	 *
	 * @return mixed
	 */
	public function execute(): mixed
	{
		$attempts = 0;

		start:

		try
		{
			return ($this->callable)();
		}
		catch(Throwable $e)
		{
			if(++$attempts < $this->attempts && ($this->decider === null || ($this->decider)($e) === true))
			{
				usleep($this->calculateWait($attempts));

				goto start;
			}

			throw $e;
		}
	}

	/**
	 * Executes and returns the return value of the callable.
	 *
	 * @return mixed
	 */
	public function __invoke(): mixed
	{
		return $this->execute();
	}
}
