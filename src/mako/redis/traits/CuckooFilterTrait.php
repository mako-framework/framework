<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

/**
 * Redis cuckoo filter commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait CuckooFilterTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function cfAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.ADD'], $arguments);
	}

	public function cfAddNx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.ADDNX'], $arguments);
	}

	public function cfCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.COUNT'], $arguments);
	}

	public function cfDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.DEL'], $arguments);
	}

	public function cfExists(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.EXISTS'], $arguments);
	}

	public function cfInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.INFO'], $arguments);
	}

	public function cfInsert(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.INSERT'], $arguments);
	}

	public function cfInsertNx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.INSERTNX'], $arguments);
	}

	public function cfLoadChunk(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.LOADCHUNK'], $arguments);
	}

	public function cfMExists(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.MEXISTS'], $arguments);
	}

	public function cfReserve(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.RESERVE'], $arguments);
	}

	public function cfScandump(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CF.SCANDUMP'], $arguments);
	}
}
