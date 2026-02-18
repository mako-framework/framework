<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use Closure;
use Doctrine\SqlFormatter\HtmlHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use ErrorException;
use mako\application\Application;
use mako\database\ConnectionManager;
use mako\error\handlers\HandlerInterface;
use mako\error\handlers\hints\traits\HintTrait;
use mako\error\handlers\ProvidesExceptionIdInterface;
use mako\file\FileSystem;
use mako\http\Request;
use mako\http\Response;
use mako\view\renderers\Template;
use mako\view\ViewFactory;
use Override;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Tempest\Highlight\Highlighter;
use Throwable;

use function abs;
use function count;
use function fclose;
use function feof;
use function fgets;
use function fopen;
use function function_exists;
use function get_loaded_extensions;
use function is_readable;
use function json_encode;
use function natcasesort;
use function simplexml_load_string;
use function str_repeat;
use function str_starts_with;
use function sys_get_temp_dir;

/**
 * Development handler.
 */
class DevelopmentHandler extends Handler implements HandlerInterface, ProvidesExceptionIdInterface
{
	use HintTrait;

	/**
	 * Source padding.
	 */
	protected const int SOURCE_PADDING = 6;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected Response $response,
		protected Application $app,
		protected array $keep = []
	) {
		$this->exceptionId = $this->generateExceptionId();
	}

	/**
	 * Returns the exception type.
	 */
	protected function getExceptionType(Throwable $exception): string
	{
		if ($exception instanceof ErrorException) {
			return $exception::class . match ($exception->getCode()) {
				E_COMPILE_ERROR                 => ': Compile Error',
				E_DEPRECATED, E_USER_DEPRECATED => ': Deprecated',
				E_NOTICE, E_USER_NOTICE         => ': Notice',
				E_WARNING, E_USER_WARNING       => ': Warning',
				default                         => '',
			};
		}

		return $exception::class;
	}

	/**
	 * Return a JSON representation of the exception.
	 */
	protected function getExceptionAsJson(Throwable $exception): string
	{
		$details = [
			'type'         => $this->getExceptionType($exception),
			'code'         => $exception->getCode(),
			'message'      => $exception->getMessage(),
			'file'         => $exception->getFile(),
			'line'         => $exception->getLine(),
			'exception_id' => $this->exceptionId,
		];

		return json_encode(['error' => $details]);
	}

	/**
	 * Return a XML representation of the exception.
	 */
	protected function getExceptionAsXml(Throwable $exception): string
	{
		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><error />");

		$xml->addChild('type', $this->getExceptionType($exception));

		$xml->addChild('code', $exception->getCode());

		$xml->addChild('message', $exception->getMessage());

		$xml->addChild('file', $exception->getFile());

		$xml->addChild('line', (string) $exception->getLine());

		$xml->addChild('exception_id', $this->exceptionId);

		return $xml->asXML();
	}

	/**
	 * Returns the source code surrounding the error.
	 */
	protected function getSourceCode(string $file, int $line): ?array
	{
		if (!is_readable($file)) {
			return null;
		}

		$handle      = fopen($file, 'r');
		$lines       = [];
		$currentLine = 0;

		$highlighter = new Highlighter;

		while (!feof($handle)) {
			if ($currentLine++ > $line + static::SOURCE_PADDING) {
				break;
			}

			$sourceCode = fgets($handle);

			if ($currentLine >= ($line - static::SOURCE_PADDING) && $currentLine <= ($line + static::SOURCE_PADDING)) {
				$lines[$currentLine] = $highlighter->parse($sourceCode, 'php');
			}
		}

		fclose($handle);

		return $lines;
	}

	/**
	 * Returns an enhanced stack trace.
	 */
	protected function getEnhancedStackTrace(Throwable $exception): array
	{
		$stackTrace = $exception->getTrace();

		$frameCount = count($stackTrace);

		$enhancedStackTrace = [];

		$foundAppFrame = false;

		foreach ($stackTrace as $key => $frame) {
			$key = abs($key - $frameCount);

			$enhancedStackTrace[$key] = $frame;

			$enhancedStackTrace[$key]['is_error'] = $enhancedStackTrace[$key]['is_app'] = $enhancedStackTrace[$key]['is_internal'] = $enhancedStackTrace[$key]['open'] = false;

			if (!isset($frame['file'])) {
				$enhancedStackTrace[$key]['is_internal'] = true;

				continue;
			}

			$enhancedStackTrace[$key]['code'] = $this->getSourceCode($frame['file'], $frame['line']);

			$enhancedStackTrace[$key]['is_app'] = str_starts_with($frame['file'], $this->app->getPath());

			if ($foundAppFrame === false && $enhancedStackTrace[$key]['is_app'] === true) {
				$enhancedStackTrace[$key]['open'] = $foundAppFrame = true;
			}
		}

		return [
			'-' => [
				'class'       => $exception::class,
				'file'        => $exception->getFile(),
				'line'        => $exception->getLine(),
				'is_error'    => true,
				'is_app'      => false,
				'is_internal' => false,
				'open'        => $foundAppFrame === false,
				'code'        => $this->getSourceCode($exception->getFile(), $exception->getLine()),
			],
		] + $enhancedStackTrace;
	}

	/**
	 * Returns the previous exceptions.
	 */
	protected function getPreviousExceptions(Throwable $exception): array
	{
		$previousExceptions = [];

		while (($exception = $exception->getPrevious()) !== null) {
			$previousExceptions[] = [
				'type'    => $this->getExceptionType($exception),
				'file'    => $exception->getFile(),
				'line'    => $exception->getLine(),
				'code'    => $exception->getCode(),
				'message' => $exception->getMessage(),
			];
		}

		return $previousExceptions;
	}

	/**
	 * Returns a Symfony var-dumper closure.
	 */
	protected function getDumper(): Closure
	{
		$dumper = new HtmlDumper(null, $this->app->getCharset(), HtmlDumper::DUMP_STRING_LENGTH);
		$cloner = new VarCloner;

		$dumper->setStyles([
			'default'   => 'background-color:transparent;color:#91CDA4;line-height:1.2em;font:14px Menlo, Monaco, Consolas, monospace;word-wrap:break-word;white-space:pre-wrap;position:relative;z-index:99999;word-break:normal',
			'num'       => 'font-weight:normal;color:#666666',
	        'const'     => 'font-weight:bold',
	        'str'       => 'font-weight:normal;color:#888888',
	        'note'      => 'color:#666666',
	        'ref'       => 'color:#A0A0A0',
	        'public'    => 'color:#94A9A9',
	        'protected' => 'color:#94A9A9',
	        'private'   => 'color:#94A9A9',
	        'meta'      => 'color:#7B8D8D',
	        'key'       => 'color:#569771',
	        'index'     => 'color:#666666',
	        'ellipsis'  => 'color:#91CDA4',
		]);

		// We're using a callable to capture the output so that the generated javascript/css only gets printed once

		$callable = new class {
			protected $dump;

			public function __invoke($line, $depth): void
			{
				if ($depth >= 0) {
					$this->dump .= str_repeat('  ', $depth) . "{$line}\n";
				}
			}

			public function getDump(): ?string
			{
				try {
					return $this->dump;
				}
				finally {
					$this->dump = null;
				}

			}
		};

		return static function ($value) use ($dumper, $cloner, $callable): ?string {
			$dumper->dump($cloner->cloneVar($value, Caster::EXCLUDE_VERBOSE), $callable);

			return $callable->getDump();
		};
	}

	/**
	 * Returns the database queries.
	 */
	protected function getQueries(): ?array
	{
		if (!$this->app->getContainer()->has(ConnectionManager::class)) {
			return null;
		}

		$formatter = new SqlFormatter(new HtmlHighlighter([
			HtmlHighlighter::HIGHLIGHT_BACKTICK_QUOTE => 'style="color:#C678DD;"',
			HtmlHighlighter::HIGHLIGHT_COMMENT        => 'style="color:#5C6370"',
			HtmlHighlighter::HIGHLIGHT_NUMBER         => 'style="color:#D19A66;"',
			HtmlHighlighter::HIGHLIGHT_PRE            => 'style="padding:1rem;background-color:#383E49;color:#ABB2BF;border-radius:8px;"',
			HtmlHighlighter::HIGHLIGHT_QUOTE          => 'style="color:#98C379;"',
			HtmlHighlighter::HIGHLIGHT_VARIABLE       => 'style="color:#61AFEF;"',
			HtmlHighlighter::HIGHLIGHT_WORD           => '',
		]));

		$groupedQueries = $this->app->getContainer()->get(ConnectionManager::class)->getLogs();

		foreach ($groupedQueries as $connectionKey => $connectionQueries) {
			foreach ($connectionQueries as $key => $query) {
				$groupedQueries[$connectionKey][$key]['query'] = $formatter->format($query['query']);
			}
		}

		return $groupedQueries;
	}

	/**
	 * Returns a view factory.
	 */
	protected function getViewFactory(): ViewFactory
	{
		$fileSystem = new FileSystem;

		$factory = new ViewFactory($fileSystem, '', $this->app->getCharset());

		$factory->extend('.tpl.php', static fn () => new Template($fileSystem, sys_get_temp_dir()));

		$factory->registerNamespace('mako-error', __DIR__ . '/views');

		return $factory;
	}

	/**
	 * Returns a list of the loaded extensions.
	 */
	protected function getExtensions(): array
	{
		$extensions = get_loaded_extensions();

		natcasesort($extensions);

		return $extensions;
	}

	/**
	 * Returns a rendered error view.
	 */
	protected function getExceptionAsHtml(Throwable $exception): string
	{
		return $this->getViewFactory()->render('mako-error::development.error', [
			'type'         => $this->getExceptionType($exception),
			'code'         => $exception->getCode(),
			'message'      => $exception->getMessage(),
			'file'         => $exception->getFile(),
			'line'         => $exception->getLine(),
			'trace'        => $this->getEnhancedStackTrace($exception),
			'previous'     => $this->getPreviousExceptions($exception),
			'dump'         => $this->getDumper(),
			'queries'      => $this->getQueries(),
			'exception_id' => $this->exceptionId,
			'hint'         => $this->getHint($exception),
			'superglobals' => [
				'_ENV'     => $_ENV,
				'_SERVER'  => $_SERVER,
				'_COOKIE'  => $_COOKIE,
				'_SESSION' => $_SESSION ?? [],
				'_GET'     => $_GET,
				'_POST'    => $_POST,
				'_FILES'   => $_FILES,
			],
			'extensions'   => $this->getExtensions(),
		]);
	}

	/**
	 * Builds a response.
	 *
	 * @return array{type: string, body: string}
	 */
	protected function buildResponse(Throwable $exception): array
	{
		if (function_exists('json_encode') && $this->respondWithJson()) {
			return ['type' => 'application/json', 'body' => $this->getExceptionAsJson($exception)];
		}

		if (function_exists('simplexml_load_string') && $this->respondWithXml()) {
			return ['type' => 'application/xml', 'body' => $this->getExceptionAsXml($exception)];
		}

		return ['type' => 'text/html', 'body' => $this->getExceptionAsHtml($exception)];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function handle(Throwable $exception): mixed
	{
		['type' => $type, 'body' => $body] = $this->buildResponse($exception);

		$this->sendResponse($this->response
		->clearExcept($this->keep)
		->disableCaching()
		->disableCompression()
		->setType($type)
		->setBody($body)
		->setStatus($this->getHttpStatus($exception)), $exception);

		return false;
	}
}
