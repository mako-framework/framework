<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\cli;

use mako\cli\output\Output;
use mako\error\handlers\HandlerInterface;
use Throwable;

/**
 * Production handler.
 *
 * @author Frederic G. Østby
 */
class ProductionHandler implements HandlerInterface
{
	/**
	 * Output.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output Output
	 */
	public function __construct(Output $output)
	{
		$this->output = $output;
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(Throwable $exception)
	{
		$this->output->errorLn('<bg_red><white>An error has occurred while executing your command.</white></bg_red>' . PHP_EOL);

		return false;
	}
}
