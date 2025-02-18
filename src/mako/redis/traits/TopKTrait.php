<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

use Deprecated;

/**
 * Redis top-k commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait TopKTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function topKAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOPK.ADD'], $arguments);
	}

	#[Deprecated(since: 'Redis.Bloom 2.4')]
	public function topKCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOPK.COUNT'], $arguments);
	}

	public function topKIncrBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOPK.INCRBY'], $arguments);
	}

	public function topKInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOPK.INFO'], $arguments);
	}

	public function topKList(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOPK.LIST'], $arguments);
	}

	public function topKQuery(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOPK.QUERY'], $arguments);
	}

	public function topKReserve(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOPK.RESERVE'], $arguments);
	}
}
