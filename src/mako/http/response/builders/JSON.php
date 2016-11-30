<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\response\builders;

use mako\http\Request;
use mako\http\Response;

/**
 * JSON builder.
 *
 * @author  Frederic G. Østby
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
	 * Constructor.
	 *
	 * @access  public
	 * @param   mixed  $data     Data
	 * @param   int    $options  JSON encode options
	 */
	public function __construct($data, int $options = 0)
	{
		$this->data = $data;

		$this->options = $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build(Request $request, Response $response)
	{
		$response->type('application/json');

		$response->body(json_encode($this->data, $this->options));
	}
}
