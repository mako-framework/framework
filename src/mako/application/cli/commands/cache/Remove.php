<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\cache;

use mako\application\cli\commands\cache\Command;
use mako\cache\CacheManager;

/**
 * Command that removes the chosen key from the cache.
 *
 * @author Frederic G. Østby
 */
class Remove extends Command
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
		'description' => 'Removes the chosen key from the cache.',
		'options'     =>
		[
			'key' =>
			[
				'optional'    => false,
				'description' => 'Cache key',
			],
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
	 * @access public
	 * @param \mako\cache\CacheManager $cache         Cache manager
	 * @param string                   $key           Cache Key
	 * @param null|string              $configuration Configuration name
	 */
	public function execute(CacheManager $cache, string $key, string $configuration = null)
	{
		if($configuration !== null && $this->checkConfigurationExistence($configuration) === false)
		{
			return Command::STATUS_ERROR;
		}

		$cache->instance($configuration)->remove($key);

		$this->write('Removed the [ <yellow>' . $key . '</yellow> ] key from the [ <yellow>'. ($configuration ?? 'default') . '</yellow> ] cache.');
	}
}
