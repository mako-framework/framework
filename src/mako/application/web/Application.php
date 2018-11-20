<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\web;

use mako\application\Application as BaseApplication;
use mako\http\Request;
use mako\http\routing\Dispatcher;
use mako\http\routing\Router;
use mako\i18n\I18n;

use function ob_start;

/**
 * Web application.
 *
 * @author Frederic G. Østby
 */
class Application extends BaseApplication
{
	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		ob_start();

		$request = $this->container->get(Request::class);

		// Override the application language?

		if(($language = $request->language()) !== null)
		{
			$this->setLanguage($language);

			if($this->container->has(I18n::class))
			{
				$this->container->get(I18n::class)->setLanguage($this->language);
			}
		}

		// Route the request

		$route = $this->container->get(Router::class)->route($request);

		// Dispatch the request and send the response

		$this->container->get(Dispatcher::class)->dispatch($route)->send();
	}
}
