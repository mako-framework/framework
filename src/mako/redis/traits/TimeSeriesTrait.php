<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

/**
 * Redis time series commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait TimeSeriesTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function tsAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.ADD'], $arguments);
	}

	public function tsAlter(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.ALTER'], $arguments);
	}

	public function tsCreate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.CREATE'], $arguments);
	}

	public function tsCreateRule(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.CREATERULE'], $arguments);
	}

	public function tsDecrBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.DECRBY'], $arguments);
	}

	public function tsDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.DEL'], $arguments);
	}

	public function tsDeleteRule(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.DELETERULE'], $arguments);
	}

	public function tsGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.GET'], $arguments);
	}

	public function tsIncBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.INCRBY'], $arguments);
	}

	public function tsInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.INFO'], $arguments);
	}

	public function tsMAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.MADD'], $arguments);
	}

	public function tsMGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.MGET'], $arguments);
	}

	public function tsMRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.MRANGE'], $arguments);
	}

	public function tsMRevRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.MREVRANGE'], $arguments);
	}

	public function tsQueryIndex(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.QUERYINDEX'], $arguments);
	}

	public function tsRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.RANGE'], $arguments);
	}

	public function tsRevRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TS.REVRANGE'], $arguments);
	}
}
