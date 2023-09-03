<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\cache;

use mako\cache\CacheManager;
use mako\cli\input\arguments\Argument;

/**
 * Command that clears the cache.
 */
class Clear extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Clears the cache.';

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-c|--configuration', 'Configuration name', Argument::IS_OPTIONAL),
		];
	}

	/**
	 * Executes the command.
	 *
	 * @return int|void
	 */
	public function execute(CacheManager $cache, ?string $configuration = null)
	{
		if($configuration !== null && $this->checkConfigurationExistence($configuration) === false)
		{
			return static::STATUS_ERROR;
		}

		$cache->getInstance($configuration)->clear();

		$this->write('Cleared the [ <yellow>' . ($configuration ?? 'default') . '</yellow> ] cache.');
	}
}
