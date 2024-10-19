<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

/**
 * Redis auto-suggest commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait AutoSuggestTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	public function ftSugAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SUGADD'], $arguments);
	}

	public function ftSugDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SUGDEL'], $arguments);
	}

	public function ftSugGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SUGGET'], $arguments);
	}

	public function ftSugLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FT.SUGLEN'], $arguments);
	}
}
