<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use mako\error\handlers\HandlerInterface;
use mako\http\exceptions\HttpStatusException;
use mako\http\Request;
use mako\http\Response;
use mako\view\ViewFactory;
use Throwable;

use function array_filter;
use function function_exists;
use function is_array;
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
	 * Cookies and headers to keep.
	 *
	 * @var array
	 */
	protected $keep;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request          $request  Request
	 * @param \mako\http\Response         $response Response
	 * @param \mako\view\ViewFactory|null $view     View factory
	 * @param array                       $keep     Cookies and headers to keep
	 */
	public function __construct(Request $request, Response $response, ?ViewFactory $view = null, array $keep = [])
	{
		$this->request = $request;

		$this->response = $response;

		if($view !== null)
		{
			$this->view = $view;

			$this->view->registerNamespace('mako-error', __DIR__ . '/views');
		}

		$this->keep = $keep;
	}

	/**
	 * Returns status code and message.
	 *
	 * @param  \Throwable $exception Exception
	 * @return array
	 */
	protected function getStatusCodeMessageAndMetadata(Throwable $exception): array
	{
		if($exception instanceof HttpStatusException)
		{
			$message = $exception->getMessage();

			$metadata = $exception->getMetadata();
		}

		if(empty($message))
		{
			$message = 'An error has occurred while processing your request.';
		}

		return ['code' => $this->getStatusCode($exception), 'message' => $message, 'metadata' => $metadata ?? []];
	}

	/**
	 * Return a JSON representation of the exception.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsJson(Throwable $exception): string
	{
		return json_encode(['error' => array_filter($this->getStatusCodeMessageAndMetadata($exception))]);
	}

	/**
	 * Return a XML representation of the exception.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsXml(Throwable $exception): string
	{
		['code' => $code, 'message' => $message, 'metadata' => $metadata] = $this->getStatusCodeMessageAndMetadata($exception);

		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><error />");

		$xml->addChild('code', $code);

		$xml->addChild('message', $message);

		if(!empty($metadata))
		{
			$meta = $xml->addChild('metadata');

			($builder = function ($xml, $metadata) use (&$builder)
			{
				foreach($metadata as $key => $value)
				{
					if(is_array($value))
					{
						$child = $xml->addChild($key);

						return $builder($child, $value);
					}

					$xml->addChild($key, $value);
				}
			})($meta, $metadata);
		}

		return $xml->asXML();
	}

	/**
	 * Returns a rendered error view.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsHtml(Throwable $exception): string
	{
		$view = 'error';

		if($exception instanceof HttpStatusException)
		{
			$code = $exception->getCode();

			if($this->view->exists("mako-error::{$code}"))
			{
				$view = $code;
			}

			$metadata = $exception->getMetadata();
		}

		try
		{
			return $this->view->render('mako-error::' . $view, ['_metadata_' => $metadata ?? []]);
		}
		catch(Throwable $e)
		{
			return $this->view->clearAutoAssignVariables()->render('mako-error::' . $view, ['_metadata_' => $metadata ?? []]);
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
		['message' => $message] = $this->getStatusCodeMessageAndMetadata($exception);

		return $message;
	}

	/**
	 * Returns a response.
	 *
	 * @param  \Throwable $exception Exception
	 * @return array
	 */
	protected function buildResponse(Throwable $exception): array
	{
		if(function_exists('json_encode') && $this->respondWithJson())
		{
			return ['type' => 'application/json', 'body' => $this->getExceptionAsJson($exception)];
		}

		if(function_exists('simplexml_load_string') && $this->respondWithXml())
		{
			return ['type' => 'application/xml', 'body' => $this->getExceptionAsXml($exception)];
		}

		if($this->view !== null)
		{
			return ['type' => 'text/html', 'body' => $this->getExceptionAsHtml($exception)];
		}

		return ['type' => 'text/plain', 'body' => $this->getExceptionAsPlainText($exception)];
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(Throwable $exception)
	{
		['type' => $type, 'body' => $body] = $this->buildResponse($exception);

		$this->sendResponse($this->response
		->clearExcept($this->keep)
		->disableCaching()
		->disableCompression()
		->setType($type)
		->setBody($body)
		->setStatus($this->getStatusCode($exception)), $exception);

		return false;
	}
}
