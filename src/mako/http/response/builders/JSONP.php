<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\response\builders;

use mako\http\Request;
use mako\http\Response;

/**
 * JSONP builder.
 *
 * @author  Frederic G. Østby
 */
class JSONP implements ResponseBuilderInterface
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
	 * JSONP callback name.
	 *
	 * @var string
	 */
	protected $callbackKey;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   mixed   $data      Data
	 * @param   int     $options   JSON encode options
	 * @param   string  $callback  JSONP callback name
	 */
	public function __construct($data, int $options = 0, string $callbackKey = 'callback')
	{
		$this->data = $data;

		$this->options = $options;

		$this->callbackKey = $callbackKey;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build(Request $request, Response $response)
	{
		$callback = $request->get($this->callbackKey, 'callback');

		if(preg_match('/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u', $callback) !== 1)
		{
			$callback = 'callback';
		}

		$response->type('text/javascript');

		$response->body($callback . '(' . json_encode($this->data, $this->options) . ');');
	}
}