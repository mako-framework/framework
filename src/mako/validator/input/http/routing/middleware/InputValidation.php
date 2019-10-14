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
	protected $defaultMessage = 'Invalid input.';

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
	 * Should we redirect the client?
	 *
	 * @param  \mako\http\Request                  $request   Request
	 * @param  \mako\validator\ValidationException $exception Validation exception
	 * @return bool
	 */
	protected function shouldRedirect(Request $request, ValidationException $exception): bool
	{
		if($this->session === null || in_array($request->getMethod(), ['GET', 'HEAD']))
		{
			return false;
		}

		return  $exception->getMeta('should_redirect') !== false && $this->respondWithJson() === false && $this->respondWithXml() === false;
	}

	/**
	 * Get the redirect URL.
	 *
	 * @param  \mako\validator\ValidationException $exception Validation exception
	 * @return string
	 */
	protected function getRedirectUrl(ValidationException $exception): string
	{
		return $exception->getMeta('redirect_url', $this->urlBuilder->current());
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

		$this->session->putFlash($this->oldInputFlashKey, $exception->getMeta('old_input'));

		$response->setBody(new Redirect($this->getRedirectUrl($exception), Redirect::SEE_OTHER));

		return $response;
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

		$xml->addChild('message', $exception->getMeta('message', $this->defaultMessage));

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
				'message' => $exception->getMeta('message', $this->defaultMessage),
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
			$response->clear();

			if($this->shouldRedirect($request, $e))
			{
				return $this->handleRedirect($response, $e);
			}

			return $this->handleOutput($response, $e);
		}
	}
}
