<?php

namespace mako\tests\unit;

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

class helpersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function testGetPath()
	{
		$this->assertEquals('/foo/bar/baz/index.php', \mako\get_path('/foo/bar', 'baz', 'index'));

		$this->assertEquals('/foo/bar/baz/index.txt', \mako\get_path('/foo/bar', 'baz', 'index', '.txt'));

		$this->assertEquals('/foo/bar/packages/bax/baz/index.php', \mako\get_path('/foo/bar', 'baz', 'bax::index'));

		$this->assertEquals('/foo/bar/packages/bax/baz/index.txt', \mako\get_path('/foo/bar', 'baz', 'bax::index', '.txt'));
	}

	/**
	 * 
	 */

	public function testGetCascadingPaths()
	{
		$this->assertEquals(['/foo/bar/baz/index.php'], \mako\get_cascading_paths('/foo/bar', 'baz', 'index'));

		$this->assertEquals(['/foo/bar/baz/index.txt'], \mako\get_cascading_paths('/foo/bar', 'baz', 'index', '.txt'));

		$this->assertEquals(['/foo/bar/baz/packages/bax/index.php', '/foo/bar/packages/bax/baz/index.php'], \mako\get_cascading_paths('/foo/bar', 'baz', 'bax::index'));

		$this->assertEquals(['/foo/bar/baz/packages/bax/index.txt', '/foo/bar/packages/bax/baz/index.txt'], \mako\get_cascading_paths('/foo/bar', 'baz', 'bax::index', '.txt'));
	}

	/**
	 * 
	 */

	public function testGetClassTraits()
	{
		$traitsD = \mako\get_class_traits('mako\tests\unit\D');

		$traitsE = \mako\get_class_traits('mako\tests\unit\E');

		$expectedTraits = ['mako\tests\unit\C' => 'mako\tests\unit\C', 'mako\tests\unit\B' => 'mako\tests\unit\B', 'mako\tests\unit\A' => 'mako\tests\unit\A'];

		$this->assertEquals($expectedTraits, $traitsD);

		$this->assertEquals($traitsD, $traitsE);
	}
}