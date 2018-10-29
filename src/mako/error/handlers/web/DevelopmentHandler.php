<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use mako\application\Application;
use mako\error\handlers\HandlerInterface;
use mako\error\handlers\web\traits\HandlerHelperTrait;
use mako\http\Request;
use mako\http\Response;
use Throwable;
use Whoops\Handler\HandlerInterface as WhoopsHandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as Whoops;

use function current;

/**
 * Development handler.
 *
 * @author Frederic G. Østby
 */
class DevelopmentHandler implements HandlerInterface
{
	use HandlerHelperTrait;

	/**
	 * Whoops.
	 *
	 * @var \Whoops\Run
	 */
	protected $whoops;

	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * Response instance.
	 *
	 * @var \mako\http\Response
	 */
	protected $response;

	/**
	 * Application instance.
	 *
	 * @var \mako\application\Application
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param \Whoops\Run                   $whoops   Whoops
	 * @param \mako\http\Request            $request  Request
	 * @param \mako\http\Response           $response Response
	 * @param \mako\application\Application $app      Application
	 */
	public function __construct(Whoops $whoops, Request $request, Response $response, Application $app)
	{
		$this->whoops = $whoops;

		$this->request = $request;

		$this->response = $response;

		$this->app = $app;

		$this->configureWhoops();
	}

	/**
	 * Returns a Whoops handler.
	 *
	 * @return \Whoops\Handler\HandlerInterface
	 */
	protected function getWhoopsHandler(): WhoopsHandlerInterface
	{
		if($this->returnAsJson())
		{
			return new JsonResponseHandler;
		}

		if($this->returnAsXml())
		{
			return new XmlResponseHandler;
		}

		$handler = new PrettyPageHandler;

		$handler->handleUnconditionally(true);

		$handler->setApplicationPaths([$this->app->getPath()]);

		$handler->setPageTitle('Error');

		$blacklist = $this->app->getConfig()->get('application.error_handler.debug_blacklist');

		if(!empty($blacklist))
		{
			foreach($blacklist as $superglobal => $keys)
			{
				foreach($keys as $key)
				{
					$handler->blacklist($superglobal, $key);
				}
			}
		}

		return $handler;
	}

	/**
	 * Configure Whoops.
	 */
	protected function configureWhoops()
	{
		$this->whoops->pushHandler($this->getWhoopsHandler());

		$this->whoops->writeToOutput(false);

		$this->whoops->allowQuit(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Throwable $exception)
	{
		$this->sendResponse($this->response
		->clear()
		->disableCaching()
		->disableCompression()
		->body($this->whoops->handleException($exception))
		->status($this->getStatusCode($exception))
		->type(current($this->whoops->getHandlers())->contentType()), $exception);

		return false;
	}
}
