<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services\cli;

use mako\application\services\Service;
use mako\cli\input\Input;
use mako\cli\input\reader\Reader;

/**
 * Input service.
 *
 * @author  Frederic G. Østby
 */

class InputService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\cli\input\Input', 'input'], function($container)
		{
			return new Input(new Reader);
		});
	}
}