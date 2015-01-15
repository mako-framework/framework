<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services\cli;

use mako\application\services\Service;
use mako\cli\output\Output;
use mako\cli\output\formatter\Formatter;
use mako\cli\output\writer\Error;
use mako\cli\output\writer\Standard;

/**
 * Output service.
 *
 * @author  Frederic G. Østby
 */

class OutputService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\cli\output\Output', 'output'], function($container)
		{
			return new Output(new Standard, new Error, new Formatter);
		});
	}
}