<?php

use \mako\http\routing\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{
	public function testBasicMatch()
	{
		$route = new Route(['GET'], '/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/'));

		$route = new Route(['GET'], '/foo', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo'));

		$this->assertFalse($route->isMatch('/foo/'));

		$route = new Route(['GET'], '/foo/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo'));

		$this->assertTrue($route->isMatch('/foo/'));
	}

	public function testMatchWithParameter()
	{
		$route = new Route(['GET'], '/foo/{id}', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123'));

		$this->assertFalse($route->isMatch('/foo/'));

		$this->assertFalse($route->isMatch('/foo/123/'));

		$route = new Route(['GET'], '/foo/{id}/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123/'));

		$this->assertFalse($route->isMatch('/foo/'));

		$this->assertTrue($route->isMatch('/foo/123/'));
	}
}