<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\http\routing\URLBuilder;

/**
 * URL builder service.
 *
 * @author  Frederic G. Østby
 */

class URLBuilderService extends \mako\core\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\http\routing\URLBuilder', 'urlbuilder'], function($container)
		{
			return new URLBuilder($container->get('request'), $container->get('routes'), $container->get('config')->get('application.clean_urls'));
		});
	}
}