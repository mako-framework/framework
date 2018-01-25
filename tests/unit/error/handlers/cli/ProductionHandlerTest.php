<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\cli;

use ErrorException;
use Mockery;

use mako\cli\output\Output;
use mako\error\handlers\cli\ProductionHandler;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ProductionHandlerTest extends TestCase
{
	/**
	 *
	 */
	public function testRegularError()
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('errorLn')->once()->with('<bg_red><white>An error has occurred while executing your command.</white></bg_red>' . PHP_EOL);

		//

		$handler = new ProductionHandler($output);

		$this->assertFalse($handler->handle(new ErrorException));
	}
}
