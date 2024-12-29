<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\classes;

use Attribute;
use mako\classes\ClassInspector;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

#[Attribute()]
class AA
{
}

interface IA
{
}
interface IB extends IA
{
}
interface IC extends IB
{
}
interface ID
{
}

trait A
{
}
trait B
{
	use A;
}
trait C
{
}

class D implements IC, ID
{
	use B; use C;
}
class E extends D
{
}
class F extends E
{
}
#[AA]
class G
{
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class ClassInspectorTest extends TestCase
{
	/**
	 *
	 */
	public function testGetAttributes(): void
	{
		$attributes = ClassInspector::getAttributes(G::class);

		$this->assertSame([AA::class], $attributes);
	}

	/**
	 *
	 */
	public function testGetParents(): void
	{
		$parents = ClassInspector::getParents(F::class);

		$expectedParents =
		[
			E::class => E::class,
			D::class => D::class,
		];

		$this->assertSame($expectedParents, $parents);
	}

	/**
	 *
	 */
	public function testGetInterfaces(): void
	{
		$interfaces = ClassInspector::getInterfaces(F::class);

		$exptectedInterfaces =
		[
			IC::class => IC::class,
			ID::class => ID::class,
			IA::class => IA::class,
			IB::class => IB::class,
		];

		// We are sorting the arrays since the order doesn't matter and it changed between PHP 7.3 and 7.4

		sort($interfaces);
		sort($exptectedInterfaces);

		$this->assertSame($exptectedInterfaces, $interfaces);
	}

	/**
	 *
	 */
	public function testGetTraits(): void
	{
		$traitsD = ClassInspector::getTraits(D::class);

		$traitsE = ClassInspector::getTraits(E::class);

		$expectedTraits =
		[
			C::class => C::class,
			B::class => B::class,
			A::class => A::class,
		];

		$this->assertEquals($expectedTraits, $traitsD);

		$this->assertEquals($traitsD, $traitsE);
	}
}
