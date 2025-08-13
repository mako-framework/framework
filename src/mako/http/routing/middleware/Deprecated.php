<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use mako\http\exceptions\GoneException;
use mako\http\Request;
use mako\http\Response;
use RuntimeException;

class Deprecated implements MiddlewareInterface
{
	/**
	 * RFC 7231 date format.
	 */
	protected const string RFC_7231_DATE = 'D, d M Y H:i:s \G\M\T';

	/**
	 * Deprecation date.
	 */
	protected ?DateTimeInterface $deprecationDate = null;

	/**
	 * Sunset date.
	 */
	protected ?DateTimeInterface $sunsetDate = null;

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
			$this->deprecationDate = $deprecationDate instanceof DateTimeInterface ? $deprecationDate : new DateTime($deprecationDate, new DateTimeZone('UTC'));
		}

		if ($sunsetDate !== null) {
			$this->sunsetDate = $sunsetDate instanceof DateTimeInterface ? $sunsetDate : new DateTime($sunsetDate, new DateTimeZone('UTC'));
		}

		if ($deprecationDate !== null && $sunsetDate !== null && $this->deprecationDate >= $this->sunsetDate) {
			throw new RuntimeException('The deprecation date must be earlier than the sunset date.');
		}
	}
	/**
	 * {@inheritDoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		if ($this->deprecationDate !== null) {
			$response->headers->add('Deprecation', "@{$this->deprecationDate->getTimestamp()}");
		}

		if ($this->sunsetDate !== null) {
			$sunsetDate = $this->sunsetDate;

			// Ensure that the sunset header is a UTC date

			if ($sunsetDate->getTimezone()->getName() !== 'UTC' && ($sunsetDate instanceof DateTime || $sunsetDate instanceof DateTimeImmutable)) {
				$sunsetDate = $sunsetDate->setTimezone(new DateTimeZone('UTC'));
			}

			$response->headers->add('Sunset', $sunsetDate->format(static::RFC_7231_DATE));

			// Throw a GoneException if automatic disabling is enabled and the current date is after the sunset date

			if ($this->disableAfterSunset && new DateTime(timezone: new DateTimeZone('UTC')) > $sunsetDate) {
				throw new GoneException;
			}
		}

		return $next($request, $response);
	}
}
