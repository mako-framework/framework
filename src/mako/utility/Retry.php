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
 *
 * @author Frederic G. Østby
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
	 * The number of attempts.
	 *
	 * @var int
	 */
	protected $attempts;

	/**
	 * The time we want to want to wait between each attempt in microseconds.
	 *
	 * @var int
	 */
	protected $wait;

	/**
	 * Callable that decides whether or not we should retry.
	 *
	 * @var callable|null
	 */
	protected $decider;

	/**
	 * Constructor.
	 *
	 * @param callable      $callable The callable
	 * @param int           $attempts The number of attempts
	 * @param int           $wait     The time we want to want to wait between each attempt in microseconds
	 * @param callable|null $decider  Callable that decides whether or not we should retry
	 */
	public function __construct(callable $callable, $attempts = 5, $wait = 50000, ?callable $decider = null)
	{
		$this->callable = $callable;

		$this->attempts = $attempts;

		$this->wait = $wait;

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
	 * Executes and returns the return value of the callable.
	 *
	 * @return mixed
	 */
	public function execute()
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
				usleep($this->wait);

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
	public function __invoke()
	{
		return $this->execute();
	}
}
