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
 *
 * @author Frederic G. Østby
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
	protected $status;

	/**
	 * Character set.
	 *
	 * @var string|null
	 */
	protected $charset;

	/**
	 * Constructor.
	 *
	 * @param mixed       $data    Data
	 * @param int         $options JSON encode options
	 * @param int|null    $status  Status code
	 * @param string|null $charset Character set
	 */
	public function __construct($data, int $options = 0, ?int $status = null, ?string $charset = null)
	{
		$this->data = $data;

		$this->options = $options;

		$this->status = $status;

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
	 * Sets the status code.
	 *
	 * @param  int                               $status Status code
	 * @return \mako\http\response\builders\JSON
	 */
	public function setStatus(int $status): JSON
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Sets the status code.
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
	 * {@inheritdoc}
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

		if(!empty($this->status))
		{
			$response->setStatus($this->status);
		}

		if(!empty($this->charset))
		{
			$response->setCharset($this->charset);
		}

		$response->setBody($json);
	}
}
