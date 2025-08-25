<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pagination;

use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\routing\URLBuilder;
use mako\pagination\Pagination;
use mako\tests\TestCase;
use mako\view\View;
use mako\view\ViewFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

#[Group('unit')]
class PaginationTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest(): MockInterface&Request
	{
		return Mockery::mock(Request::class);
	}

	/**
	 *
	 */
	public function getViewFactory(): MockInterface&ViewFactory
	{
		return Mockery::mock(ViewFactory::class);
	}

	/**
	 *
	 */
	public function getView(): MockInterface&View
	{
		return Mockery::mock(View::class);
	}

	/**
	 *
	 */
	public function getURLBuilder(): MockInterface&URLBuilder
	{
		return Mockery::mock(URLBuilder::class);
	}

	/**
	 *
	 */
	public function testItems(): void
	{

		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(200, $pagination->items());
	}

	/**
	 *
	 */
	public function testItemsPerPage(): void
	{

		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(20, $pagination->itemsPerPage());
	}

	/**
	 *
	 */
	public function testCurrentPage(): void
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(1, $pagination->currentPage());
	}

	/**
	 *
	 */
	public function testNumberOfPages(): void
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(10, $pagination->numberOfPages());
	}

	/**
	 *
	 */
	public function testIsValidpage(): void
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
	public function testLimit(): void
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(20, $pagination->limit());
	}

	/**
	 *
	 */
	public function testOffset(): void
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(0, $pagination->offset());

		//

		$pagination = new Pagination(200, 20, 2);

		$this->assertEquals(20, $pagination->offset());
	}

	/**
	 *
	 */
	public function testToArrayWithoutRequestAndUrlBuilder(): void
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals(['current_page' => 1, 'number_of_pages' => 10, 'items' => 200, 'items_per_page' => 20], $pagination->toArray());
	}

	/**
	 *
	 */
	public function testToJsonWithoutRequestAndUrlBuilder(): void
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals('{"current_page":1,"number_of_pages":10,"items":200,"items_per_page":20}', $pagination->toJson());
	}

	/**
	 *
	 */
	public function testJsonEncodeWithoutRequestAndUrlBuilder(): void
	{
		$pagination = new Pagination(200, 20, 1);

		$this->assertEquals('{"current_page":1,"number_of_pages":10,"items":200,"items_per_page":20}', json_encode($pagination));
	}

	/**
	 *
	 */
	public function testRenderException(): void
	{
		$this->expectException(RuntimeException::class);

		$pagination = new Pagination(200, 20, 1);

		$pagination->render('partials.pagination');
	}

	/**
	 *
	 */
	public function testPaginateExceptionWithNoRequest(): void
	{
		$this->expectException(RuntimeException::class);

		$pagination = new Pagination(200, 20, 1);

		$pagination->pagination();
	}

	/**
	 *
	 */
	public function testPaginateExceptionWithNoUrlBuilder(): void
	{
		$this->expectException(RuntimeException::class);

		$pagination = new Pagination(200, 20, 1);

		$pagination->setRequest($this->getRequest());

		$pagination->pagination();
	}

	/**
	 *
	 */
	public function testRenderPage1(): void
	{
		$request = $this->getRequest();

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['page' => 1]);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

		$urlBuilder = $this->getURLBuilder();

		$urlBuilder->shouldReceive('current')->twice()->with(['page' => 1])->andReturn('http://example.org/?page=1');
		$urlBuilder->shouldReceive('current')->twice()->with(['page' => 2])->andReturn('http://example.org/?page=2');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 3])->andReturn('http://example.org/?page=3');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 4])->andReturn('http://example.org/?page=4');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 5])->andReturn('http://example.org/?page=5');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 10])->andReturn('http://example.org/?page=10');

		$viewFactory = $this->getViewFactory();

		$paginationArray =
		[
			'current_page'    => 1,
			'number_of_pages' => 10,
			'items'           => 200,
			'items_per_page'  => 20,
			'first'           => 'http://example.org/?page=1',
			'last'            => 'http://example.org/?page=10',
			'next'            => 'http://example.org/?page=2',
			'previous'        => null,
			'pages'           => [
				0 => [
					'url'        => 'http://example.org/?page=1',
					'number'     => 1,
					'is_current' => true,
				],
				1 => [
					'url'        => 'http://example.org/?page=2',
					'number'     => 2,
					'is_current' => false,
				],
				2 => [
					'url'        => 'http://example.org/?page=3',
					'number'     => 3,
					'is_current' => false,
				],
				3 => [
					'url'       => 'http://example.org/?page=4',
					'number'    => 4,
					'is_current'=> false,
				],
				4 => [
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
	public function testRenderPage2(): void
	{
		$request = $this->getRequest();

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['page' => 2]);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

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
			'current_page'    => 2,
			'number_of_pages' => 10,
			'items'           => 200,
			'items_per_page'  => 20,
			'first'           => 'http://example.org/?page=1',
			'last'            => 'http://example.org/?page=10',
			'next'            => 'http://example.org/?page=3',
			'previous'        => 'http://example.org/?page=1',
			'pages'           => [
				0 => [
					'url'        => 'http://example.org/?page=1',
					'number'     => 1,
					'is_current' => false,
				],
				1 => [
					'url'        => 'http://example.org/?page=2',
					'number'     => 2,
					'is_current' => true,
				],
				2 => [
					'url'        => 'http://example.org/?page=3',
					'number'     => 3,
					'is_current' => false,
				],
				3 => [
					'url'       => 'http://example.org/?page=4',
					'number'    => 4,
					'is_current'=> false,
				],
				4 => [
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

	/**
	 *
	 */
	public function testRenderPage10(): void
	{
		$request = $this->getRequest();

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['page' => 10]);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

		$urlBuilder = $this->getURLBuilder();

		$urlBuilder->shouldReceive('current')->once()->with(['page' => 1])->andReturn('http://example.org/?page=1');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 6])->andReturn('http://example.org/?page=6');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 7])->andReturn('http://example.org/?page=7');
		$urlBuilder->shouldReceive('current')->once()->with(['page' => 8])->andReturn('http://example.org/?page=8');
		$urlBuilder->shouldReceive('current')->twice()->with(['page' => 9])->andReturn('http://example.org/?page=9');
		$urlBuilder->shouldReceive('current')->twice()->with(['page' => 10])->andReturn('http://example.org/?page=10');

		$viewFactory = $this->getViewFactory();

		$paginationArray =
		[
			'current_page'    => 10,
			'number_of_pages' => 10,
			'items'           => 200,
			'items_per_page'  => 20,
			'first'           => 'http://example.org/?page=1',
			'last'            => 'http://example.org/?page=10',
			'next'            => null,
			'previous'        => 'http://example.org/?page=9',
			'pages'           => [
				0 => [
					'url'        => 'http://example.org/?page=6',
					'number'     => 6,
					'is_current' => false,
				],
				1 => [
					'url'        => 'http://example.org/?page=7',
					'number'     => 7,
					'is_current' => false,
				],
				2 => [
					'url'        => 'http://example.org/?page=8',
					'number'     => 8,
					'is_current' => false,
				],
				3 => [
					'url'       => 'http://example.org/?page=9',
					'number'    => 9,
					'is_current'=> false,
				],
				4 => [
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
