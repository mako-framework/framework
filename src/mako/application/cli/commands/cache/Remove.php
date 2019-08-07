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
 *
 * @author Frederic G. Østby
 */
class Remove extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Removes the chosen key from the cache.';

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-k|--key', 'Cache key'),
			new Argument('-c|--configuration', 'Configuration name', Argument::IS_OPTIONAL),
		];
	}

	/**
	 * Executes the command.
	 *
	 * @param  \mako\cache\CacheManager $cache         Cache manager
	 * @param  string                   $key           Cache Key
	 * @param  string|null              $configuration Configuration name
	 * @return int|void
	 */
	public function execute(CacheManager $cache, string $key, ?string $configuration = null)
	{
		if($configuration !== null && $this->checkConfigurationExistence($configuration) === false)
		{
			return static::STATUS_ERROR;
		}

		$cache->instance($configuration)->remove($key);

		$this->write("Removed the [ <yellow>{$key}</yellow> ] key from the [ <yellow>" . ($configuration ?? 'default') . '</yellow> ] cache.');
	}
}
