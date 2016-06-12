<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\syringe;

use PHPUnit_Framework_TestCase;

use mako\syringe\Container;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Bax
{
	use \mako\syringe\ContainerAwareTrait;

	public function getContainer()
	{
		return $this->container;
	}
}

class BaxChild extends Bax
{

}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class ContainerAwareTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testBasic()
	{
		$container = new Container;

		$bax = $container->get('mako\tests\unit\syringe\Bax');

		$this->assertInstanceOf('mako\syringe\Container', $bax->getContainer());
	}

	/**
	 *
	 */
	public function testContainerAwareChild()
	{
		$container = new Container;

		$bax = $container->get('mako\tests\unit\syringe\BaxChild');

		$this->assertInstanceOf('mako\syringe\Container', $bax->getContainer());
	}
}