<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use Throwable;

use mako\error\handlers\HandlerInterface;
use mako\error\handlers\web\HandlerHelperTrait;
use mako\http\Request;
use mako\http\Response;
use mako\http\exceptions\RequestException;
use mako\view\ViewFactory;

/**
 * Production handler.
 *
 * @author Frederic G. Østby
 */
class ProductionHandler implements HandlerInterface
{
	use HandlerHelperTrait;

	/**
	 * View factory instance.
	 *
	 * @var \mako\view\ViewFactory
	 */
	protected $view;

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
	 * Constructor.
	 *
	 * @param \mako\view\ViewFactory $view     View factory
	 * @param \mako\http\Request     $request  Request
	 * @param \mako\http\Response    $response Response
	 */
	public function __construct(ViewFactory $view, Request $request, Response $response)
	{
		$this->view = $view;

		$this->request = $request;

		$this->response = $response;

		$this->response->clear()->disableCaching()->disableCompression();

		$this->view->registerNamespace('mako-error', __DIR__ . '/views');
	}

	/**
	 * Return a JSON string.
	 *
	 * @param  Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsJson(Throwable $exception): string
	{
		$code = 500;

		$message = 'An error has occurred while processing your request.';

		if($exception instanceof RequestException)
		{
			$code = $exception->getCode();

			switch($code)
			{
				case 403:
					$message = 'You don\'t have permission to access the requested resource.';
					break;
				case 404:
					$message = 'The resource you requested could not be found. It may have been moved or deleted.';
					break;
				case 405:
					$message = 'The request method that was used is not supported by this resource.';
					break;
			}
		}

		return json_encode(['code' => $code, 'message' => $message]);
	}

	/**
	 * Returns a rendered error view.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsRenderedView(Throwable $exception): string
	{
		$view = 'error';

		if($exception instanceof RequestException)
		{
			$code = $exception->getCode();

			if($this->view->exists('mako-error::' . $code))
			{
				$view = $code;
			}
		}

		return $this->view->render('mako-error::' . $view);
	}

	/**
	 * Returns a response body.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getBody(Throwable $exception): string
	{
		if($this->returnAsJson())
		{
			$this->response->type('application/json');

			return $this->getExceptionAsJson($exception);
		}

		$this->response->type('text/html');

		return $this->getExceptionAsRenderedView($exception);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Throwable $exception)
	{
		$this->response
		->body($this->getBody($exception))
		->status($this->getStatusCode($exception))
		->send();

		return false;
	}
}
