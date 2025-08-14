<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\builders;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\Status;
use Override;

use function is_int;
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
	protected ?string $callback = null;

	/**
	 * HTTP status code.
	 */
	protected ?Status $status = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $data,
		protected int $options = 0,
		null|int|Status $status = null,
		protected ?string $charset = null
	) {
		if (!empty($status)) {
			$this->setStatus($status);
		}
	}

	/**
	 * Enables JSONP support.
	 *
	 * @return $this
	 */
	public function asJsonpWith(string $callback): JSON
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Sets the response character set.
	 *
	 * @return $this
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
	 *
	 * @return $this
	 */
	public function setStatus(int|Status $status): JSON
	{
		$this->status = is_int($status) ? Status::from($status): $status;

		return $this;
	}

	/**
	 * Returns the HTTP status.
	 */
	public function getStatus(): ?Status
	{
		return $this->status;
	}

	/**
	 * Ensures a valid callback name.
	 */
	protected function normalizeCallback(string $callback): string
	{
		if (preg_match('/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u', $callback) !== 1) {
			return 'callback';
		}

		return $callback;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function build(Request $request, Response $response): void
	{
		$json = json_encode($this->data, $this->options);

		if ($this->callback !== null && ($callback = $request->query->get($this->callback)) !== null) {
			$response->setType('text/javascript');

			$json = "/**/{$this->normalizeCallback($callback)}({$json});";
		}
		else {
			$response->setType('application/json');
		}

		if (!empty($this->status)) {
			$response->setStatus($this->status);
		}

		if (!empty($this->charset)) {
			$response->setCharset($this->charset);
		}

		$response->setBody($json);
	}
}
