<?php

namespace mako\tests\unit;

/**
 * @group unit
 */

class helpersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function testMakoPath()
	{
		$this->assertEquals('/foo/bar/baz/index.php', mako_path('/foo/bar', 'baz', 'index'));

		$this->assertEquals('/foo/bar/baz/index.txt', mako_path('/foo/bar', 'baz', 'index', '.txt'));

		$this->assertEquals('/foo/bar/packages/bax/baz/index.php', mako_path('/foo/bar', 'baz', 'bax::index'));

		$this->assertEquals('/foo/bar/packages/bax/baz/index.txt', mako_path('/foo/bar', 'baz', 'bax::index', '.txt'));
	}

	/**
	 * 
	 */

	public function testMakoCascadingPath()
	{
		$this->assertEquals(['/foo/bar/baz/index.php'], mako_cascading_paths('/foo/bar', 'baz', 'index'));

		$this->assertEquals(['/foo/bar/baz/index.txt'], mako_cascading_paths('/foo/bar', 'baz', 'index', '.txt'));

		$this->assertEquals(['/foo/bar/baz/packages/bax/index.php', '/foo/bar/packages/bax/baz/index.php'], mako_cascading_paths('/foo/bar', 'baz', 'bax::index'));

		$this->assertEquals(['/foo/bar/baz/packages/bax/index.txt', '/foo/bar/packages/bax/baz/index.txt'], mako_cascading_paths('/foo/bar', 'baz', 'bax::index', '.txt'));
	}
}