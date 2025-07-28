<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http;

use mako\http\response\builders\ResponseBuilderInterface;
use mako\http\response\Cookies;
use mako\http\response\Headers;
use mako\http\response\senders\ResponseSenderInterface;
use mako\http\response\Status;
use mako\security\Signer;

use function hash;
use function header;
use function in_array;
use function is_int;
use function ob_end_flush;
use function ob_get_length;
use function ob_get_level;
use function ob_start;
use function setcookie;
use function setrawcookie;
use function str_replace;
use function stripos;

/**
 * HTTP response.
 */
class Response
{
	/**
	 * Default HTTP status.
	 */
	public const Status DEFAULT_STATUS = Status::OK;

	/**
	 * Response body.
	 */
	protected mixed $body = null;

	/**
	 * Response content type.
	 */
	protected string $contentType = 'text/html';

	/**
	 * HTTP Status.
	 */
	protected Status $status = self::DEFAULT_STATUS;

	/**
	 * Response headers.
	 */
	public protected(set) Headers $headers;

	/**
	 * Cookies.
	 */
	public protected(set) Cookies $cookies;

	/**
	 * Compress output?
	 */
	protected bool $outputCompression = false;

	/**
	 * Output compression handler.
	 */
	protected $outputCompressionHandler = 'ob_gzhandler';

	/**
	 * Enable response cache?
	 */
	protected bool $responseCache = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected string $charset = 'UTF-8',
		?Signer $signer = null
	) {
		$this->headers = new Headers;

		$this->cookies = new Cookies($signer);
	}

	/**
	 * Returns the request instance.
	 */
	public function getRequest(): Request
	{
		return $this->request;
	}

	/**
	 * Sets the response body.
	 *
	 * @return $this
	 */
	public function setBody(mixed $body): Response
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * Returns the response body.
	 */
	public function getBody(): mixed
	{
		return $this->body;
	}

	/**
	 * Clears the response body.
	 *
	 * @return $this
	 */
	public function clearBody(): Response
	{
		$this->body = null;

		return $this;
	}

	/**
	 * Sets the response content type.
	 *
	 * @return $this
	 */
	public function setType(string $contentType, ?string $charset = null): Response
	{
		$this->contentType = $contentType;

		if ($charset !== null) {
			$this->charset = $charset;
		}

		return $this;
	}

	/**
	 * Returns the response content type.
	 */
	public function getType(): string
	{
		return $this->contentType;
	}

	/**
	 * Sets the response character set.
	 *
	 * @return $this
	 */
	public function setCharset(string $charset): Response
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response character set.
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Sets the HTTP status.
	 *
	 * @return $this
	 */
	public function setStatus(int|Status $status): Response
	{
		$this->status = is_int($status) ? Status::from($status): $status;

		return $this;
	}

	/**
	 * Returns the HTTP status.
	 */
	public function getStatus(): Status
	{
		return $this->status;
	}

	/**
	 * Returns response header collection.
	 */
	public function getHeaders(): Headers
	{
		return $this->headers;
	}

	/**
	 * Returns response cookie collection.
	 */
	public function getCookies(): Cookies
	{
		return $this->cookies;
	}

	/**
	 * Clears the response body, cookies and headers.
	 *
	 * @return $this
	 */
	public function clear(): Response
	{
		$this->clearBody();

		$this->headers->clear();

		$this->cookies->clear();

		return $this;
	}

	/**
	 * Clears the response body in addition to cookies and headers that don't match the provided names or patterns.
	 *
	 * @return $this
	 */
	public function clearExcept(array $exceptions): Response
	{
		$this->clearBody();

		empty($exceptions['headers']) ? $this->headers->clear() : $this->headers->clearExcept($exceptions['headers']);

		empty($exceptions['cookies']) ? $this->cookies->clear() : $this->cookies->clearExcept($exceptions['cookies']);

		return $this;
	}

	/**
	 * Resets the response.
	 */
	public function reset(): Response
	{
		$this->status = static::DEFAULT_STATUS;

		return $this->clear();
	}

	/**
	 * Resets the response except for cookies and headers that match the provided names or patterns.
	 */
	public function resetExcept(array $exceptions): Response
	{
		$this->status = static::DEFAULT_STATUS;

		return $this->clearExcept($exceptions);
	}

	/**
	 * Sends response headers.
	 */
	public function sendHeaders(): void
	{
		// Send status header

		$protocol = $this->request->server->get('SERVER_PROTOCOL', 'HTTP/1.1');

		header("{$protocol} {$this->status->value} {$this->status->getMessage()}");

		// Send content type header

		$contentType = $this->contentType;

		if (stripos($contentType, 'text/') === 0 || in_array($contentType, ['application/json', 'application/xml', 'application/rss+xml', 'application/atom+xml'])) {
			$contentType .= "; charset={$this->charset}";
		}

		header("Content-Type: {$contentType}");

		// Send other headers

		foreach ($this->headers->all() as $name => $values) {
			foreach ($values as $value) {
				header("{$name}: {$value}", false);
			}
		}

		// Send cookie headers

		foreach ($this->cookies->all() as $cookie) {
			['raw' => $raw, 'name' => $name, 'value' => $value, 'options' => $options] = $cookie;

			if ($raw) {
				setrawcookie($name, $value, $options);

				continue;
			}

			setcookie($name, $value, $options);
		}
	}

	/**
	 * Is the response cacheable?
	 */
	public function isCacheable(): bool
	{
		if ($this->request->isCacheable() === false) {
			return false;
		}

		if (in_array($this->status->value, [200, 203, 204, 206, 300, 301, 404, 405, 410, 414, 501]) === false) {
			return false;
		}

		if ($this->headers->hasValue('Cache-Control', 'no-store', false) || $this->headers->hasValue('Cache-Control', 'private', false)) {
			return false;
		}

		return true;
	}

	/**
	 * Enables ETag response cache.
	 *
	 * @return $this
	 */
	public function enableCaching(): Response
	{
		$this->responseCache = true;

		return $this;
	}

	/**
	 * Disables ETag response cache.
	 *
	 * @return $this
	 */
	public function disableCaching(): Response
	{
		$this->responseCache = false;

		return $this;
	}

	/**
	 * Enables output compression.
	 *
	 * @return $this
	 */
	public function enableCompression(): Response
	{
		$this->outputCompression = true;

		return $this;
	}

	/**
	 * Disables output compression.
	 *
	 * @return $this
	 */
	public function disableCompression(): Response
	{
		$this->outputCompression = false;

		return $this;
	}

	/**
	 * Sets the output compression handler.
	 *
	 * @return $this
	 */
	public function setCompressionHandler(callable $handler): Response
	{
		$this->outputCompressionHandler = $handler;

		return $this;
	}

	/**
	 * Returns the output compression handler.
	 */
	public function getCompressionHandler(): callable
	{
		return $this->outputCompressionHandler;
	}

	/**
	 * Send output to browser.
	 */
	public function send(): void
	{
		// If the body is a response sender then we'll just pass it the
		// request and response instances and let it handle the rest itself

		if ($this->body instanceof ResponseSenderInterface) {
			$this->body->send($this->request, $this);

			return;
		}

		// If the body is a response builder then we'll let it build the response

		if ($this->body instanceof ResponseBuilderInterface) {
			$this->body->build($this->request, $this);
		}

		$sendBody = true;

		// Make sure that output buffering is enabled

		if (ob_get_level() === 0) {
			ob_start();
		}

		// Cast body to string in case it's an obect implementing __toString

		$this->body = (string) $this->body;

		// Check ETag if response cache is enabled

		if ($this->responseCache === true && $this->isCacheable()) {
			$hash = '"' . hash('xxh128', $this->body) . '"';

			$this->headers->add('ETag', $hash);

			if (str_replace('-gzip', '', $this->request->headers->get('If-None-Match', '')) === $hash) {
				$this->setStatus(Status::NOT_MODIFIED);

				$sendBody = false;
			}
		}

		if ($sendBody && in_array($this->status->value, [100, 101, 102, 103, 204, 304]) === false) {
			// Start compressed output buffering if output compression is enabled

			if ($this->outputCompression) {
				ob_start($this->outputCompressionHandler);
			}

			echo $this->body;

			// If output compression is enabled then we'll have to flush the compressed buffer
			// so that we can get the compressed content length when setting the content-length header

			if ($this->outputCompression) {
				ob_end_flush();
			}

			// Add the content-length header

			if (!$this->headers->has('Transfer-Encoding')) {
				$this->headers->add('Content-Length', (string) (int) ob_get_length());
			}
		}

		// Send the headers and flush the output buffer

		$this->sendHeaders();

		ob_end_flush();
	}
}
