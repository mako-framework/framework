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
	 * JSONP callback key name.
	 *
	 * @var string
	 */
	protected $callbackKey = 'callback';

	/**
	 * JSONP callback name.
	 *
	 * @var string
	 */
	protected $callbackName = 'callback';

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   mixed   $data      Data
	 * @param   int     $options   JSON encode options
	 */
	public function __construct($data, int $options = 0)
	{
		$this->data = $data;

		$this->options = $options;
	}

	/**
	 * Sets the callback key name.
	 *
	 * @access  public
	 * @param   string                              $name  Key name
	 * @return  \mako\http\response\builders\JSONP
	 */
	public function key(string $name): JSONP
	{
		$this->callbackKey = $name;

		return $this;
	}

	/**
	 * Sets the default callback name.
	 *
	 * @access  public
	 * @param   string                              $name  Callback name
	 * @return  \mako\http\response\builders\JSONP
	 */
	public function callback(string $name): JSONP
	{
		$this->callbackName = $name;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build(Request $request, Response $response)
	{
		$callback = $request->get($this->callbackKey, $this->callbackName);

		if(preg_match('/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u', $callback) === 0)
		{
			$callback = 'callback';
		}

		$response->type('text/javascript');

		$response->body($callback . '(' . json_encode($this->data, $this->options) . ');');
	}
}
