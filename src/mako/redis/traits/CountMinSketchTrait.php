<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

/**
 * Redis count-min sketch commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait CountMinSketchTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function cmsIncryBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CMS.INCRBY'], $arguments);
	}

	public function cmsInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CMS.INFO'], $arguments);
	}

	public function cmsInitByDim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CMS.INITBYDIM'], $arguments);
	}

	public function cmsInitByProb(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CMS.INITBYPROB'], $arguments);
	}

	public function cmsMerge(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CMS.MERGE'], $arguments);
	}

	public function cmsQuery(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CMS.QUERY'], $arguments);
	}
}
