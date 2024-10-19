<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

/**
 * Redis bloom filter commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait BloomFilterTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function bfAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.ADD'], $arguments);
	}

	public function bfCard(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.CARD'], $arguments);
	}

	public function bfExists(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.EXISTS'], $arguments);
	}

	public function bfInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.INFO'], $arguments);
	}

	public function bfInsert(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.INSERT'], $arguments);
	}

	public function bfLoadChunk(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.LOADCHUNK'], $arguments);
	}

	public function bfMAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.MADD'], $arguments);
	}

	public function bfMExists(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.MEXISTS'], $arguments);
	}

	public function bfReserve(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.RESERVE'], $arguments);
	}

	public function bfScandump(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BF.SCANDUMP'], $arguments);
	}
}
