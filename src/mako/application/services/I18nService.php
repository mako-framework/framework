<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use \mako\i18n\I18n;

/**
 * I18n service.
 *
 * @author  Frederic G. Østby
 */

class I18nService extends \mako\application\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\i18n\I18n', 'i18n'], function($container)
		{
			$app = $container->get('app');

			return new I18n($container->get('fileSystem'), $app->getApplicationPath(), $app->getLanguage());
		});
	}
}