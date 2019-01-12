<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use mako\error\handlers\HandlerInterface;
use mako\error\handlers\web\traits\HandlerHelperTrait;
use mako\http\exceptions\HttpException;
use mako\http\Request;
use mako\http\Response;
use mako\view\ViewFactory;
use Throwable;

use function json_encode;
use function simplexml_load_string;

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

		$this->view->registerNamespace('mako-error', __DIR__ . '/views');
	}

	/**
	 * Returns status code and message.
	 *
	 * @param  \Throwable $exception Exception
	 * @return array
	 */
	protected function getStatusCodeAndMessage(Throwable $exception): array
	{
		if($exception instanceof HttpException)
		{
			$message = $exception->getMessage();
		}

		if(empty($message))
		{
			$message = 'An error has occurred while processing your request.';
		}

		return ['code' => $this->getStatusCode($exception), 'message' => $message];
	}

	/**
	 * Return a JSON representation of the exception.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsJson(Throwable $exception): string
	{
		return json_encode(['error' => $this->getStatusCodeAndMessage($exception)]);
	}

	/**
	 * Return a XML representation of the exception.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsXml(Throwable $exception): string
	{
		$details = $this->getStatusCodeAndMessage($exception);

		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><error />");

		$xml->addChild('code', $details['code']);

		$xml->addChild('message', $details['message']);

		return $xml->asXML();
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

		if($exception instanceof HttpException)
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

		if($this->returnAsXml())
		{
			$this->response->type('application/xml');

			return $this->getExceptionAsXml($exception);
		}

		$this->response->type('text/html');

		return $this->getExceptionAsRenderedView($exception);
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
		->body($this->getBody($exception))
		->status($this->getStatusCode($exception)), $exception);

		return false;
	}
}
