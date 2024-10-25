<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\syringe;

use mako\syringe\Container;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Bax
{
	use \mako\syringe\traits\ContainerAwareTrait;

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

#[Group('unit')]
class ContainerAwareTest extends TestCase
{
	/**
	 *
	 */
	public function testBasic(): void
	{
		$container = new Container;

		$bax = $container->get(Bax::class);

		$this->assertInstanceOf(Container::class, $bax->getContainer());
	}

	/**
	 *
	 */
	public function testContainerAwareChild(): void
	{
		$container = new Container;

		$bax = $container->get(BaxChild::class);

		$this->assertInstanceOf(Container::class, $bax->getContainer());
	}
}
