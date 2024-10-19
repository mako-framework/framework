<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

/**
 * Redis t-digest commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait TDigestTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function tDigestAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.ADD'], $arguments);
	}

	public function tDigestByRank(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.BYRANK'], $arguments);
	}

	public function tDigestByRevRank(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.BYREVRANK'], $arguments);
	}

	public function tDigestCDF(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.CDF'], $arguments);
	}

	public function tDigestCreate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.CREATE'], $arguments);
	}

	public function tDigestInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.INFO'], $arguments);
	}

	public function tDigestMax(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.MAX'], $arguments);
	}

	public function tDigestMerge(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.MERGE'], $arguments);
	}

	public function tDigestMin(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.MIN'], $arguments);
	}

	public function tDigestQuantile(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.QUANTILE'], $arguments);
	}

	public function tDigestRand(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.RAND'], $arguments);
	}

	public function tDigestReset(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.RESET'], $arguments);
	}

	public function tDigestRevRank(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.REVRANK'], $arguments);
	}

	public function tDigestTrimmedMean(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TDIGEST.TRIMMED_MEAN'], $arguments);
	}
}
