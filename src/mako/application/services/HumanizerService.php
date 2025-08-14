<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\i18n\I18n;
use mako\utility\Humanizer;
use Override;

/**
 * Humanizer service.
 */
class HumanizerService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$this->container->registerSingleton(
			[Humanizer::class, 'humanizer'],
			static fn ($container) => new Humanizer($container->has(I18n::class) ? $container->get(I18n::class) : null)
		);
	}
}
