<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\file\FileSystem;
use mako\view\renderers\Template;
use mako\view\ViewFactory;

/**
 * View factory service.
 */
class ViewFactoryService extends Service
{
	/**
	 * Returns the storage path.
	 */
	protected function getStoragePath(): string
	{
		return "{$this->app->getStoragePath()}/cache/views";
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([ViewFactory::class, 'view'], function ($container)
		{
			$fileSystem = $container->get(FileSystem::class);

			// Create factory instance

			$factory = new ViewFactory($fileSystem, "{$this->app->getPath()}/resources/views", $this->app->getCharset(), $container);

			// Register template renderer

			$factory->extend('.tpl.php', function () use ($fileSystem)
			{
				return new Template($fileSystem, $this->getStoragePath());
			});

			// Return factory instance

			return $factory;
		});
	}
}
