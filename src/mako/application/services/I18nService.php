<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\file\FileSystem;
use mako\i18n\I18n;
use mako\i18n\loaders\Loader;
use Override;

/**
 * I18n service.
 */
class I18nService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$app = $this->app;

		// Register the I18n class

		$this->container->registerSingleton(
			[I18n::class, 'i18n'],
			static fn ($container) => new I18n(
				new Loader($container->get(FileSystem::class), "{$app->getPath()}/resources/i18n"),
				$app->getLanguage()
			)
		);
	}
}
