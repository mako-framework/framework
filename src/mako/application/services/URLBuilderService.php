<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;
use mako\http\routing\URLBuilder;

/**
 * URL builder service.
 *
 * @author  Frederic G. Østby
 */

class URLBuilderService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\http\routing\URLBuilder', 'urlBuilder'], function($container)
		{
			return new URLBuilder($container->get('request'), $container->get('routes'), $container->get('config')->get('application.clean_urls'));
		});
	}
}