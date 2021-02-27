<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\web;

use Closure;
use Doctrine\SqlFormatter\SqlFormatter;
use ErrorException;
use mako\application\Application;
use mako\database\ConnectionManager;
use mako\error\handlers\HandlerInterface;
use mako\file\FileSystem;
use mako\http\Request;
use mako\http\Response;
use mako\view\renderers\Template;
use mako\view\ViewFactory;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Throwable;

use function abs;
use function count;
use function fclose;
use function feof;
use function fgets;
use function fopen;
use function function_exists;
use function get_class;
use function is_readable;
use function json_encode;
use function simplexml_load_string;
use function str_repeat;
use function strpos;
use function sys_get_temp_dir;

/**
 * Development handler.
 */
class DevelopmentHandler extends Handler implements HandlerInterface
{
	/**
	 * Source padding.
	 *
	 * @var int
	 */
	const SOURCE_PADDING = 6;

	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * Response instance.
	 *
	 * @var \mako\http\Response
	 */
	protected $response;

	/**
	 * Application instance.
	 *
	 * @var \mako\application\Application
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request            $request  Request
	 * @param \mako\http\Response           $response Response
	 * @param \mako\application\Application $app      Application
	 */
	public function __construct(Request $request, Response $response, Application $app)
	{
		$this->request = $request;

		$this->response = $response;

		$this->app = $app;
	}

	/**
	 * Returns the exception type.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionType(Throwable $exception): string
	{
		if($exception instanceof ErrorException)
		{
			$type = get_class($exception);

			switch($exception->getCode())
			{
				case E_COMPILE_ERROR:
					$type .= ': Compile Error';
					break;
				case E_DEPRECATED:
				case E_USER_DEPRECATED:
					$type .= ': Deprecated';
					break;
				case E_NOTICE:
				case E_USER_NOTICE:
					$type .= ': Notice';
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$type .= ': Warning';
					break;
			}

			return $type;
		}

		return get_class($exception);
	}

	/**
	 * Return a JSON representation of the exception.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsJson(Throwable $exception): string
	{
		$details =
		[
			'type'    => $this->getExceptionType($exception),
			'code'    => $exception->getCode(),
			'message' => $exception->getMessage(),
			'file'    => $exception->getFile(),
			'line'    => $exception->getLine(),
		];

		return json_encode(['error' => $details]);
	}

	/**
	 * Return a XML representation of the exception.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsXml(Throwable $exception): string
	{
		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><error />");

		$xml->addChild('type', $this->getExceptionType($exception));

		$xml->addChild('code', $exception->getCode());

		$xml->addChild('message', $exception->getMessage());

		$xml->addChild('file', $exception->getFile());

		$xml->addChild('line', $exception->getLine());

		return $xml->asXML();
	}

	/**
	 * Returns the source code surrounding the error.
	 *
	 * @param  string     $file File path
	 * @param  int        $line Error line
	 * @return array|null
	 */
	protected function getSourceCode(string $file, int $line): ?array
	{
		if(!is_readable($file))
		{
			return null;
		}

		$handle      = fopen($file, 'r');
		$lines       = [];
		$currentLine = 0;

		while(!feof($handle))
		{
			if($currentLine++ > $line + static::SOURCE_PADDING)
			{
				break;
			}

			$sourceCode = fgets($handle);

			if($currentLine >= ($line - static::SOURCE_PADDING) && $currentLine <= ($line + static::SOURCE_PADDING))
			{
				$lines[$currentLine] = $sourceCode;
			}
		}

		fclose($handle);

		return $lines;
	}

	/**
	 * Returns an enhanced stack trace.
	 *
	 * @param  \Throwable $exception Exception
	 * @return array
	 */
	protected function getEnhancedStackTrace(Throwable $exception): array
	{
		$stackTrace = $exception->getTrace();

		$frameCount = count($stackTrace);

		$enhancedStackTrace = [];

		$foundFirstAppFrame = false;

		foreach($stackTrace as $key => $frame)
		{
			$key = abs($key - $frameCount);

			$enhancedStackTrace[$key] = $frame;

			$enhancedStackTrace[$key]['is_app'] = $enhancedStackTrace[$key]['is_internal'] = $enhancedStackTrace[$key]['open'] = false;

			if(!isset($frame['file']))
			{
				$enhancedStackTrace[$key]['is_internal'] = true;

				continue;
			}

			$enhancedStackTrace[$key]['code'] = $this->getSourceCode($frame['file'], $frame['line']);

			$enhancedStackTrace[$key]['is_app'] = strpos($frame['file'], $this->app->getPath()) === 0;

			if($foundFirstAppFrame === false && $enhancedStackTrace[$key]['is_app'] === true)
			{
				$enhancedStackTrace[$key]['open'] = $foundFirstAppFrame = true;
			}
		}

		return $enhancedStackTrace;
	}

	/**
	 * Returns a Symfony var-dumper closure.
	 *
	 * @return \Closure
	 */
	protected function getDumper(): Closure
	{
		$dumper = new HtmlDumper(null, null, HtmlDumper::DUMP_STRING_LENGTH);
		$cloner = new VarCloner;

		$dumper->setStyles
		([
			'default'   => 'background-color:transparent; color:#91CDA4; line-height:1.2em; font:14px Menlo, Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: normal',
			'num'       => 'font-weight:normal; color:#666666',
	        'const'     => 'font-weight:bold',
	        'str'       => 'font-weight:normal; color:#888888',
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

		$callable = new class
		{
			protected $dump;

			public function __invoke($line, $depth): void
			{
				if($depth >= 0)
				{
					$this->dump .= str_repeat('  ', $depth) . "{$line}\n";
				}
			}

			public function getDump(): ?string
			{
				try
				{
					return $this->dump;
				}
				finally
				{
					$this->dump = null;
				}

			}
		};

		return static function($value) use ($dumper, $cloner, $callable): ?string
		{
			$dumper->dump($cloner->cloneVar($value, Caster::EXCLUDE_VERBOSE), $callable);

			return $callable->getDump();
		};
	}

	/**
	 * Returns the database queries.
	 *
	 * @return array|null
	 */
	protected function getQueries(): ?array
	{
		if(!$this->app->getContainer()->has(ConnectionManager::class))
		{
			return null;
		}

		$formatter = new SqlFormatter;

		$groupedQueries = $this->app->getContainer()->get(ConnectionManager::class)->getLogs();

		foreach($groupedQueries as $connectionKey => $connectionQueries)
		{
			foreach($connectionQueries as $key => $query)
			{
				$groupedQueries[$connectionKey][$key]['query'] = $formatter->format($query['query']);
			}
		}

		return $groupedQueries;
	}

	/**
	 * Returns a view factory.
	 *
	 * @return \mako\view\ViewFactory
	 */
	protected function getViewFactory(): ViewFactory
	{
		$fileSystem = new FileSystem;

		$factory = new ViewFactory($fileSystem, '');

		$factory->extend('.tpl.php', function() use ($fileSystem)
		{
			return new Template($fileSystem, sys_get_temp_dir());
		});

		$factory->registerNamespace('mako-error', __DIR__ . '/views');

		return $factory;
	}

	/**
	 * Returns a rendered error view.
	 *
	 * @param  \Throwable $exception Exception
	 * @return string
	 */
	protected function getExceptionAsHtml(Throwable $exception): string
	{
		return $this->getViewFactory()->render('mako-error::development.error',
		[
			'type'         => $this->getExceptionType($exception),
			'code'         => $exception->getCode(),
			'message'      => $exception->getMessage(),
			'file'         => $exception->getFile(),
			'line'         => $exception->getLine(),
			'trace'        => $this->getEnhancedStackTrace($exception),
			'dump'         => $this->getDumper(),
			'queries'      => $this->getQueries(),
			'superglobals' =>
			[
				'_ENV'     => $_ENV ?? [],
				'_SERVER'  => $_SERVER ?? [],
				'_COOKIE'  => $_COOKIE ?? [],
				'_SESSION' => $_SESSION ?? [],
				'_GET'     => $_GET ?? [],
				'_POST'    => $_POST ?? [],
				'_FILES'   => $_FILES ?? [],
			],
		]);
	}

	/**
	 * Builds a response.
	 *
	 * @param  \Throwable $exception Exception
	 * @return array
	 */
	protected function buildResponse(Throwable $exception): array
	{
		if(function_exists('json_encode') && $this->respondWithJson())
		{
			return ['type' => 'application/json', 'body' => $this->getExceptionAsJson($exception)];
		}

		if(function_exists('simplexml_load_string') && $this->respondWithXml())
		{
			return ['type' => 'application/xml', 'body' => $this->getExceptionAsXml($exception)];
		}

		return ['type' => 'text/html', 'body' => $this->getExceptionAsHtml($exception)];
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(Throwable $exception)
	{
		['type' => $type, 'body' => $body] = $this->buildResponse($exception);

		$this->sendResponse($this->response
		->clear()
		->disableCaching()
		->disableCompression()
		->setType($type)
		->setBody($body)
		->setStatus($this->getStatusCode($exception)), $exception);

		return false;
	}
}
