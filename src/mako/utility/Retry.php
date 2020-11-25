<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use Closure;
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
	 * The maximum number of attempts.
	 *
	 * @var int
	 */
	protected $maxAttempts;

	/**
	 * The time we want to want to wait between each attempt in microseconds.
	 *
	 * @var int
	 */
	protected $wait;

	/**
	 * Closure that decides whether or not we should retry.
	 *
	 * @var \Closure|null
	 */
	protected $decider;

	/**
	 * Constructor.
	 *
	 * @param callable      $callable    The callable
	 * @param int           $maxAttempts The maximum number of attempts
	 * @param int           $wait        The time we want to want to wait between each attempt in microseconds
	 * @param \Closure|null $decider     Closure that decides whether or not we should retry
	 */
	public function __construct(callable $callable, $maxAttempts = 5, $wait = 50000, ?Closure $decider = null)
	{
		$this->callable = $callable;

		$this->maxAttempts = $maxAttempts;

		$this->wait = $wait;

		$this->decider = $decider;
	}

	/**
	 * Sets the maximum number of attempts.
	 *
	 * @param  int                 $maxAttempts The maximum number of attempts
	 * @return \mako\utility\Retry
	 */
	public function times(int $maxAttempts): Retry
	{
		$this->maxAttempts = $maxAttempts;

		return $this;
	}

	/**
	 * Sets the time we want to want to wait between each attempt in microseconds.
	 *
	 * @param  int                 $wait The time we want to want to wait between each attempt in microseconds
	 * @return \mako\utility\Retry
	 */
	public function waitFor(int $wait): Retry
	{
		$this->wait = $wait;

		return $this;
	}

	/**
	 * Sets the decider that decides whether or not we should retry.
	 *
	 * @param  \Closure            $decider Closure that decides whether or not we should retry
	 * @return \mako\utility\Retry
	 */
	public function if(Closure $decider): Retry
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
			if(++$attempts < $this->maxAttempts && ($this->decider === null || ($this->decider)($e) === true))
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
