<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\cache;

use mako\cache\CacheManager;
use mako\cli\input\arguments\Argument;

/**
 * Command that removes the chosen key from the cache.
 */
class Remove extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Removes the chosen key from the cache.';

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-c|--configuration', 'Configuration name', Argument::IS_OPTIONAL),
			new Argument('-k|--key', 'Cache key'),
		];
	}

	/**
	 * Executes the command.
	 *
	 * @return int|void
	 */
	public function execute(CacheManager $cache, string $key, ?string $configuration = null)
	{
		if($configuration !== null && $this->checkConfigurationExistence($configuration) === false)
		{
			return static::STATUS_ERROR;
		}

		$cache->getInstance($configuration)->remove($key);

		$this->write("Removed the [ <yellow>{$key}</yellow> ] key from the [ <yellow>" . ($configuration ?? 'default') . '</yellow> ] cache.');
	}
}
