<?php

namespace mako\tests\unit\pagination;

use mako\pagination\Pagination;

use \Mockery as m;

/**
 * @group unit
 */

class PaginationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getRequest()
	{
		return m::mock('mako\http\Request');
	}

	/**
	 *
	 */

	public function getViewFactory()
	{
		return m::mock('mako\view\ViewFactory');
	}

	/**
	 *
	 */

	public function getView()
	{
		return m::mock('mako\view\View');
	}

	/**
	 *
	 */

	public function getURLBuilder()
	{
		return m::mock('mako\http\routing\URLBuilder');
	}

	/**
	 *
	 */

	public function testConstructor()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(1);

		$pagination = new Pagination($request, 200);
	}

	/**
	 *
	 */

	public function testConstructorWithConfig()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('side', 1)->andReturn(1);

		$pagination = new Pagination($request, 200, ['page_key' => 'side']);
	}

	/**
	 *
	 */

	public function testPages()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(1);

		$pagination = new Pagination($request, 200);

		$this->assertEquals(10, $pagination->pages());
	}

	/**
	 *
	 */

	public function testCurrentPage()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(11);

		$pagination = new Pagination($request, 200);

		$this->assertEquals(11, $pagination->currentPage());
	}

	/**
	 *
	 */

	public function testLimit()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(1);

		$pagination = new Pagination($request, 200);

		$this->assertEquals(20, $pagination->limit());
	}

	/**
	 *
	 */

	public function testLimitWithConfig()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(1);

		$pagination = new Pagination($request, 200, ['items_per_page' => 10]);

		$this->assertEquals(10, $pagination->limit());
	}

	/**
	 *
	 */

	public function testOffset()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(1);

		$pagination = new Pagination($request, 200);

		$this->assertEquals(0, $pagination->offset());

		//

		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(2);

		$pagination = new Pagination($request, 200);

		$this->assertEquals(20, $pagination->offset());
	}

	/**
	 *
	 */

	public function testOffsetWithConfig()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(1);

		$pagination = new Pagination($request, 200);

		$this->assertEquals(0, $pagination->offset());

		//

		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(2);

		$pagination = new Pagination($request, 200, ['items_per_page' => 10]);

		$this->assertEquals(10, $pagination->offset());
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testRenderException()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(2);

		$pagination = new Pagination($request, 200, ['items_per_page' => 10]);

		$pagination->render('partials.pagination');
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testPaginateException()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(2);

		$viewFactory = $this->getViewFactory();

		$pagination = new Pagination($request, 200, [], null, $viewFactory);

		$pagination->render('partials.pagination');
	}

	/**
	 *
	 */

	public function testRenderPage1()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(1);

		$request->shouldReceive('get')->once()->andReturn([]);

		$urlBuilder = $this->getURLBuilder();

		$urlBuilder->shouldReceive('current')->once()->with(['page' => 1])->andReturn('http://example.org/?page=1');
		$urlBuilder->shouldReceive('current')->twice()->with(['page' => 2])->andReturn('http://example.org/?page=2');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 3])->andReturn('http://example.org/?page=3');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 4])->andReturn('http://example.org/?page=4');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 5])->andReturn('http://example.org/?page=5');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 10])->andReturn('http://example.org/?page=10');

		$viewFactory = $this->getViewFactory();

		$paginationArray =
		[
			'count'=> 10,
			'last' => 'http://example.org/?page=10',
			'next' => 'http://example.org/?page=2',

			'pages'=>
			[
				0 =>
				[
					'url'        => 'http://example.org/?page=1',
					'number'     => 1,
					'is_current' => true,
				],
				1 =>
				[
					'url'        => 'http://example.org/?page=2',
					'number'     => 2,
					'is_current' => false,
				],
				2 =>
				[
					'url'        => 'http://example.org/?page=3',
					'number'     => 3,
					'is_current' => false,
				],
				3 =>
				[
					'url'       => 'http://example.org/?page=4',
					'number'    => 4,
					'is_current'=> false,
				],
				4 =>
				[
					'url'        => 'http://example.org/?page=5',
					'number'     => 5,
					'is_current' => false,
				],
			],
		];

		$view = $this->getView();

		$view->shouldReceive('render')->once()->andReturn('pagination');

		$viewFactory->shouldReceive('create')->once()->with('partials.pagination', $paginationArray)->andReturn($view);

		$pagination = new Pagination($request, 200, [], $urlBuilder, $viewFactory);

		$pagination->render('partials.pagination');
	}

	/**
	 *
	 */

	public function testRenderPage2()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(2);

		$request->shouldReceive('get')->once()->andReturn([]);

		$urlBuilder = $this->getURLBuilder();

		$urlBuilder->shouldReceive('current')->times(3)->with(['page' => 1])->andReturn('http://example.org/?page=1');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 2])->andReturn('http://example.org/?page=2');
		$urlBuilder->shouldReceive('current')->twice()->with(['page' => 3])->andReturn('http://example.org/?page=3');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 4])->andReturn('http://example.org/?page=4');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 5])->andReturn('http://example.org/?page=5');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 10])->andReturn('http://example.org/?page=10');

		$viewFactory = $this->getViewFactory();

		$paginationArray =
		[
			'count'    => 10,
			'first'    => 'http://example.org/?page=1',
			'previous' => 'http://example.org/?page=1',
			'last'     => 'http://example.org/?page=10',
			'next'     => 'http://example.org/?page=3',

			'pages'=>
			[
				0 =>
				[
					'url'        => 'http://example.org/?page=1',
					'number'     => 1,
					'is_current' => false,
				],
				1 =>
				[
					'url'        => 'http://example.org/?page=2',
					'number'     => 2,
					'is_current' => true,
				],
				2 =>
				[
					'url'        => 'http://example.org/?page=3',
					'number'     => 3,
					'is_current' => false,
				],
				3 =>
				[
					'url'       => 'http://example.org/?page=4',
					'number'    => 4,
					'is_current'=> false,
				],
				4 =>
				[
					'url'        => 'http://example.org/?page=5',
					'number'     => 5,
					'is_current' => false,
				],
			],
		];

		$view = $this->getView();

		$view->shouldReceive('render')->once()->andReturn('pagination');

		$viewFactory->shouldReceive('create')->once()->with('partials.pagination', $paginationArray)->andReturn($view);

		$pagination = new Pagination($request, 200, [], $urlBuilder, $viewFactory);

		$pagination->render('partials.pagination');
	}

	public function testRenderPage10()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->with('page', 1)->andReturn(10);

		$request->shouldReceive('get')->once()->andReturn([]);

		$urlBuilder = $this->getURLBuilder();

		$urlBuilder->shouldReceive('current')->once()->with(['page' => 1])->andReturn('http://example.org/?page=1');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 6])->andReturn('http://example.org/?page=6');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 7])->andReturn('http://example.org/?page=7');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 8])->andReturn('http://example.org/?page=8');
		$urlBuilder->shouldReceive('current')->twice()->with(['page' => 9])->andReturn('http://example.org/?page=9');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 10])->andReturn('http://example.org/?page=10');

		$viewFactory = $this->getViewFactory();

		$paginationArray =
		[
			'count'    => 10,
			'first'    => 'http://example.org/?page=1',
			'previous' => 'http://example.org/?page=9',

			'pages'=>
			[
				0 =>
				[
					'url'        => 'http://example.org/?page=6',
					'number'     => 6,
					'is_current' => false,
				],
				1 =>
				[
					'url'        => 'http://example.org/?page=7',
					'number'     => 7,
					'is_current' => false,
				],
				2 =>
				[
					'url'        => 'http://example.org/?page=8',
					'number'     => 8,
					'is_current' => false,
				],
				3 =>
				[
					'url'       => 'http://example.org/?page=9',
					'number'    => 9,
					'is_current'=> false,
				],
				4 =>
				[
					'url'        => 'http://example.org/?page=10',
					'number'     => 10,
					'is_current' => true,
				],
			],
		];

		$view = $this->getView();

		$view->shouldReceive('render')->once()->andReturn('pagination');

		$viewFactory->shouldReceive('create')->once()->with('partials.pagination', $paginationArray)->andReturn($view);

		$pagination = new Pagination($request, 200, [], $urlBuilder, $viewFactory);

		$pagination->render('partials.pagination');
	}
}