<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use \mako\security\Signer;

/**
 * Signer service.
 *
 * @author  Frederic G. Østby
 */

class SignerService extends \mako\application\services\Service
{
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\security\Signer', 'signer'], function($container)
		{
			return new Signer($container->get('config')->get('application.secret'));
		});
	}
}