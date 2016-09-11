<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\view\compilers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\view\compilers\Template;

/**
 * @group unit
 */
class TemplateTest extends PHPUnit_Framework_TestCase
{
	protected $cachePath = '/cache';

	protected $templateName = 'template';

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
	public function getFileSystem($template, $compiled)
	{
		$fileSystem = Mockery::mock('mako\file\FileSystem');

		$fileSystem->shouldReceive('get')->with($this->templateName)->once()->andReturn($template);

		$fileSystem->shouldReceive('put')->with($this->cachePath . '/' . md5($this->templateName) . '.php', $compiled);

		return $fileSystem;
	}

	/**
	 *
	 */
	public function testVerbatim()
	{
		$template = '{% verbatim %}{{$hello}}{% endverbatim %}';

		$compiled = '{{$hello}}';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testComment()
	{
		$template = 'Hello,{# this is a comment #} world!';

		$compiled = 'Hello, world!';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testMultiLineComment()
	{
		$template = "Hello,{# this \n is \n a \n comment #} world!";

		$compiled = 'Hello, world!';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testExtends()
	{
		$template = "{% extends:'parent' %}\nHello, world!";

		$compiled = '<?php $__view__ = $__viewfactory__->create(\'parent\'); $__renderer__ = $__view__->getRenderer(); ?>
Hello, world!<?php echo $__view__->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testView()
	{
		$template = '{{view:\'foo\'}}';

		$compiled = '<?php echo $__viewfactory__->create(\'foo\', get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testViewVariable()
	{
		$template = '{{view:$foo}}';

		$compiled = '<?php echo $__viewfactory__->create($foo, get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testViewMethod()
	{
		$template = '{{view:$foo->bar()}}';

		$compiled = '<?php echo $__viewfactory__->create($foo->bar(), get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testViewMethodWithArguments()
	{
		$template = '{{view:$foo->bar(1, 2)}}';

		$compiled = '<?php echo $__viewfactory__->create($foo->bar(1, 2), get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testViewWithParameters()
	{
		$template = '{{view:\'foo\', [\'foo\' => \'bar\']}}';

		$compiled = '<?php echo $__viewfactory__->create(\'foo\', [\'foo\' => \'bar\'] + get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testViewMethodWithArgumentsWithParameters()
	{
		$template = '{{view:$foo->bar(1, 2), [\'foo\' => \'bar\']}}';

		$compiled = '<?php echo $__viewfactory__->create($foo->bar(1, 2), [\'foo\' => \'bar\'] + get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testBlockDefinition()
	{
		$template = '{% block:foo %}Hello, world!{% endblock %}';

		$compiled = '<?php $__renderer__->open(\'foo\'); ?>Hello, world!<?php $__renderer__->close(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testBlockOutput()
	{
		$template = '{{ block:foo }}Hello, world!{{ endblock }}';

		$compiled = '<?php $__renderer__->open(\'foo\'); ?>Hello, world!<?php $__renderer__->output(\'foo\'); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testControlStructures()
	{
		$template = '{% if(1 === 1) %}foo{% elseif(1 === 1) %}bar{% else if(1 === 1) %}baz{% else %}bax{% endif %}';

		$compiled = '<?php if(1 === 1): ?>foo<?php elseif(1 === 1): ?>bar<?php else if(1 === 1): ?>baz<?php else: ?>bax<?php endif; ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEcho()
	{
		$template = '{{$foo}}';

		$compiled = '<?php echo $this->escapeHTML($foo, $__charset__); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoRaw()
	{
		$template = '{{raw:$foo}}';

		$compiled = '<?php echo $foo; ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoPreserve()
	{
		$template = '{{preserve:$foo}}';

		$compiled = '<?php echo $this->escapeHTML($foo, $__charset__, false); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoJS()
	{
		$template = '{{js:$foo}}';

		$compiled = '<?php echo $this->escapeJavascript($foo, $__charset__); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoCSS()
	{
		$template = '{{css:$foo}}';

		$compiled = '<?php echo $this->escapeCSS($foo, $__charset__); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoAttribute()
	{
		$template = '{{attribute:$foo}}';

		$compiled = '<?php echo $this->escapeAttribute($foo, $__charset__); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoURL()
	{
		$template = '{{url:$foo}}';

		$compiled = '<?php echo $this->escapeURL($foo, $__charset__); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoEmptyElse()
	{
		$template = '{{$foo || \'bar\'}}';

		$compiled = '<?php echo $this->escapeHTML((!empty($foo) ? $foo : \'bar\'), $__charset__); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}
}