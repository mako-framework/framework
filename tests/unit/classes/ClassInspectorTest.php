<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\classes;

use mako\classes\ClassInspector;
use mako\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

interface IA {}
interface IB extends IA {}
interface IC extends IB {}
interface ID {}

trait A {}
trait B { use A; }
trait C {}

class D implements IC, ID { use B, C; }
class E extends D {}
class F extends E {}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class ClassInspectorTest extends TestCase
{
	/**
	 *
	 */
	public function testGetParents(): void
	{
		$parents = ClassInspector::getParents(F::class);

		$expectedParents =
		[
			'mako\tests\unit\classes\E' => 'mako\tests\unit\classes\E',
			'mako\tests\unit\classes\D' => 'mako\tests\unit\classes\D',
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
			'mako\tests\unit\classes\IC' => 'mako\tests\unit\classes\IC',
			'mako\tests\unit\classes\ID' => 'mako\tests\unit\classes\ID',
			'mako\tests\unit\classes\IA' => 'mako\tests\unit\classes\IA',
			'mako\tests\unit\classes\IB' => 'mako\tests\unit\classes\IB',
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
		$traitsD = ClassInspector::getTraits('mako\tests\unit\classes\D');

		$traitsE = ClassInspector::getTraits('mako\tests\unit\classes\E');

		$expectedTraits =
		[
			'mako\tests\unit\classes\C' => 'mako\tests\unit\classes\C',
			'mako\tests\unit\classes\B' => 'mako\tests\unit\classes\B',
			'mako\tests\unit\classes\A' => 'mako\tests\unit\classes\A',
		];

		$this->assertEquals($expectedTraits, $traitsD);

		$this->assertEquals($traitsD, $traitsE);
	}
}
