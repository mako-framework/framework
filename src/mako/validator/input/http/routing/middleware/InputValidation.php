<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\http\routing\middleware;

use Closure;
use mako\http\exceptions\BadRequestException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\senders\Redirect;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\http\routing\URLBuilder;
use mako\http\traits\ContentNegotiationTrait;
use mako\session\Session;
use mako\validator\exceptions\ValidationException;
use mako\validator\input\http\InputInterface;
use mako\view\ViewFactory;
use Override;

use function array_diff_key;
use function array_flip;
use function function_exists;
use function in_array;
use function simplexml_load_string;

/**
 * Input validation middleware.
 */
class InputValidation implements MiddlewareInterface
{
	use ContentNegotiationTrait;

	/**
	 * Default HTTP status code for invalid requests.
	 */
	protected int $httpStatusCode = 400;

	/**
	 * Headers and cookies to keep.
	 */
	protected array $keep = ['headers' => ['Access-Control-.*']];

	/**
	 * Session flash key for errors.
	 */
	protected string $errorsFlashKey = 'mako.errors';

	/**
	 * Session flash key for old input.
	 */
	protected string $oldInputFlashKey = 'mako.old';

	/**
	 * Errors view variable name.
	 */
	protected string $errorsVariableName = '_errors_';

	/**
	 * Old input view variable name.
	 */
	protected string $oldInputVariableName = '_old_';

	/**
	 * Default error message.
	 */
	protected string $defaultErrorMessage = 'Invalid input.';

	/**
	 * Keys that will be removed from the old input.
	 */
	protected array $dontInclude = ['password', 'password_confirmation'];

	/**
	 * Request.
	 */
	protected Request $request;

	/**
	 * Response.
	 */
	protected Response $response;

	/**
	 * Input.
	 */
	protected ?InputInterface $input = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected URLBuilder $urlBuilder,
		protected ?Session $session = null,
		protected ?ViewFactory $viewFactory = null
	) {
	}

	/**
	 * Set the input.
	 */
	protected function setInput(?InputInterface $input): void
	{
		$this->input = $input;
	}

	/**
	 * Should we redirect the client if possible?
	 */
	protected function shouldRedirect(): bool
	{
		if ($this->session === null || $this->viewFactory === null || in_array($this->request->getMethod(), ['GET', 'HEAD'])) {
			return false;
		}

		return ($this->input === null || $this->input->shouldRedirect()) && $this->respondWithJson() === false && $this->respondWithXml() === false;
	}

	/**
	 * Should the old input be included?
	 */
	protected function shouldIncludeOldInput(): bool
	{
		if ($this->input === null) {
			return true;
		}

		return $this->input->shouldIncludeOldInput();
	}

	/**
	 * Returns the old input.
	 */
	protected function getOldInput(): array
	{
		if ($this->input === null) {
			$oldInput = $this->request->getData()->all();
		}
		else {
			$oldInput = $this->input->getOldInput();
		}

		return array_diff_key($oldInput, array_flip($this->dontInclude));
	}

	/**
	 * Returns the redirect URL.
	 */
	protected function getRedirectUrl(): string
	{
		if ($this->input === null) {
			return $this->urlBuilder->current();
		}

		return $this->input->getRedirectUrl();
	}

	/**
	 * Add errors to flash data and redirect.
	 */
	protected function handleRedirect(ValidationException $exception): Response
	{
		$this->session->putFlash($this->errorsFlashKey, $exception->getErrors());

		if ($this->shouldIncludeOldInput()) {
			$this->session->putFlash($this->oldInputFlashKey, $this->getOldInput());
		}

		$this->response->setBody(new Redirect($this->getRedirectUrl(), Redirect::SEE_OTHER));

		return $this->response;
	}

	/**
	 * Returns the error message.
	 */
	protected function getErrorMessage(): string
	{
		if ($this->input !== null) {
			$errorMessage = $this->input->getErrorMessage();
		}

		return $errorMessage ?? $this->defaultErrorMessage;
	}

	/**
	 * Builds XML based on the exception.
	 */
	protected function buildXmlFromException(ValidationException $exception, string $charset): string
	{
		$xml = simplexml_load_string("<?xml version='1.0' encoding='{$charset}'?><error />");

		$xml->addChild('message', $this->getErrorMessage());

		$errors = $xml->addChild('errors');

		foreach ($exception->getErrors() as $field => $error) {
			$errors->addChild($field, $error);
		}

		return $xml->asXML();
	}

	/**
	 * Output errors.
	 */
	protected function handleOutput(ValidationException $exception): Response
	{
		$this->response->setStatus($this->httpStatusCode);

		if ($this->respondWithJson()) {
			$this->response->setBody(new JSON([
				'message' => $this->getErrorMessage(),
				'errors'  => $exception->getErrors(),
			]));

			return $this->response;
		}

		if (function_exists('simplexml_load_string') && $this->respondWithXml()) {
			$this->response->setType('application/xml');

			$this->response->setBody($this->buildXmlFromException($exception, $this->response->getCharset()));

			return $this->response;
		}

		throw new BadRequestException;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$this->request = $request;
		$this->response = $response;

		// Assign errors and old input to the view if the session and view factory are available

		if ($this->session !== null && $this->viewFactory !== null) {
			$this->viewFactory->assign($this->errorsVariableName, $this->session->getFlash($this->errorsFlashKey));
			$this->viewFactory->assign($this->oldInputVariableName, $this->session->getFlash($this->oldInputFlashKey));
		}

		// Execute the next middleware and handle any validation exceptions

		try {
			return $next($request, $response);
		}
		catch (ValidationException $e) {
			$this->setInput($e->getInput());

			$response->clearExcept($this->keep);

			if ($this->shouldRedirect()) {
				return $this->handleRedirect($e);
			}

			return $this->handleOutput($e);
		}
	}
}
