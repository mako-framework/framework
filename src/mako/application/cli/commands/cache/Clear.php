<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\cache;

use mako\cache\CacheManager;
use mako\cli\input\arguments\Argument;
use mako\reactor\attributes\Arguments;
use mako\reactor\attributes\Command as CommandAttribute;

/**
 * Command that clears the cache.
 */
#[CommandAttribute('cache:clear', 'Clears the cache.')]
#[Arguments(
	new Argument('-c|--configuration', 'Configuration name', Argument::IS_OPTIONAL),
)]
class Clear extends Command
{
	/**
	 * Executes the command.
	 *
	 * @return int|void
	 */
	public function execute(CacheManager $cache, ?string $configuration = null)
	{
		if ($configuration !== null && $this->checkConfigurationExistence($configuration) === false) {
			return static::STATUS_ERROR;
		}

		$cache->getInstance($configuration)->clear();

		$this->write('Cleared the [ <yellow>' . ($configuration ?? 'default') . '</yellow> ] cache.');
	}
}
