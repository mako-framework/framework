<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\i18n\I18n;
use mako\utility\Humanizer;

/**
 * Humanizer service.
 *
 * @author Frederic G. Østby
 */
class HumanizerService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([Humanizer::class, 'humanizer'], static function($container)
		{
			return new Humanizer($container->has(I18n::class) ? $container->get(I18n::class) : null);
		});
	}
}
