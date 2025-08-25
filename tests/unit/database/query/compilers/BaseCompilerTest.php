<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use DateTime;
use Exception;
use mako\database\connections\Connection;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\database\query\Raw;
use mako\database\query\ResultSet;
use mako\database\query\Subquery;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class BaseCompilerTest extends TestCase
{
	/**
	 *
	 */
	public function testSetAndGetDateFormat(): void
	{
		$format = Compiler::getDateFormat();

		Compiler::setDateFormat('foobar');

		$this->assertSame('foobar', Compiler::getDateFormat());

		Compiler::setDateFormat($format);
	}

	/**
	 *
	 */
	protected function getConnection(): Connection&MockInterface
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) {
			return new Compiler($query);
		});

		$connection->shouldReceive('column')->andReturn(null);

		return $connection;
	}

	/**
	 *
	 */
	protected function getBuilder($table = 'foobar')
	{
		return (new Query($this->getConnection()))->table($table);
	}

	/**
	 *
	 */
	public function testBasicSelect(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicSelectWithoutTable(): void
	{
		$query = $this->getBuilder(null);

		$query = $query->selectRaw('1, 2, 3')->getCompiler()->select();

		$this->assertEquals('SELECT 1, 2, 3', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithMultipleTables(): void
	{
		$query = $this->getBuilder(['foo', 'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foo", "bar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicSelectWithSubqueryUsingClosure(): void
	{
		$query = $this->getBuilder(new Subquery(function ($query): void {
			$query->table('foobar');
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT * FROM "foobar")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicSelectWithSubqueryWithAggregate(): void
	{
		$query = $this->getBuilder(new Subquery(function ($query): void {
			$query->table('foobar')->min('foobar');
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT MIN("foobar") FROM "foobar")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicSelectWithSubqueryWithTableAlias(): void
	{
		$query = $this->getBuilder(new Subquery(function ($query): void {
			$query->table('foobar');
		}, 'table_alias'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT * FROM "foobar") AS "table_alias"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testDistinctSelect(): void
	{
		$query = $this->getBuilder();

		$query->distinct();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT DISTINCT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCloumns(): void
	{
		$query = $this->getBuilder();

		$query->select(['foo', 'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "bar" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCloumnAlias(): void
	{
		$query = $this->getBuilder();

		$query->select(['foo', 'bar as baz']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "bar" AS "baz" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithTablePrefix(): void
	{
		$query = $this->getBuilder();

		$query->select(['foo', 'foobar.bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "foobar"."bar" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJSONColumn(): void
	{
		$this->expectException(Exception::class);

		$this->expectExceptionMessage('The [ mako\database\query\compilers\Compiler ] query compiler does not support the unified JSON field syntax.');

		$query = $this->getBuilder();

		$query->select(['json->0->bar']);

		$query = $query->getCompiler()->select();
	}

	/**
	 *
	 */
	public function testSelectWithSubqueryColumn(): void
	{
		$query = $this->getBuilder();

		$query->select(['foo', new Subquery(function ($query): void {
			$query->table('barfoo')->select(['baz'])->limit(1);
		}, 'baz')]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", (SELECT "baz" FROM "barfoo" LIMIT 1) AS "baz" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimit(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LIMIT 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOffset(): void
	{
		$query = $this->getBuilder();

		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" OFFSET 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndOffset(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LIMIT 10 OFFSET 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExclusiveLock(): void
	{
		$query = $this->getBuilder();

		$query->lock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSharedLock(): void
	{
		$query = $this->getBuilder();

		$query->lock(false);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSharedLockMethod(): void
	{
		$query = $this->getBuilder();

		$query->sharedLock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCustomLock(): void
	{
		$query = $this->getBuilder();

		$query->lock('CUSTOM LOCK');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithWhere(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ?', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithTupleWhere(): void
	{
		$query = $this->getBuilder();

		$query->where(['foo', 'bar'], '=', ['baz', 'bax']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE ("foo", "bar") = (?, ?)', $query['sql']);
		$this->assertEquals(['baz', 'bax'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawWhere(): void
	{
		$query = $this->getBuilder();

		$query->where(new Raw('LOWER("foo")'), '=', 'bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE LOWER("foo") = ?', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithWhereRaw(): void
	{
		$query = $this->getBuilder();

		$query->whereRaw('foo', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEmpty($query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithTupleWhereRaw(): void
	{
		$query = $this->getBuilder();

		$query->whereRaw(['foo', 'bar'], '=', '(SELECT foo, bar FROM foobar LIMIT 1)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE ("foo", "bar") = (SELECT foo, bar FROM foobar LIMIT 1)', $query['sql']);
		$this->assertEmpty($query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithFullWhereRaw(): void
	{
		$query = $this->getBuilder();

		$query->whereRaw('MATCH(foo) AGAINST (? IN BOOLEAN MODE)', ['bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE MATCH(foo) AGAINST (? IN BOOLEAN MODE)', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithWheres(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->where('bar', '=', 'foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? AND "bar" = ?', $query['sql']);
		$this->assertEquals(['bar', 'foo'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrWhere(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orWhere('foo', '=', 'baz');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "foo" = ?', $query['sql']);
		$this->assertEquals(['bar', 'baz'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrWhereRaw(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orWhereRaw('foo', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "foo" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithFullOrWhereRaw(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orWhereRaw('MATCH(foo) AGAINST (? IN BOOLEAN MODE)', ['baz']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR MATCH(foo) AGAINST (? IN BOOLEAN MODE)', $query['sql']);
		$this->assertEquals(['bar', 'baz'], $query['params']);
	}

	/**
	 *
	 */
	public function testWhereColumn(): void
	{
		$query = $this->getBuilder();

		$query->whereColumn('foo', '=', 'bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = "bar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testOrWhereColumn(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 1);
		$query->orWhereColumn('bar', '=', 'baz');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "bar" = "baz"', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testWhereColumnWithTuple(): void
	{
		$query = $this->getBuilder();

		$query->whereColumn(['foo', 'bar'], '=', 'baz');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE ("foo", "bar") = "baz"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereColumn(['foo', 'bar'], '=', ['baz', 'bax']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE ("foo", "bar") = ("baz", "bax")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNestedWheres(): void
	{
		$query = $this->getBuilder();

		$query->where(function ($query): void {
			$query->where('foo', '=', 'bar');
			$query->where('bar', '=', 'foo');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE ("foo" = ? AND "bar" = ?)', $query['sql']);
		$this->assertEquals(['bar', 'foo'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithBetween(): void
	{
		$query = $this->getBuilder();

		$query->between('foo', 1, 10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithBetweenAndOrBetween(): void
	{
		$query = $this->getBuilder();

		$query->between('foo', 1, 10);

		$query->orBetween('foo', 21, 30);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" BETWEEN ? AND ? OR "foo" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10, 21, 30], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotBetween(): void
	{
		$query = $this->getBuilder();

		$query->notBetween('foo', 1, 10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotBetweenAndOrNotBetween(): void
	{
		$query = $this->getBuilder();

		$query->notBetween('foo', 1, 10);

		$query->orNotBetween('foo', 21, 30);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT BETWEEN ? AND ? OR "foo" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10, 21, 30], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithInAndOrIn(): void
	{
		$query = $this->getBuilder();

		$query->in('foo', [1, 2, 3]);

		$query->orIn('foo', [4, 5, 6]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (?, ?, ?) OR "foo" IN (?, ?, ?)', $query['sql']);
		$this->assertEquals([1, 2, 3, 4, 5, 6], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawIn(): void
	{
		$query = $this->getBuilder();

		$query->in('foo', new Raw('SELECT id FROM barfoo'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (SELECT id FROM barfoo)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawWithBoundParameters(): void
	{
		$query = $this->getBuilder();

		$query->where('id', '>', 1);

		$query->in('foo', new Raw('SELECT id FROM barfoo WHERE id > ?', [2]));

		$query->where('id', '<', 3);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "id" > ? AND "foo" IN (SELECT id FROM barfoo WHERE id > ?) AND "id" < ?', $query['sql']);
		$this->assertEquals([1, 2, 3], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSubqueryIn(): void
	{
		$query = $this->getBuilder();

		$query->in('foo', new Subquery(function ($query): void {
			$query->table('barfoo')->select(['id']);
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (SELECT "id" FROM "barfoo")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotIn(): void
	{
		$query = $this->getBuilder();

		$query->notIn('foo', [1, 2, 3]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT IN (?, ?, ?)', $query['sql']);
		$this->assertEquals([1, 2, 3], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotInAndOrNotIn(): void
	{
		$query = $this->getBuilder();

		$query->notIn('foo', [1, 2, 3]);

		$query->orNotIn('foo', [4, 5, 6]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT IN (?, ?, ?) OR "foo" NOT IN (?, ?, ?)', $query['sql']);
		$this->assertEquals([1, 2, 3, 4, 5, 6], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNull(): void
	{
		$query = $this->getBuilder();

		$query->isNull('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNullAndOrNull(): void
	{
		$query = $this->getBuilder();

		$query->isNull('foo');

		$query->orIsNull('bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NULL OR "bar" IS NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNotNull(): void
	{
		$query = $this->getBuilder();

		$query->isNotNull('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NOT NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNotNullAndOrNotNull(): void
	{
		$query = $this->getBuilder();

		$query->isNotNull('foo');

		$query->orIsNotNull('bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NOT NULL OR "bar" IS NOT NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExistsSubquery(): void
	{
		$query = $this->getBuilder();

		$query->exists(new Subquery(function ($query): void {
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'));
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExistsSubqueryAndOrExists(): void
	{
		$query = $this->getBuilder();

		$query->exists(new Subquery(function ($query): void {
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'));
		}));

		$query->orExists(new Subquery(function ($query): void {
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('barbaz.id'));
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id) OR EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = barbaz.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExistsClosure(): void
	{
		$query = $this->getBuilder();

		$query->exists(new Subquery(function ($query): void {
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'));
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotExists(): void
	{
		$query = $this->getBuilder();

		$query->notExists(new Subquery(function ($query): void {
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'));
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE NOT EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotExistsAndOrNotExists(): void
	{
		$query = $this->getBuilder();

		$query->notExists(new Subquery(function ($query): void {
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'));
		}));

		$query->orNotExists(new Subquery(function ($query): void {
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('barbaz.id'));
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE NOT EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id) OR NOT EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = barbaz.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJoin(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJoinRaw(): void
	{
		$query = $this->getBuilder();

		$query->joinRaw('barfoo', 'barfoo.foobar_id', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLeftJoin(): void
	{
		$query = $this->getBuilder();

		$query->leftJoin('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LEFT OUTER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLeftJoinRaw(): void
	{
		$query = $this->getBuilder();

		$query->leftJoinRaw('barfoo', 'barfoo.foobar_id', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LEFT OUTER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRightJoin(): void
	{
		$query = $this->getBuilder();

		$query->rightJoin('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" RIGHT OUTER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRightJoinRaw(): void
	{
		$query = $this->getBuilder();

		$query->rightJoinRaw('barfoo', 'barfoo.foobar_id', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" RIGHT OUTER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCrossJoin(): void
	{
		$query = $this->getBuilder();

		$query->crossJoin('barfoo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" CROSS JOIN "barfoo"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithComplexJoin(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', function ($join): void {
			$join->on('barfoo.foobar_id', '=', 'foobar.id');
			$join->orOn('barfoo.foobar_id', '!=', 'foobar.id');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id" OR "barfoo"."foobar_id" != "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithComplexNestedJoin(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', function ($join): void {
			$join->on('barfoo.foobar_id', '=', 'foobar.id');
			$join->on(function ($join): void {
				$join->on('barfoo.foobar_id', '=', 'foobar.id');
				$join->orOn('barfoo.foobar_id', '!=', 'foobar.id');
			});
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id" AND ("barfoo"."foobar_id" = "foobar"."id" OR "barfoo"."foobar_id" != "foobar"."id")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithComplexRawJoin(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', function ($join): void {
			$join->onRaw('barfoo.foobar_id', '=', 'SUBSTRING("foo", 1, 2)');
			$join->orOnRaw('barfoo.foobar_id', '!=', 'SUBSTRING("foo", 1, 2)');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING("foo", 1, 2) OR "barfoo"."foobar_id" != SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithComplexRawJoinWithParameter(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', function ($join): void {
			$join->onRaw('barfoo.foobar_id', '=', 'SUBSTRING(?, 1, 2)', ['foo']);
			$join->orOnRaw('barfoo.foobar_id', '!=', 'SUBSTRING(?, 1, 2)', ['bar']);
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING(?, 1, 2) OR "barfoo"."foobar_id" != SUBSTRING(?, 1, 2)', $query['sql']);
		$this->assertEquals(['foo', 'bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSubqueryJoin(): void
	{
		$query = $this->getBuilder();

		$query->join(new Subquery(function ($query): void {
			$query->table('barfoo')->where('id', '>', 1);
		}, 'barfoo'), 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN (SELECT * FROM "barfoo" WHERE "id" > ?) AS "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawSubqueryJoin(): void
	{
		$query = $this->getBuilder();

		$query->join(new Raw('(SELECT * FROM "barfoo" WHERE "id" > 1) AS "barfoo"'), 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN (SELECT * FROM "barfoo" WHERE "id" > 1) AS "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLateralJoin(): void
	{
		$query = $this->getBuilder();

		$query->table('customers')
		->lateralJoin(new Subquery(function (Query $query): void {
			$query->table('sales')
			->whereRaw('sales.customer_id', '=', '"customers"."id"')
			->descending('created_at')
			->limit(3);
		}, 'recent_sales'))
		->select(['customers.*', 'recent_sales.*']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customers".*, "recent_sales".* FROM "customers" LEFT OUTER JOIN LATERAL (SELECT * FROM "sales" WHERE "sales"."customer_id" = "customers"."id" ORDER BY "created_at" DESC LIMIT 3) AS "recent_sales" ON TRUE', $query['sql']);
	}

	/**
	 *
	 */
	public function testSelectWithGroupBy(): void
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithGroupByArray(): void
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', 'order_date', new Raw('SUM(price) as sum')]);

		$query->groupBy(['customer', 'order_date']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", "order_date", SUM(price) as sum FROM "orders" GROUP BY "customer", "order_date"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHaving(): void
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->having(new Raw('SUM(price)'), '<', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ?', $query['sql']);
		$this->assertEquals([2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHavingRaw(): void
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->havingRaw('SUM(price)', '<', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ?', $query['sql']);
		$this->assertEquals([2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHavingAndOrHaving(): void
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->having(new Raw('SUM(price)'), '<', 2000);
		$query->orHaving(new Raw('SUM(price)'), '>', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ? OR SUM(price) > ?', $query['sql']);
		$this->assertEquals([2000, 2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHavinRawgAndOrHavingRaw(): void
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->havingRaw('SUM(price)', '<', 2000);
		$query->orHavingRaw('SUM(price)', '>', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ? OR SUM(price) > ?', $query['sql']);
		$this->assertEquals([2000, 2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrder(): void
	{
		$query = $this->getBuilder();

		$query->orderBy('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderArray(): void
	{
		$query = $this->getBuilder();

		$query->orderBy(['foo', 'bar'], 'DESC');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo", "bar" DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderRaw(): void
	{
		$query = $this->getBuilder();

		$query->orderByRaw('FIELD(id, 1, 2, 3)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, 1, 2, 3) ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderRawWithParameters(): void
	{
		$query = $this->getBuilder();

		$query->orderByRaw('FIELD(id, [?])', [[1, 2, 3]]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, [?]) ASC', $query['sql']);
		$this->assertEquals([[1, 2, 3]], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderDescending(): void
	{
		$query = $this->getBuilder();

		$query->descending('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderDescendingRaw(): void
	{
		$query = $this->getBuilder();

		$query->descendingRaw('FIELD(id, 1, 2, 3)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, 1, 2, 3) DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderDescendingRawWithParameters(): void
	{
		$query = $this->getBuilder();

		$query->descendingRaw('FIELD(id, [?])', [[1, 2, 3]]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, [?]) DESC', $query['sql']);
		$this->assertEquals([[1, 2, 3]], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderAscending(): void
	{
		$query = $this->getBuilder();

		$query->ascending('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderAscendingRaw(): void
	{
		$query = $this->getBuilder();

		$query->ascendingRaw('FIELD(id, 1, 2, 3)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, 1, 2, 3) ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderAscendingRawWithParameters(): void
	{
		$query = $this->getBuilder();

		$query->ascendingRaw('FIELD(id, [?])', [[1, 2, 3]]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, [?]) ASC', $query['sql']);
		$this->assertEquals([[1, 2, 3]], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithMultipleOrder(): void
	{
		$query = $this->getBuilder();

		$query->orderBy('foo');
		$query->orderBy('bar', 'DESC');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" ASC, "bar" DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectClearOrderings(): void
	{
		$query = $this->getBuilder();

		$query->orderBy('foo');
		$query->orderBy('bar', 'DESC');

		$query = $query->clearOrderings()->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicDelete(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->delete();

		$this->assertEquals('DELETE FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testDeleteWithWhere(): void
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->delete();

		$this->assertEquals('DELETE FROM "foobar" WHERE "id" = ?', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicInsert(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insert(['foo' => 'bar']);

		$this->assertEquals('INSERT INTO "foobar" ("foo") VALUES (?)', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicInsertWithNoValues(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insert([]);

		$this->assertEquals('INSERT INTO "foobar" DEFAULT VALUES', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicInsertWithMultipleRows(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertMultiple(['foo' => 'bar', 'baz' => 'bax'], ['foo' => 'bar', 'baz' => 'bax']);

		$this->assertEquals('INSERT INTO "foobar" ("foo", "baz") VALUES (?, ?), (?, ?)', $query['sql']);
		$this->assertEquals(['bar', 'bax', 'bar', 'bax'], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicUpdate(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['foo' => 'bar']);

		$this->assertEquals('UPDATE "foobar" SET "foo" = ?', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateWithJSONColumn(): void
	{
		$this->expectException(Exception::class);

		$this->expectExceptionMessage('The [ mako\database\query\compilers\Compiler ] query compiler does not support the unified JSON field syntax.');

		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->bar->0' => 1]);
	}

	/**
	 *
	 */
	public function testUpdateWithWhere(): void
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->update(['foo' => 'bar']);

		$this->assertEquals('UPDATE "foobar" SET "foo" = ? WHERE "id" = ?', $query['sql']);
		$this->assertEquals(['bar', 1], $query['params']);
	}

	/**
	 *
	 */
	public function testCountAggregate(): void
	{
		$query = $this->getBuilder();

		$query->count();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(*) FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		$query = $this->getBuilder();

		$query->count('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testCountRawAggregate(): void
	{
		$query = $this->getBuilder();

		$query->count(new Raw('foo * bar'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(foo * bar) FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testCountDistinctAggregate(): void
	{
		$query = $this->getBuilder();

		$query->countDistinct('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(DISTINCT "foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testCountDistinctAggregateWithMultipleColumns(): void
	{
		$query = $this->getBuilder();

		$query->countDistinct(['foo', 'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(DISTINCT "foo", "bar") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testAvgAggregate(): void
	{
		$query = $this->getBuilder();

		$query->avg('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT AVG("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testMaxAggregate(): void
	{
		$query = $this->getBuilder();

		$query->max('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT MAX("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testMinAggregate(): void
	{
		$query = $this->getBuilder();

		$query->min('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT MIN("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSumAggregate(): void
	{
		$query = $this->getBuilder();

		$query->sum('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT SUM("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testAggregateWithRaw(): void
	{
		$query = $this->getBuilder();

		$query->count(new Raw('DISTINCT "foo"'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(DISTINCT "foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testColumnWithoutParam(): void
	{
		$query = $this->getBuilder();

		$query->select(['id']);

		$query->column();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "id" FROM "foobar" LIMIT 1', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testColumnWithParam(): void
	{
		$query = $this->getBuilder();

		$query->column('id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "id" FROM "foobar" LIMIT 1', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUnion(): void
	{
		$query = $this->getBuilder('sales2015')->union()->table('sales2016');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" UNION SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testMultipleUnions(): void
	{
		$query = $this->getBuilder('sales2015')->union()->table('sales2016')->union()->table('sales2017');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" UNION SELECT * FROM "sales2016" UNION SELECT * FROM "sales2017"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUnionsWithLimits(): void
	{
		$query = $this->getBuilder(new Subquery(function ($query): void {
			$query->table('sales2015')->limit(10);
		}, 'limitedsales2015'))
		->union()
		->table(new Subquery(function ($query): void {
			$query->table('sales2016')->limit(10);
		}, 'limitedsales2016'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT * FROM "sales2015" LIMIT 10) AS "limitedsales2015" UNION SELECT * FROM (SELECT * FROM "sales2016" LIMIT 10) AS "limitedsales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUnionAll(): void
	{
		$query = $this->getBuilder('sales2015')->unionAll()->table('sales2016');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" UNION ALL SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testIntersect(): void
	{
		$query = $this->getBuilder('sales2015')->intersect()->table('sales2016');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" INTERSECT SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testAll(): void
	{
		$query = $this->getBuilder('sales2015')->intersectAll()->table('sales2016');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" INTERSECT ALL SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testExcept(): void
	{
		$query = $this->getBuilder('sales2015')->except()->table('sales2016');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" EXCEPT SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testExceptAll(): void
	{
		$query = $this->getBuilder('sales2015')->exceptAll()->table('sales2016');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" EXCEPT ALL SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithPrefix(): void
	{
		$query = $this->getBuilder();

		$query->prefix('/*PREFIX*/');

		$query = $query->getCompiler()->select();

		$this->assertEquals('/*PREFIX*/ SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBatch(): void
	{
		/** @var MockInterface&Query $builder */
		$builder = Mockery::mock(Query::class . '[limit,offset,all]', [$this->getConnection()]);

		$builder->shouldReceive('limit')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(10);

		$builder->shouldReceive('offset')->once()->with(15);

		$builder->shouldReceive('offset')->once()->with(20);

		$builder->shouldReceive('all')->times(5)->andReturn(new ResultSet([5]), new ResultSet([5]), new ResultSet([5]), new ResultSet([5]), new ResultSet([]));

		$batches = 0;

		$builder->ascending('id')->batch(function ($results) use (&$batches): void {
			$this->assertEquals([5], $results->getItems());

			$batches++;
		}, 5);

		$this->assertEquals(4, $batches);

		//

		/** @var MockInterface&Query $builder */
		$builder = Mockery::mock(Query::class . '[limit,offset,all]', [$this->getConnection()]);

		$builder->shouldReceive('limit')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(10);

		$builder->shouldReceive('offset')->once()->with(15);

		$builder->shouldReceive('offset')->once()->with(20);

		$builder->shouldReceive('all')->times(4)->andReturn(new ResultSet([5]), new ResultSet([5]), new ResultSet([5]), new ResultSet([]));

		$batches = 0;

		$builder->ascending('id')->batch(function ($results) use (&$batches): void {
			$this->assertEquals([5], $results->getItems());

			$batches++;
		}, 5, 5);

		$this->assertEquals(3, $batches);

		//

		/** @var MockInterface&Query $builder */
		$builder = Mockery::mock(Query::class . '[limit,offset,all]', [$this->getConnection()]);

		$builder->shouldReceive('limit')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(10);

		$builder->shouldReceive('all')->times(2)->andReturn(new ResultSet([5]), new ResultSet([5]));

		$batches = 0;

		$builder->ascending('id')->batch(function ($results) use (&$batches): void {
			$this->assertEquals([5], $results->getItems());

			$batches++;
		}, 5, 5, 15);

		$this->assertEquals(2, $batches);
	}

	/**
	 *
	 */
	public function testCommonTableExpression(): void
	{
		$query = $this->getBuilder('cte');

		$query->with('cte', [], new Subquery(function ($query): void {
			$query->table('articles');
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('WITH "cte" AS (SELECT * FROM "articles") SELECT * FROM "cte"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testCommonTableExpressionWithColumns(): void
	{
		$query = $this->getBuilder('cte');

		$query->with('cte', ['title', 'content'], new Subquery(function ($query): void {
			$query->table('articles')->select(['title', 'content']);
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('WITH "cte" ("title", "content") AS (SELECT "title", "content" FROM "articles") SELECT * FROM "cte"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testRecursiveCommonTableExpression(): void
	{
		$query = $this->getBuilder('cte');

		$query->withRecursive('cte', [], new Subquery(function ($query): void {
			$query->table('articles');
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('WITH RECURSIVE "cte" AS (SELECT * FROM "articles") SELECT * FROM "cte"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testMultipleCommonTableExpressions(): void
	{
		$query = $this->getBuilder('cte2');

		$query->with('cte1', [], new Subquery(function ($query): void {
			$query->table('articles');
		}));

		$query->with('cte2', [], new Subquery(function ($query): void {
			$query->table('cte1');
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('WITH "cte1" AS (SELECT * FROM "articles"), "cte2" AS (SELECT * FROM "cte1") SELECT * FROM "cte2"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testNestedCommonTableExpression(): void
	{
		$query = $this->getBuilder('cte');

		$query->with('cte', ['a', 'b', 'c'], new Subquery(function ($query): void {
			$query->with('cte2', [], new Subquery(function ($query): void {
				$query->selectRaw('1, 2, 3');
			}))
			->table('cte2');
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('WITH "cte" ("a", "b", "c") AS (WITH "cte2" AS (SELECT 1, 2, 3) SELECT * FROM "cte2") SELECT * FROM "cte"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testCloneQueryWithUnions(): void
	{
		$query1 = $this->getBuilder();

		$query1->table('foo')->where('id', '=', 1);

		$query1->union();

		$query1->table('bar')->where('id', '=', 2);

		$query2 = clone $query1;

		$this->assertSame($query1->getCompiler()->select()['params'], $query2->getCompiler()->select()['params']);
	}

	/**
	 *
	 */
	public function testDateTimeParameter(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', new DateTime('2021-11-02 13:37:00'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ?', $query['sql']);
		$this->assertEquals(['2021-11-02 13:37:00'], $query['params']);
	}
}
