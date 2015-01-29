<?php

namespace mako\tests\unit\syringe;

use mako\syringe\ClassInspector;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

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

class D
{
	use C, B;
}

class E extends D
{

}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class ClassInspectorTest extends \PHPUnit_Framework_TestCase
{
	public function testGetClassTraits()
	{
		$traitsD = ClassInspector::getTraits('mako\tests\unit\syringe\D');

		$traitsE = ClassInspector::getTraits('mako\tests\unit\syringe\E');

		$expectedTraits =
		[
			'mako\tests\unit\syringe\C' => 'mako\tests\unit\syringe\C',
			'mako\tests\unit\syringe\B' => 'mako\tests\unit\syringe\B',
			'mako\tests\unit\syringe\A' => 'mako\tests\unit\syringe\A'
		];

		$this->assertEquals($expectedTraits, $traitsD);

		$this->assertEquals($traitsD, $traitsE);
	}
}