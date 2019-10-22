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
use mako\validator\input\HttpInputInterface;
use mako\validator\ValidationException;
use mako\view\ViewFactory;

use function function_exists;
use function in_array;
use function simplexml_load_string;

/**
 * Input validation middleware.
 *
 * @author Frederic G. Østby
 */
class InputValidation implements MiddlewareInterface
{
	use ContentNegotiationTrait;

	/**
	 * Default HTTP status code for invalid requests.
	 *
	 * @var int
	 */
	protected $httpStatusCode = 400;

	/**
	 * Session flash key for errors.
	 *
	 * @var string
	 */
	protected $errorsFlashKey = 'mako.errors';

	/**
	 * Session flash key for old input.
	 *
	 * @var string
	 */
	protected $oldInputFlashKey = 'mako.old';

	/**
	 * Errors view variable name.
	 *
	 * @var string
	 */
	protected $errorsVariableName = '_errors_';

	/**
	 * Old input view variable name.
	 *
	 * @var string
	 */
	protected $oldInputVariableName = '_old_';

	/**
	 * Default error message.
	 *
	 * @var string
	 */
	protected $defaultErrorMessage = 'Invalid input.';

	/**
	 * Keys that will be removed from the old input.
	 *
	 * @var array
	 */
	protected $dontInclude = ['password', 'password_confirmation'];

	/**
	 * Request.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * Response.
	 *
	 * @var \mako\http\Response
	 */
	protected $response;

	/**
	 * URL builder.
	 *
	 * @var \mako\http\routing\URLBuilder
	 */
	protected $urlBuilder;

	/**
	 * Session.
	 *
	 * @var \mako\session\Session|null
	 */
	protected $session;

	/**
	 * View factory.
	 *
	 * @var \mako\view\ViewFactory|null
	 */
	protected $viewFactory;

	/**
	 * Input.
	 *
	 * @var \mako\validator\input\HttpInputInterface|null
	 */
	protected $input;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request            $request     Request
	 * @param \mako\http\Response           $response    Response
	 * @param \mako\http\routing\URLBuilder $urlBuilder  URL builder
	 * @param \mako\session\Session|null    $session     Session
	 * @param \mako\view\ViewFactory|null   $viewFactory View factory
	 */
	public function __construct(Request $request, Response $response, URLBuilder $urlBuilder, ?Session $session = null, ?ViewFactory $viewFactory = null)
	{
		$this->request = $request;

		$this->response = $response;

		$this->urlBuilder = $urlBuilder;

		$this->session = $session;

		$this->viewFactory = $viewFactory;
	}

	/**
	 * Set the input.
	 *
	 * @param \mako\validator\input\HttpInputInterface|null $input Input
	 */
	protected function setInput(?HttpInputInterface $input): void
	{
		$this->input = $input;
	}

	/**
	 * Should we redirect the client if possible?
	 *
	 * @return bool
	 */
	protected function shouldRedirect(): bool
	{
		if($this->session === null || in_array($this->request->getMethod(), ['GET', 'HEAD']))
		{
			return false;
		}

		return ($this->input !== null && $this->input->shouldRedirect()) && $this->respondWithJson() === false && $this->respondWithXml() === false;
	}

	/**
	 * Should the old input be included?
	 *
	 * @return bool
	 */
	protected function shouldIncludeOldInput(): bool
	{
		if($this->input === null)
		{
			return true;
		}

		return $this->input->shouldIncludeOldInput();
	}

	/**
	 * Returns the old input.
	 *
	 * @return array
	 */
	protected function getOldInput(): array
	{
		if($this->input === null)
		{
			$oldInput = $this->request->getData()->all();
		}
		else
		{
			$oldInput = $this->input->getOldInput();
		}

		return array_diff_key($oldInput, array_flip($this->dontInclude));
	}

	/**
	 * Returns the redirect URL.
	 *
	 * @return string
	 */
	protected function getRedirectUrl(): string
	{
		if($this->input === null)
		{
			return $this->urlBuilder->current();
		}

		return $this->input->getRedirectUrl();
	}

	/**
	 * Add errors to flash data and redirect.
	 *
	 * @param  \mako\http\Response                 $response  Response
	 * @param  \mako\validator\ValidationException $exception Validation exception
	 * @return \mako\http\Response
	 */
	protected function handleRedirect(Response $response, ValidationException $exception): Response
	{
		$this->session->putFlash($this->errorsFlashKey, $exception->getErrors());

		if($this->shouldIncludeOldInput())
		{
			$this->session->putFlash($this->oldInputFlashKey, $this->getOldInput());
		}

		$response->setBody(new Redirect($this->getRedirectUrl(), Redirect::SEE_OTHER));

		return $response;
	}

	/**
	 * Returns the error message.
	 *
	 * @return string
	 */
	protected function getErrorMessage(): string
	{
		if($this->input !== null)
		{
			$errorMessage = $this->input->getErrorMessage();
		}

		return $errorMessage ?? $this->defaultErrorMessage;
	}

	/**
	 * Builds XML based on the exception.
	 *
	 * @param  \mako\validator\ValidationException $exception Validation exception
	 * @param  string                              $charset   Character set
	 * @return string
	 */
	protected function buildXmlFromException(ValidationException $exception, string $charset): string
	{
		$xml = simplexml_load_string("<?xml version='1.0' encoding='{$charset}'?><error />");

		$xml->addChild('message', $this->getErrorMessage());

		$errors = $xml->addChild('errors');

		foreach($exception->getErrors() as $field => $error)
		{
			$errors->addChild($field, $error);
		}

		return $xml->asXML();
	}

	/**
	 * Output errors.
	 *
	 * @param  \mako\http\Response                 $response  Response
	 * @param  \mako\validator\ValidationException $exception Validation exception
	 * @return \mako\http\Response
	 */
	protected function handleOutput(Response $response, ValidationException $exception): Response
	{
		$response->setStatus($this->httpStatusCode);

		if($this->respondWithJson())
		{
			$response->setBody(new JSON
			([
				'message' => $this->getErrorMessage(),
				'errors'  => $exception->getErrors(),
			]));

			return $response;
		}

		if(function_exists('simplexml_load_string') && $this->respondWithXml())
		{
			$response->setType('application/xml');

			$response->setBody($this->buildXmlFromException($exception, $response->getCharset()));

			return $response;
		}

		throw new BadRequestException;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		if($this->session !== null && $this->viewFactory !== null)
		{
			$this->viewFactory->assign($this->errorsVariableName, $this->session->getFlash($this->errorsFlashKey));

			$this->viewFactory->assign($this->oldInputVariableName, (object) $this->session->getFlash($this->oldInputFlashKey));
		}

		try
		{
			return $next($request, $response);
		}
		catch(ValidationException $e)
		{
			$this->setInput($e->getInput());

			$response->clear();

			if($this->shouldRedirect())
			{
				return $this->handleRedirect($response, $e);
			}

			return $this->handleOutput($response, $e);
		}
	}
}
