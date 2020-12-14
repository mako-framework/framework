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
	 * Data.
	 *
	 * @var mixed
	 */
	protected $data;

	/**
	 * JSON encode options.
	 *
	 * @var int
	 */
	protected $options;

	/**
	 * Callback.
	 *
	 * @var string|null
	 */
	protected $callback;

	/**
	 * Status code.
	 *
	 * @var int|null
	 */
	protected $statusCode;

	/**
	 * Character set.
	 *
	 * @var string|null
	 */
	protected $charset;

	/**
	 * Constructor.
	 *
	 * @param mixed       $data       Data
	 * @param int         $options    JSON encode options
	 * @param int|null    $statusCode Status code
	 * @param string|null $charset    Character set
	 */
	public function __construct($data, int $options = 0, ?int $statusCode = null, ?string $charset = null)
	{
		$this->data = $data;

		$this->options = $options;

		$this->statusCode = $statusCode;

		$this->charset = $charset;
	}

	/**
	 * Enables JSONP support.
	 *
	 * @param  string                            $callback Query string field
	 * @return \mako\http\response\builders\JSON
	 */
	public function asJsonpWith(string $callback): JSON
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Sets the response character set.
	 *
	 * @param  string                            $charset Character set
	 * @return \mako\http\response\builders\JSON
	 */
	public function setCharset(string $charset): JSON
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response character set.
	 *
	 * @return string|null
	 */
	public function getCharset(): ?string
	{
		return $this->charset;
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @param  int                               $statusCode Status code
	 * @return \mako\http\response\builders\JSON
	 */
	public function setStatus(int $statusCode): JSON
	{
		$this->statusCode = $statusCode;

		return $this;
	}

	/**
	 * Returns the HTTP status code.
	 *
	 * @return int|null
	 */
	public function getStatus(): ?int
	{
		return $this->statusCode;
	}

	/**
	 * Ensures a valid callback name.
	 *
	 * @param  string $callback Callback name
	 * @return string
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

		if(!empty($this->callback) && ($callback = $request->getQuery()->get($this->callback)) !== null)
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
