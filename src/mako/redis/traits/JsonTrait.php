<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

/**
 * Redis JSON commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait JsonTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function jsonArrAppend(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.ARRAPPEND'], $arguments);
	}

	public function jsonArrIndex(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.ARRINDEX'], $arguments);
	}

	public function jsonArrInsert(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.ARRINSERT'], $arguments);
	}

	public function jsonArrLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.ARRLEN'], $arguments);
	}

	public function jsonArrPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.ARRPOP'], $arguments);
	}

	public function jsonArrTrim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.ARRTRIM'], $arguments);
	}

	public function jsonClear(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.CLEAR'], $arguments);
	}

	public function jsonDebug(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.DEBUG'], $arguments);
	}

	public function jsonDebugMemory(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.DEBUG MEMORY'], $arguments);
	}

	public function jsonDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.DEL'], $arguments);
	}

	public function jsonForget(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.FORGET'], $arguments);
	}

	public function jsonGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.GET'], $arguments);
	}

	public function jsonMerge(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.MERGE'], $arguments);
	}

	public function jsonMGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.MGET'], $arguments);
	}

	public function jsonMset(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.MSET'], $arguments);
	}

	public function jsonNumIncrBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.NUMINCRBY'], $arguments);
	}

	public function jsonNumMultBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.NUMMULTBY'], $arguments);
	}

	public function jsonObjKeys(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.OBJKEYS'], $arguments);
	}

	public function jsonObjLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.OBJLEN'], $arguments);
	}

	public function jsonResp(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.RESP'], $arguments);
	}

	public function jsonSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.SET'], $arguments);
	}

	public function jsonStrAppend(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.STRAPPEND'], $arguments);
	}

	public function jsonStrLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.STRLEN'], $arguments);
	}

	public function jsonToggle(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.TOGGLE'], $arguments);
	}

	public function jsonType(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['JSON.TYPE'], $arguments);
	}
}
