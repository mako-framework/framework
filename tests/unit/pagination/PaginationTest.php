<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pagination;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\pagination\Pagination;

/**
 * @group unit
 */
class PaginationTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function getRequest()
	{
		return Mockery::mock('mako\http\Request');
	}

	/**
	 *
	 */
	public function getViewFactory()
	{
		return Mockery::mock('mako\view\ViewFactory');
	}

	/**
	 *
	 */
	public function getView()
	{
		return Mockery::mock('mako\view\View');
	}

	/**
	 *
	 */
	public function getURLBuilder()
	{
		return Mockery::mock('mako\http\routing\URLBuilder');
	}

	/**
	 *
	 */
	public function testItems()
	{

		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(200, $pagination->items());
	}

	/**
	 *
	 */
	public function testItemsPerPage()
	{

		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(20, $pagination->itemsPerPage());
	}

	/**
	 *
	 */
	public function testCurrentPage()
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(1, $pagination->currentPage());
	}

	/**
	 *
	 */
	public function testNumberOfPages()
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(10, $pagination->numberOfPages());
	}

	/**
	 *
	 */
	public function testIsValidpage()
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertTrue($pagination->isValidPage());

		//

		$pagination = new Pagination(200, 20, 10);

		$this->assertTrue($pagination->isValidPage());

		//

		$pagination = new Pagination(200, 20, 11);

		$this->assertFalse($pagination->isValidPage());
	}

	/**
	 *
	 */
	public function testLimit()
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(20, $pagination->limit());
	}

	/**
	 *
	 */
	public function testOffset()
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(0, $pagination->offset());

		//

		$pagination = new Pagination(200, 20, 2);

		$this->assertEquals(20, $pagination->offset());
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testRenderException()
	{
		$pagination = new Pagination(200, 20, 1);

		$pagination->render('partials.pagination');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testPaginateExceptionWithNoRequest()
	{
		$pagination = new Pagination(200, 20, 1);

		$pagination->pagination();
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testPaginateExceptionWithNoUrlBuilder()
	{
		$pagination = new Pagination(200, 20, 1);

		$pagination->setRequest($this->getRequest());

		$pagination->pagination();
	}

	/**
	 *
	 */
	public function testRenderPage1()
	{
		$request = $this->getRequest();

		$query = Mockery::mock('mako\http\request\Parameters');

		$query->shouldReceive('all')->once()->andReturn(['page' => 1]);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

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
			'items'           => 200,
			'items_per_page'  => 20,
			'number_of_pages' => 10,
			'last'            => 'http://example.org/?page=10',
			'next'            => 'http://example.org/?page=2',

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

		$pagination = new Pagination(200, 20, 1);

		$pagination->setRequest($request);

		$pagination->setURLBuilder($urlBuilder);

		$pagination->setViewFactory($viewFactory);

		$pagination->render('partials.pagination');
	}

	/**
	 *
	 */
	public function testRenderPage2()
	{
		$request = $this->getRequest();

		$query = Mockery::mock('mako\http\request\Parameters');

		$query->shouldReceive('all')->once()->andReturn(['page' => 2]);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

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
			'items'           => 200,
			'items_per_page'  => 20,
			'number_of_pages' => 10,
			'first'           => 'http://example.org/?page=1',
			'previous'        => 'http://example.org/?page=1',
			'last'            => 'http://example.org/?page=10',
			'next'            => 'http://example.org/?page=3',

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

		$pagination = new Pagination(200, 20, 2);

		$pagination->setRequest($request);

		$pagination->setURLBuilder($urlBuilder);

		$pagination->setViewFactory($viewFactory);

		$pagination->render('partials.pagination');
	}

	public function testRenderPage10()
	{
		$request = $this->getRequest();

		$query = Mockery::mock('mako\http\request\Parameters');

		$query->shouldReceive('all')->once()->andReturn(['page' => 10]);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

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
			'items'           => 200,
			'items_per_page'  => 20,
			'number_of_pages' => 10,
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

		$pagination = new Pagination(200, 20, 10);

		$pagination->setRequest($request);

		$pagination->setURLBuilder($urlBuilder);

		$pagination->setViewFactory($viewFactory);

		$pagination->render('partials.pagination');
	}
}
