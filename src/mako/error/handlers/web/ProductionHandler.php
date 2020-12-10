<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use mako\error\handlers\HandlerInterface;
use mako\http\exceptions\HttpException;
use mako\http\Request;
use mako\http\Response;
use mako\view\ViewFactory;
use Throwable;

use function function_exists;
use function json_encode;
use function simplexml_load_string;

/**
 * Production handler.
 */
class ProductionHandler extends Handler implements HandlerInterface
{
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
	 * View factory instance.
	 *
	 * @var \mako\view\ViewFactory|null
	 */
	protected $view;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request          $request  Request
	 * @param \mako\http\Response         $response Response
	 * @param \mako\view\ViewFactory|null $view     View factory
	 */
	public function __construct(Request $request, Response $response, ?ViewFactory $view = null)
	{
		$this->request = $request;

		$this->response = $response;

		if($view !== null)
		{
			$this->view = $view;

			$this->view->registerNamespace('mako-error', __DIR__ . '/views');
		}
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
		['code' => $code, 'message' => $message] = $this->getStatusCodeAndMessage($exception);

		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><error />");

		$xml->addChild('code', $code);

		$xml->addChild('message', $message);

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

			if($this->view->exists("mako-error::{$code}"))
			{
				$view = $code;
			}
		}

		try
		{
			return $this->view->render('mako-error::' . $view);
		}
		catch(Throwable $e)
		{
			return $this->view->clearAutoAssignVariables()->render('mako-error::' . $view);
		}
	}

	/**
	 * Returns a plain text representation of the error.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsPlainText(Throwable $exception): string
	{
		['message' => $message] = $this->getStatusCodeAndMessage($exception);

		return $message;
	}

	/**
	 * Returns a response body.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getBody(Throwable $exception): string
	{
		if(function_exists('json_encode') && $this->respondWithJson())
		{
			$this->response->setType('application/json');

			return $this->getExceptionAsJson($exception);
		}

		if(function_exists('simplexml_load_string') && $this->respondWithXml())
		{
			$this->response->setType('application/xml');

			return $this->getExceptionAsXml($exception);
		}

		if($this->view !== null)
		{
			$this->response->setType('text/html');

			return $this->getExceptionAsRenderedView($exception);
		}

		$this->response->setType('text/plain');

		return $this->getExceptionAsPlainText($exception);
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
		->setBody($this->getBody($exception))
		->setStatus($this->getStatusCode($exception)), $exception);

		return false;
	}
}
