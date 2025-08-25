<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\view\compilers;

use mako\file\FileSystem;
use mako\tests\TestCase;
use mako\view\compilers\Template;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class TemplateTest extends TestCase
{
	protected $cachePath = '/cache';

	protected $templateName = 'template';

	/**
	 *
	 */
	public function getFileSystem($template, $compiled): FileSystem&MockInterface
	{
		$fileSystem = Mockery::mock(FileSystem::class);

		$fileSystem->shouldReceive('get')->with($this->templateName)->once()->andReturn($template);

		$fileSystem->shouldReceive('put')->with($this->cachePath . '/' . hash('xxh128', $this->templateName) . '.php', $compiled);

		return $fileSystem;
	}

	/**
	 *
	 */
	public function testVerbatim(): void
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
	public function testComment(): void
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
	public function testMultiLineComment(): void
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
	public function testExtends(): void
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
	public function testView(): void
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
	public function testCaptureWithPlainVariableName(): void
	{
		$template = '{% capture:foobar %}Hello{% endcapture %}';

		$compiled = '<?php ob_start(); ?>Hello<?php $foobar = ob_get_clean(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testCaptureWithDollarVariableName(): void
	{
		$template = '{% capture:$foobar %}Hello{% endcapture %}';

		$compiled = '<?php ob_start(); ?>Hello<?php $foobar = ob_get_clean(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testNospace(): void
	{
		$template = <<<'EOF'
		{% nospace %}
		<div>
			<span>hello, world!</span>
		</div>
		{% endnospace %}
		EOF;

		$compiled = '<div><span>hello, world!</span></div>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testNospaceBuffered(): void
	{
		$template = '{% nospace:buffered %}hello{% endnospace %}';

		$compiled = '<?php ob_start(); ?>hello<?php echo trim(preg_replace(\'/>\s+</\', \'><\', ob_get_clean())); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testViewVariable(): void
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
	public function testViewMethod(): void
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
	public function testViewMethodWithArguments(): void
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
	public function testViewWithParameters(): void
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
	public function testViewWithVariableParameters(): void
	{
		$template = '{{view:\'foo\', $foobar}}';

		$compiled = '<?php echo $__viewfactory__->create(\'foo\', $foobar + get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testViewMethodWithArgumentsWithParameters(): void
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
	public function testViewMethodWithArgumentsWithVariabbleParameters(): void
	{
		$template = '{{view:$foo->bar(1, 2), $foobar}}';

		$compiled = '<?php echo $__viewfactory__->create($foo->bar(1, 2), $foobar + get_defined_vars())->render(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testBlockDefinition(): void
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
	public function testBlockDefinitionWithQuotes(): void
	{
		$template = "{% block:'foo' %}Hello, world!{% endblock %}";

		$compiled = '<?php $__renderer__->open(\'foo\'); ?>Hello, world!<?php $__renderer__->close(); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testBlockOutput(): void
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
	public function testBlockOutputWithQuotes(): void
	{
		$template = "{{ block:'foo' }}Hello, world!{{ endblock }}";

		$compiled = '<?php $__renderer__->open(\'foo\'); ?>Hello, world!<?php $__renderer__->output(\'foo\'); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testControlStructures(): void
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
	public function testEcho(): void
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
	public function testEchoRaw(): void
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
	public function testEchoPreserve(): void
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
	public function testEchoJS(): void
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
	public function testEchoCSS(): void
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
	public function testEchoAttribute(): void
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
	public function testEchoURL(): void
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
	public function testEchoEmptyElseWithDefault(): void
	{
		$template = '{{$foo, default: \'bar\'}}';

		$compiled = '<?php echo $this->escapeHTML((empty($foo) ? (isset($foo) && ($foo === 0 || $foo === 0.0 || $foo === \'0\') ? $foo : \'bar\') : $foo), $__charset__); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}

	/**
	 *
	 */
	public function testEchoRawEmptyElseWithDefault(): void
	{
		$template = '{{raw: $foo, default: \'bar\'}}';

		$compiled = '<?php echo (empty($foo) ? (isset($foo) && ($foo === 0 || $foo === 0.0 || $foo === \'0\') ? $foo : \'bar\') : $foo); ?>';

		//

		$fileSystem = $this->getFileSystem($template, $compiled);

		//

		(new Template($fileSystem, $this->cachePath, $this->templateName))->compile();
	}
}
