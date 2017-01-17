<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\builders;

use mako\http\Request;
use mako\http\Response;

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
	 * @var string
	 */
	protected $callback;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param mixed $data    Data
	 * @param int   $options JSON encode options
	 */
	public function __construct($data, int $options = 0)
	{
		$this->data = $data;

		$this->options = $options;
	}

	/**
	 * Enables JSONP support.
	 *
	 * @access public
	 * @param  string                            $callback Query string field
	 * @return \mako\http\response\builders\JSON
	 */
	public function asJsonpWith(string $callback): JSON
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Ensures a valid callback name.
	 *
	 * @access protected
	 * @param  string $callback Callback name
	 * @return string
	 */
	protected function normalizeCallback(string $callback): string
	{
		if(preg_match('/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u', $callback) === 0)
		{
			return 'callback';
		}

		return $callback;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build(Request $request, Response $response)
	{
		$json = json_encode($this->data, $this->options);

		if(!empty($this->callback) && ($callback = $request->query->get($this->callback)) !== null)
		{
			$response->type('text/javascript');

			$json = '/**/' . $this->normalizeCallback($callback) . '(' . $json . ');';
		}
		else
		{
			$response->type('application/json');
		}

		$response->body($json);
	}
}
