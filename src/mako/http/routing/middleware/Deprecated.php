<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use DateTimeInterface;
use DateTimeZone;
use mako\chrono\TimeImmutable;
use mako\http\exceptions\GoneException;
use mako\http\Request;
use mako\http\Response;
use Override;
use RuntimeException;

class Deprecated implements MiddlewareInterface
{
	/**
	 * Deprecation date.
	 */
	protected ?TimeImmutable $deprecationDate = null;

	/**
	 * Sunset date.
	 */
	protected ?TimeImmutable $sunsetDate = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		null|DateTimeInterface|string $deprecationDate = null,
		null|DateTimeInterface|string $sunsetDate = null,
		protected bool $disableAfterSunset = false
	) {
		if ($deprecationDate === null && $sunsetDate === null) {
			throw new RuntimeException('You must specify either a deprecation date or a sunset date or both.');
		}

		if ($deprecationDate !== null) {
			$this->deprecationDate = $this->createTimeImmutable($deprecationDate);
		}

		if ($sunsetDate !== null) {
			$this->sunsetDate = $this->createTimeImmutable($sunsetDate);
		}

		if ($deprecationDate !== null && $sunsetDate !== null && $this->deprecationDate >= $this->sunsetDate) {
			throw new RuntimeException('The deprecation date must be earlier than the sunset date.');
		}
	}

	/**
	 * Creates a TimeImmutable instance based on the provided input.
	 */
	protected function createTimeImmutable(DateTimeInterface|string $date): TimeImmutable
	{
		return $date instanceof DateTimeInterface
			? ($date instanceof TimeImmutable ? $date : TimeImmutable::createFromInterface($date))
			: new TimeImmutable($date, new DateTimeZone('UTC'));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		if ($this->deprecationDate !== null) {
			$response->headers->add('Deprecation', "@{$this->deprecationDate->getTimestamp()}");
		}

		if ($this->sunsetDate !== null) {
			$response->headers->add('Sunset', $this->sunsetDate->toRfc7231String());

			// Throw a GoneException if automatic disabling is enabled and the current date is after the sunset date

			if ($this->disableAfterSunset && TimeImmutable::now() > $this->sunsetDate) {
				throw new GoneException;
			}
		}

		return $next($request, $response);
	}
}
