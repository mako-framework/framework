<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\cache;

use mako\cache\CacheManager;

/**
 * Command that clears the cache.
 *
 * @author Frederic G. Østby
 */
class Clear extends Command
{
	/**
	 * Make the command strict.
	 *
	 * @var bool
	 */
	protected $isStrict = true;

	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Clears the cache.',
		'options'     =>
		[
			'configuration' =>
			[
				'optional'    => true,
				'description' => 'Configuration name',
			],
		],
	];

	/**
	 * Executes the command.
	 *
	 * @param  \mako\cache\CacheManager $cache         Cache manager
	 * @param  string|null              $configuration Configuration name
	 * @return int|void
	 */
	public function execute(CacheManager $cache, ?string $configuration = null)
	{
		if($configuration !== null && $this->checkConfigurationExistence($configuration) === false)
		{
			return static::STATUS_ERROR;
		}

		$cache->instance($configuration)->clear();

		$this->write('Cleared the [ <yellow>' . ($configuration ?? 'default') . '</yellow> ] cache.');
	}
}
