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
use Override;
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
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected Response $response,
		protected ?ViewFactory $view = null,
		protected array $keep = []
	) {
		if ($this->view !== null) {
			$this->view->registerNamespace('mako-error', __DIR__ . '/views');
		}

		$this->exceptionId = $this->generateExceptionId();
	}

	/**
	 * Returns status code and message.
	 */
	protected function getStatusCodeMessageAndMetadata(Throwable $exception): array
	{
		if ($exception instanceof HttpStatusException) {
			$message = $exception->getMessage();

			$metadata = $exception->getMetadata();
		}

		if (empty($message)) {
			$message = 'An error has occurred while processing your request.';
		}

		return [
			'code'         => $this->getHttpStatus($exception)->value,
			'message'      => $message,
			'exception_id' => $this->exceptionId,
			'metadata'     => $metadata ?? [],
		];
	}

	/**
	 * Return a JSON representation of the exception.
	 */
	protected function getExceptionAsJson(Throwable $exception): string
	{
		return json_encode(['error' => array_filter($this->getStatusCodeMessageAndMetadata($exception))]);
	}

	/**
	 * Return a XML representation of the exception.
	 */
	protected function getExceptionAsXml(Throwable $exception): string
	{
		['code' => $code, 'message' => $message, 'metadata' => $metadata] = $this->getStatusCodeMessageAndMetadata($exception);

		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><error />");

		$xml->addChild('code', $code);

		$xml->addChild('message', $message);

		$xml->addChild('exception_id', $this->exceptionId);

		if (!empty($metadata)) {
			$meta = $xml->addChild('metadata');

			($builder = static function ($xml, $metadata) use (&$builder) {
				foreach ($metadata as $key => $value) {
					if (is_array($value)) {
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
	 */
	protected function getExceptionAsHtml(Throwable $exception): string
	{
		$view = 'error';

		if ($exception instanceof HttpStatusException) {
			$code = $exception->getStatus()->value;

			if ($this->view->exists("mako-error::{$code}")) {
				$view = $code;
			}

			$metadata = $exception->getMetadata();
		}

		$this->view->assign('exception_id', $this->exceptionId);

		try {
			return $this->view->render('mako-error::' . $view, [
				'_exception_' => $exception,
				'_metadata_' => $metadata ?? [],
			]);
		}
		catch (Throwable $e) {
			return $this->view->clearAutoAssignVariables()->render('mako-error::' . $view, [
				'_exception_' => $exception,
				'_metadata_' => $metadata ?? [],
			]);
		}
	}

	/**
	 * Returns a plain text representation of the error.
	 */
	protected function getExceptionAsPlainText(Throwable $exception): string
	{
		['message' => $message] = $this->getStatusCodeMessageAndMetadata($exception);

		return $message;
	}

	/**
	 * Returns a response.
	 *
	 * @return array{type: string, body: string}
	 */
	protected function buildResponse(Throwable $exception): array
	{
		if (function_exists('json_encode') && $this->respondWithJson()) {
			return ['type' => 'application/json', 'body' => $this->getExceptionAsJson($exception)];
		}

		if (function_exists('simplexml_load_string') && $this->respondWithXml()) {
			return ['type' => 'application/xml', 'body' => $this->getExceptionAsXml($exception)];
		}

		if ($this->view !== null) {
			return ['type' => 'text/html', 'body' => $this->getExceptionAsHtml($exception)];
		}

		return ['type' => 'text/plain', 'body' => $this->getExceptionAsPlainText($exception)];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function handle(Throwable $exception): mixed
	{
		['type' => $type, 'body' => $body] = $this->buildResponse($exception);

		$this->sendResponse($this->response
		->clearExcept($this->keep)
		->disableCaching()
		->disableCompression()
		->setType($type)
		->setBody($body)
		->setStatus($this->getHttpStatus($exception)), $exception);

		return false;
	}
}
