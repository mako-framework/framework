<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\utility\Humanizer;

/**
 * Humanizer service.
 *
 * @author  Frederic G. Østby
 */

class HumanizerService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\utility\Humanizer', 'humanizer'], function($container)
		{
			return new Humanizer($container->get('i18n'));
		});
	}
}