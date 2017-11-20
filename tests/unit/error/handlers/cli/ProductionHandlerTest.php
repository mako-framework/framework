<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\cli;

use Mockery;
use ErrorException;
use PHPUnit_Framework_TestCase;

use mako\cli\output\Output;
use mako\error\handlers\cli\ProductionHandler;

/**
 * @group unit
 */
class ProductionHandlerTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

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
