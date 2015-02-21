<?php

namespace mako\tests\unit\utility;

use mako\utility\HTML;

/**
 * @group unit
 */

class HTMLTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testTag()
	{
		$html = new HTML;

		$this->assertEquals('<br>', $html->tag('br'));

		$this->assertEquals('<input disabled="disabled">', $html->tag('input', ['disabled']));

		$this->assertEquals('<p>hello</p>', $html->tag('p', [], 'hello'));

		$this->assertEquals('<p class="foo" id="bar">hello</p>', $html->tag('p', ['class' => 'foo', 'id' => 'bar'], 'hello'));

		//

		$html = new HTML(true);

		$this->assertEquals('<br />', $html->tag('br'));

		$this->assertEquals('<input disabled="disabled" />', $html->tag('input', ['disabled']));

		$this->assertEquals('<p>hello</p>', $html->tag('p', [], 'hello'));

		$this->assertEquals('<p class="foo" id="bar">hello</p>', $html->tag('p', ['class' => 'foo', 'id' => 'bar'], 'hello'));
	}

	/**
	 *
	 */

	public function testAudio()
	{
		$html = new HTML;

		$this->assertEquals('<audio><source src="foo.ogg"></audio>', $html->audio(['foo.ogg']));

		$this->assertEquals('<audio><source src="foo.ogg"><source src="foo.mp3"></audio>', $html->audio(['foo.ogg', 'foo.mp3']));

		$this->assertEquals('<audio class="foo"><source src="foo.ogg"></audio>', $html->audio(['foo.ogg'], ['class' => 'foo']));

		$this->assertEquals('<audio class="foo"><source src="foo.ogg"><source src="foo.mp3"></audio>', $html->audio(['foo.ogg', 'foo.mp3'], ['class' => 'foo']));

		//

		$html = new HTML(true);

		$this->assertEquals('<audio><source src="foo.ogg" /></audio>', $html->audio(['foo.ogg']));

		$this->assertEquals('<audio><source src="foo.ogg" /><source src="foo.mp3" /></audio>', $html->audio(['foo.ogg', 'foo.mp3']));

		$this->assertEquals('<audio class="foo"><source src="foo.ogg" /></audio>', $html->audio(['foo.ogg'], ['class' => 'foo']));

		$this->assertEquals('<audio class="foo"><source src="foo.ogg" /><source src="foo.mp3" /></audio>', $html->audio(['foo.ogg', 'foo.mp3'], ['class' => 'foo']));
	}

	/**
	 *
	 */

	public function testVideo()
	{
		$html = new HTML;

		$this->assertEquals('<video><source src="foo.mp4"></video>', $html->video(['foo.mp4']));

		$this->assertEquals('<video><source src="foo.mp4"><source src="foo.ogg"></video>', $html->video(['foo.mp4', 'foo.ogg']));

		$this->assertEquals('<video class="foo"><source src="foo.mp4"></video>', $html->video(['foo.mp4'], ['class' => 'foo']));

		$this->assertEquals('<video class="foo"><source src="foo.mp4"><source src="foo.ogg"></video>', $html->video(['foo.mp4', 'foo.ogg'], ['class' => 'foo']));

		//

		$html = new HTML(true);

		$this->assertEquals('<video><source src="foo.mp4" /></video>', $html->video(['foo.mp4']));

		$this->assertEquals('<video><source src="foo.mp4" /><source src="foo.ogg" /></video>', $html->video(['foo.mp4', 'foo.ogg']));

		$this->assertEquals('<video class="foo"><source src="foo.mp4" /></video>', $html->video(['foo.mp4'], ['class' => 'foo']));

		$this->assertEquals('<video class="foo"><source src="foo.mp4" /><source src="foo.ogg" /></video>', $html->video(['foo.mp4', 'foo.ogg'], ['class' => 'foo']));
	}

	/**
	 *
	 */

	public function testUl()
	{
		$html = new HTML;

		$this->assertEquals('<ul><li>hello</li></ul>', $html->ul(['hello']));

		$this->assertEquals('<ul class="foo"><li>hello</li></ul>', $html->ul(['hello'], ['class' => 'foo']));

		$this->assertEquals('<ul><li>hello</li><li>world</li></ul>', $html->ul(['hello', 'world']));

		$this->assertEquals('<ul><li>hello</li><li><ul><li>world</li></ul></li></ul>', $html->ul(['hello', ['world']]));

		//

		$html = new HTML(true);

		$this->assertEquals('<ul><li>hello</li></ul>', $html->ul(['hello']));

		$this->assertEquals('<ul class="foo"><li>hello</li></ul>', $html->ul(['hello'], ['class' => 'foo']));

		$this->assertEquals('<ul><li>hello</li><li>world</li></ul>', $html->ul(['hello', 'world']));

		$this->assertEquals('<ul><li>hello</li><li><ul><li>world</li></ul></li></ul>', $html->ul(['hello', ['world']]));
	}

	/**
	 *
	 */

	public function testOl()
	{
		$html = new HTML;

		$this->assertEquals('<ol><li>hello</li></ol>', $html->ol(['hello']));

		$this->assertEquals('<ol class="foo"><li>hello</li></ol>', $html->ol(['hello'], ['class' => 'foo']));

		$this->assertEquals('<ol><li>hello</li><li>world</li></ol>', $html->ol(['hello', 'world']));

		$this->assertEquals('<ol><li>hello</li><li><ol><li>world</li></ol></li></ol>', $html->ol(['hello', ['world']]));

		//

		$html = new HTML(true);

		$this->assertEquals('<ol><li>hello</li></ol>', $html->ol(['hello']));

		$this->assertEquals('<ol class="foo"><li>hello</li></ol>', $html->ol(['hello'], ['class' => 'foo']));

		$this->assertEquals('<ol><li>hello</li><li>world</li></ol>', $html->ol(['hello', 'world']));

		$this->assertEquals('<ol><li>hello</li><li><ol><li>world</li></ol></li></ol>', $html->ol(['hello', ['world']]));
	}

	/**
	 *
	 */

	public function testCustom()
	{
		HTML::registerTag('foo', function($html, $content = null, $attributes = [])
		{
			return $html->tag('foo', $attributes, $content);
		});

		$html = new HTML;

		$this->assertEquals('<foo>', $html->foo());

		$this->assertEquals('<foo>hello</foo>', $html->foo('hello'));

		$this->assertEquals('<foo class="foo">hello</foo>', $html->foo('hello', ['class' => 'foo']));

		//

		$html = new HTML(true);

		$this->assertEquals('<foo />', $html->foo());

		$this->assertEquals('<foo>hello</foo>', $html->foo('hello'));

		$this->assertEquals('<foo class="foo">hello</foo>', $html->foo('hello', ['class' => 'foo']));
	}

	/**
	 * @expectedException \BadMethodCallException
	 */

	public function testException()
	{
		$html = new HTML;

		$html->bar();
	}
}