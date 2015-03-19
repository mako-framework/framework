<?php

namespace mako\tests\unit\utility;

use mako\utility\Str;

/**
 * @group unit
 */

class StrTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	 public function testNl2br()
	 {
	 	$this->assertEquals('Hello<br>World!', Str::nl2br("Hello\nWorld!"));
	 	$this->assertEquals('Hello<br>World!', Str::nl2br("Hello\rWorld!"));
	 	$this->assertEquals('Hello<br>World!', Str::nl2br("Hello\n\rWorld!"));
	 	$this->assertEquals('Hello<br>World!', Str::nl2br("Hello\r\nWorld!"));

	 	$this->assertEquals('Hello<br />World!', Str::nl2br("Hello\nWorld!", true));
	 	$this->assertEquals('Hello<br />World!', Str::nl2br("Hello\rWorld!", true));
	 	$this->assertEquals('Hello<br />World!', Str::nl2br("Hello\n\rWorld!", true));
	 	$this->assertEquals('Hello<br />World!', Str::nl2br("Hello\r\nWorld!", true));
	 }

	 /**
	  *
	  */

	 public function testBr2nl()
	 {
	 	$this->assertEquals("Hello\nWorld!", Str::br2nl("Hello<br>World!"));
	 	$this->assertEquals("Hello\nWorld!", Str::br2nl("Hello<br/>World!"));
	 	$this->assertEquals("Hello\nWorld!", Str::br2nl("Hello<br />World!"));
	 }

	 /**
	  *
	  */

	 public function testPluralize()
	 {
	 	// Regex rules

	 	$this->assertEquals('apples', Str::pluralize('apple'));
	 	$this->assertEquals('quizzes', Str::pluralize('quiz'));
	 	$this->assertEquals('mice', Str::pluralize('mouse'));
	 	$this->assertEquals('slices', Str::pluralize('slice'));
	 	$this->assertEquals('beehives', Str::pluralize('beehive'));
	 	$this->assertEquals('wives', Str::pluralize('wife'));
	 	$this->assertEquals('thieves', Str::pluralize('thief'));
	 	$this->assertEquals('sheaves', Str::pluralize('sheaf'));
	 	$this->assertEquals('leaves', Str::pluralize('leaf'));
	 	$this->assertEquals('loaves', Str::pluralize('loaf'));
	 	$this->assertEquals('flies', Str::pluralize('fly'));
	 	$this->assertEquals('oases', Str::pluralize('oasis'));
	 	$this->assertEquals('tomatoes', Str::pluralize('tomato'));
	 	$this->assertEquals('potatoes', Str::pluralize('potato'));
	 	$this->assertEquals('echoes', Str::pluralize('echo'));
	 	$this->assertEquals('heroes', Str::pluralize('hero'));
	 	$this->assertEquals('vetoes', Str::pluralize('veto'));
	 	$this->assertEquals('buses', Str::pluralize('bus'));
	 	$this->assertEquals('octopi', Str::pluralize('octopus'));
	 	$this->assertEquals('viri', Str::pluralize('virus'));
	 	$this->assertEquals('axes', Str::pluralize('axis'));
	 	$this->assertEquals('pluses', Str::pluralize('plus'));
	 	$this->assertEquals('humans', Str::pluralize('human'));
	 	$this->assertEquals('men', Str::pluralize('man'));
	 	$this->assertEquals('women', Str::pluralize('woman'));

	 	// Irregulars

	 	$this->assertEquals('aliases', Str::pluralize('alias'));
	 	$this->assertEquals('audio', Str::pluralize('audio'));
	 	$this->assertEquals('children', Str::pluralize('child'));
	 	$this->assertEquals('deer', Str::pluralize('deer'));
	 	$this->assertEquals('equipment', Str::pluralize('equipment'));
	 	$this->assertEquals('fish', Str::pluralize('fish'));
	 	$this->assertEquals('feet', Str::pluralize('foot'));
	 	$this->assertEquals('geese', Str::pluralize('goose'));
	 	$this->assertEquals('gold', Str::pluralize('gold'));
	 	$this->assertEquals('information', Str::pluralize('information'));
	 	$this->assertEquals('money', Str::pluralize('money'));
	 	$this->assertEquals('oxen', Str::pluralize('ox'));
	 	$this->assertEquals('police', Str::pluralize('police'));
	 	$this->assertEquals('series', Str::pluralize('series'));
	 	$this->assertEquals('sexes', Str::pluralize('sex'));
	 	$this->assertEquals('sheep', Str::pluralize('sheep'));
	 	$this->assertEquals('species', Str::pluralize('species'));
	 	$this->assertEquals('teeth', Str::pluralize('tooth'));

	 	// Should not pluralize when number === 1

	 	$this->assertEquals('apple', Str::pluralize('apple', 1));
	 }

	 /**
	  *
	  */

	 public function testCamel2underscored()
	 {
	 	$this->assertEquals('hello_world', Str::camel2underscored('helloWorld'));
	 	$this->assertEquals('hello_world', Str::camel2underscored('HelloWorld'));
	 	$this->assertEquals('this_is_camel_case', Str::camel2underscored('thisIsCamelCase'));
	 	$this->assertEquals('this_is_camel_case', Str::camel2underscored('ThisIsCamelCase'));
	 }

	 /**
	  *
	  */

	 public function testUnderscored2camel()
	 {
	 	$this->assertEquals('helloWorld', Str::underscored2camel('hello_world'));
	 	$this->assertEquals('HelloWorld', Str::underscored2camel('hello_world', true));
	 	$this->assertEquals('thisIsUnderscored', Str::underscored2camel('this_is_underscored'));
	 	$this->assertEquals('ThisIsUnderscored', Str::underscored2camel('this_is_underscored', true));
	 }

	 /**
	  *
	  */

	 public function testLimitChars()
	 {
	 	$this->assertEquals(str_repeat('x', 50), Str::limitChars(str_repeat('x', 50)));
	 	$this->assertEquals(str_repeat('x', 100) . '...', Str::limitChars(str_repeat('x', 200)));
	 	$this->assertEquals(str_repeat('x', 40) . '...', Str::limitChars(str_repeat('x', 50), 40));
	 	$this->assertEquals(str_repeat('x', 40) . ',,,', Str::limitChars(str_repeat('x', 50), 40, ',,,'));
	 }

	 /**
	  *
	  */

	 public function testLimitWords()
	 {
	 	$this->assertEquals(trim(str_repeat('Hello ', 50)), Str::limitWords(trim(str_repeat('Hello ', 50))));
	 	$this->assertEquals(trim(str_repeat('Hello ', 100)) . '...', Str::limitWords(trim(str_repeat('Hello ', 200))));
	 	$this->assertEquals(trim(str_repeat('Hello ', 40)) . '...', Str::limitWords(trim(str_repeat('Hello ', 50)), 40));
	 	$this->assertEquals(trim(str_repeat('Hello ', 40)) . ',,,', Str::limitWords(trim(str_repeat('Hello ', 50)), 40, ',,,'));
	 }

	 /**
	  *
	  */

	 public function testSlug()
	 {
	 	$this->assertEquals('hello-world', Str::slug('hello world'));
	 	$this->assertEquals('hello-world', Str::slug('HELLO WORLD'));
	 	$this->assertEquals('hello-world', Str::slug('HELLO WORLD#'));
	 	$this->assertEquals('japanese-%E6%97%A5%E6%9C%AC%E8%AA%9E', Str::slug('Japanese 日本語'));
	 }

	 /**
	  *
	  */

	 public function testAscii()
	 {
	 	$this->assertEquals('hello', Str::ascii('hello'));
	 	$this->assertEquals('l', Str::ascii('øl'));
	 }

	 /**
	  *
	  */

	 public function testAlternator()
	 {
	 	$alternator = Str::alternator(['foo', 'bar']);

	 	$this->assertEquals('foo', $alternator());
	 	$this->assertEquals('bar', $alternator());
	 	$this->assertEquals('foo', $alternator());

	 	$alternator = Str::alternator(['foo', 'bar', 'baz']);

	 	$this->assertEquals('foo', $alternator());
	 	$this->assertEquals('bar', $alternator());
	 	$this->assertEquals('baz', $alternator());
	 	$this->assertEquals('foo', $alternator());
	 }

	 /**
	  *
	  */

	 public function testAutolink()
	 {
	 	$this->assertEquals('go to <a href="http://example.org">http://example.org</a>', Str::autolink('go to http://example.org'));
	 	$this->assertEquals('go to <a href="http://example.org">http://example.org</a>', Str::autolink('go to <a href="http://example.org">http://example.org</a>'));
	 	$this->assertEquals('go to <a href="http://example.org" class="foo">http://example.org</a>', Str::autolink('go to http://example.org', ['class' => 'foo']));
	 }

	 /**
	  *
	  */

	 public function testMask()
	 {
	 	$this->assertEquals('**llo', Str::mask('hello'));
	 	$this->assertEquals('**lle', Str::mask('kølle'));
	 	$this->assertEquals('********タジー', Str::mask('ファイナルファンタジー'));

	 	$this->assertEquals('***lo', Str::mask('hello', 2));
	 	$this->assertEquals('***le', Str::mask('kølle', 2));
	 	$this->assertEquals('*********ジー', Str::mask('ファイナルファンタジー', 2));

	 	$this->assertEquals('*****', Str::mask('hello', 0));
	 	$this->assertEquals('*****', Str::mask('kølle', 0));
	 	$this->assertEquals('***********', Str::mask('ファイナルファンタジー', 0));

	 	$this->assertEquals('xxxxx', Str::mask('hello', 0, 'x'));
	 	$this->assertEquals('xxxxx', Str::mask('kølle', 0, 'x'));
	 	$this->assertEquals('xxxxxxxxxxx', Str::mask('ファイナルファンタジー', 0, 'x'));
	 }

	 /**
	  *
	  */

	 public function testIncrement()
	 {
	 	$this->assertEquals('foo_1', Str::increment('foo'));
	 	$this->assertEquals('foo_2', Str::increment('foo_1'));

	 	$this->assertEquals('foo-1', Str::increment('foo', 1, '-'));
	 	$this->assertEquals('foo-2', Str::increment('foo-1', 1, '-'));

	 	$this->assertEquals('foo-10', Str::increment('foo', 10, '-'));
	 	$this->assertEquals('foo-11', Str::increment('foo-10', 10, '-'));
	 }

	 /**
	  *
	  */

	 public function testRandom()
	 {
	 	$this->assertEquals(32, mb_strlen(Str::random()));
	 	$this->assertEquals(16, mb_strlen(Str::random(Str::ALNUM, 16)));
	 }
}