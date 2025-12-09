<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

use Deprecated;

/**
 * Redis query engine commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait RedisQueryEngineTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function ftList(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT._LIST'], $arguments);
	}

	public function ftAggregate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.AGGREGATE'], $arguments);
	}

	public function ftAliasAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.ALIASADD'], $arguments);
	}

	public function ftAliasDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.ALIASDEL'], $arguments);
	}

	public function ftAliasUpdate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.ALIASUPDATE'], $arguments);
	}

	public function ftAlter(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.ALTER'], $arguments);
	}

	#[Deprecated('use the "configGet" method instead', 'Redis 8.0.0')]
	public function ftConfigGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.CONFIG', 'GET'], $arguments);
	}

	#[Deprecated('use the "configSet" method instead', 'Redis 8.0.0')]
	public function ftConfigSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.CONFIG', 'SET'], $arguments);
	}

	public function ftCreate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.CREATE'], $arguments);
	}

	public function ftCursorDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.CURSOR', 'DEL'], $arguments);
	}

	public function ftCursorRead(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.CURSOR', 'READ'], $arguments);
	}

	public function ftDictAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.DICTADD'], $arguments);
	}

	public function ftDictDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.DICTDEL'], $arguments);
	}

	public function ftDictDump(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.DICTDUMP'], $arguments);
	}

	public function ftDropIndex(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.DROPINDEX'], $arguments);
	}

	public function ftExplain(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.EXPLAIN'], $arguments);
	}

	public function ftExplainCli(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.EXPLAINCLI'], $arguments);
	}

	public function ftInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.INFO'], $arguments);
	}

	public function ftProfile(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.PROFILE'], $arguments);
	}

	public function ftSearch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SEARCH'], $arguments);
	}

	public function ftSpellCheck(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SPELLCHECK'], $arguments);
	}

	public function ftSynDump(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SYNDUMP'], $arguments);
	}

	public function ftSynUpdate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SYNUPDATE'], $arguments);
	}

	#[Deprecated(null, 'Redis 8.0.0')]
	public function ftTagVals(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.TAGVALS'], $arguments);
	}
}
