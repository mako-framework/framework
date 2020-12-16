<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\syringe;

use mako\syringe\ClassInspector;
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
			'mako\tests\unit\syringe\E' => 'mako\tests\unit\syringe\E',
			'mako\tests\unit\syringe\D' => 'mako\tests\unit\syringe\D',
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
			'mako\tests\unit\syringe\IC' => 'mako\tests\unit\syringe\IC',
			'mako\tests\unit\syringe\ID' => 'mako\tests\unit\syringe\ID',
			'mako\tests\unit\syringe\IA' => 'mako\tests\unit\syringe\IA',
			'mako\tests\unit\syringe\IB' => 'mako\tests\unit\syringe\IB',
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
		$traitsD = ClassInspector::getTraits('mako\tests\unit\syringe\D');

		$traitsE = ClassInspector::getTraits('mako\tests\unit\syringe\E');

		$expectedTraits =
		[
			'mako\tests\unit\syringe\C' => 'mako\tests\unit\syringe\C',
			'mako\tests\unit\syringe\B' => 'mako\tests\unit\syringe\B',
			'mako\tests\unit\syringe\A' => 'mako\tests\unit\syringe\A',
		];

		$this->assertEquals($expectedTraits, $traitsD);

		$this->assertEquals($traitsD, $traitsE);
	}
}
