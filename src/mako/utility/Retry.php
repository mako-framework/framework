<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use mako\chrono\Sleeper;
use mako\chrono\SleeperInterface;
use Throwable;

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
	 * @var (callable(Throwable): bool)|null
	 */
	protected $decider;

	/**
	 * Constructor.
	 *
	 * @param (callable(Throwable): bool)|null $decider
	 */
	public function __construct(
		callable $callable,
		protected int $attempts = 5,
		protected int $wait = 50000,
		protected bool $exponentialWait = false,
		?callable $decider = null,
		protected SleeperInterface $sleeper = new Sleeper
	) {
		$this->callable = $callable;

		$this->decider = $decider;
	}

	/**
	 * Sets the number of attempts.
	 *
	 * @return $this
	 */
	public function setAttempts(int $attempts): Retry
	{
		$this->attempts = $attempts;

		return $this;
	}

	/**
	 * Sets the time we want to want to wait between each attempt in microseconds.
	 *
	 * @return $this
	 */
	public function setWait(int $wait): Retry
	{
		$this->wait = $wait;

		return $this;
	}

	/**
	 * Enables exponential waiting.
	 *
	 * @return $this
	 */
	public function exponentialWait(): Retry
	{
		$this->exponentialWait = true;

		return $this;
	}

	/**
	 * Sets the decider that decides whether or not we should retry.
	 *
	 * @param  (callable(Throwable): bool) $decider
	 * @return $this
	 */
	public function setDecider(callable $decider): Retry
	{
		$this->decider = $decider;

		return $this;
	}

	/**
	 * Returns the number of microseconds we should wait for.
	 */
	protected function calculateWait(int $attempts): int
	{
		if ($this->exponentialWait) {
			return (2 ** ($attempts - 1)) * $this->wait;
		}

		return $this->wait;
	}

	/**
	 * Executes and returns the return value of the callable.
	 */
	public function execute(): mixed
	{
		$attempts = 0;

		start:

		try {
			return ($this->callable)();
		}
		catch (Throwable $e) {
			if (++$attempts < $this->attempts && ($this->decider === null || ($this->decider)($e) === true)) {
				$this->sleeper->microSleep($this->calculateWait($attempts));

				goto start;
			}

			throw $e;
		}
	}

	/**
	 * Executes and returns the return value of the callable.
	 */
	public function __invoke(): mixed
	{
		return $this->execute();
	}
}
