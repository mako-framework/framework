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
 */
class ProductionHandler implements HandlerInterface
{
	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output Output
	 */
	public function __construct(
		protected Output $output
	)
	{}

	/**
	 * {@inheritDoc}
	 */
	public function handle(Throwable $exception): mixed
	{
		$this->output->errorLn('<bg_red><white>An error has occurred while executing your command.</white></bg_red>' . PHP_EOL);

		return false;
	}
}
