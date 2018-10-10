<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\view\renderers\Template;
use mako\view\ViewFactory;

/**
 * View factory service.
 *
 * @author Frederic G. Østby
 */
class ViewFactoryService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([ViewFactory::class, 'view'], function($container)
		{
			$app = $container->get('app');

			$applicationPath = $app->getPath();

			$fileSystem = $container->get('fileSystem');

			// Create factory instance

			$factory = new ViewFactory($fileSystem, $applicationPath . '/resources/views', $app->getCharset(), $container);

			// Register template renderer

			$factory->extend('.tpl.php', function() use ($applicationPath, $fileSystem)
			{
				return new Template($fileSystem, $applicationPath . '/storage/cache/views');
			});

			// Return factory instance

			return $factory;
		});
	}
}
