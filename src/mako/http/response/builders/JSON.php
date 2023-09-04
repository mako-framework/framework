<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\builders;

use mako\http\Request;
use mako\http\Response;

use function json_encode;
use function preg_match;

/**
 * JSON builder.
 */
class JSON implements ResponseBuilderInterface
{
	/**
	 * Callback.
	 */
	protected null|string $callback = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $data,
		protected int $options = 0,
		protected ?int $statusCode = null,
		protected ?string $charset = null
	)
	{}

	/**
	 * Enables JSONP support.
	 */
	public function asJsonpWith(string $callback): JSON
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Sets the response character set.
	 */
	public function setCharset(string $charset): JSON
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response character set.
	 */
	public function getCharset(): ?string
	{
		return $this->charset;
	}

	/**
	 * Sets the HTTP status code.
	 */
	public function setStatus(int $statusCode): JSON
	{
		$this->statusCode = $statusCode;

		return $this;
	}

	/**
	 * Returns the HTTP status code.
	 */
	public function getStatus(): ?int
	{
		return $this->statusCode;
	}

	/**
	 * Ensures a valid callback name.
	 */
	protected function normalizeCallback(string $callback): string
	{
		if(preg_match('/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u', $callback) !== 1)
		{
			return 'callback';
		}

		return $callback;
	}

	/**
	 * {@inheritDoc}
	 */
	public function build(Request $request, Response $response): void
	{
		$json = json_encode($this->data, $this->options);

		if($this->callback !== null && ($callback = $request->query->get($this->callback)) !== null)
		{
			$response->setType('text/javascript');

			$json = "/**/{$this->normalizeCallback($callback)}({$json});";
		}
		else
		{
			$response->setType('application/json');
		}

		if(!empty($this->statusCode))
		{
			$response->setStatus($this->statusCode);
		}

		if(!empty($this->charset))
		{
			$response->setCharset($this->charset);
		}

		$response->setBody($json);
	}
}
