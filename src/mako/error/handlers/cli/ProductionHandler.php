<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\cli;

use mako\cli\output\Output;
use mako\error\handlers\HandlerInterface;
use Override;
use Throwable;

/**
 * Production handler.
 */
class ProductionHandler implements HandlerInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function handle(Throwable $exception): mixed
	{
		$this->output->errorLn('<bg_red><white>An error has occurred while executing your command.</white></bg_red>' . PHP_EOL);

		return false;
	}
}
