<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use Throwable;
use ErrorException;

use mako\error\handlers\Handler;
use mako\http\Request;
use mako\http\Response;
use mako\http\exceptions\RequestException;
use mako\http\exceptions\MethodNotAllowedException;
use mako\view\ViewFactory;

/**
 * Web handler.
 *
 * @author  Frederic G. Østby
 */
class WebHandler extends Handler
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
	 * View factory.
	 *
	 * @var \mako\view\ViewFactory
	 */
	protected $view;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \Throwable              $exception  Throwable
	 * @param   \mako\http\Request      $request    Request instance
	 * @param   \mako\http\Response     $response   Response intance
	 * @param   \mako\view\ViewFactory  $view       View factory instance
	 */
	public function __construct(Throwable $exception, Request $request, Response $response, ViewFactory $view)
	{
		parent::__construct($exception);

		$this->request = $request;

		$this->response = $response;

		$this->view = $view;

		$this->view->registerNamespace('mako-error', __DIR__ . '/views');
	}

	/**
	 * Should we return the error as JSON?
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function returnAsJson(): bool
	{
		$jsonMimeTypes = ['application/json', 'text/json'];

		if($this->request->isAjax() || in_array($this->response->getType(), $jsonMimeTypes))
		{
			return true;
		}

		$acceptableContentTypes = $this->request->acceptableContentTypes();

		if(isset($acceptableContentTypes[0]) && in_array($acceptableContentTypes[0], $jsonMimeTypes))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns the source code of a trace frame.
	 *
	 * @access  protected
	 * @param   string      $file     File path
	 * @param   int         $line     Frame line
	 * @return  bool|array
	 */
	protected function getFrameSource(string $file, int $line)
	{
		if(!is_readable($file))
		{
			return false;
		}

		$handle      = fopen($file, 'r');
		$lines       = [];
		$currentLine = 0;

		while(!feof($handle))
		{
			$currentLine++;

			$sourceCode = fgets($handle);

			if($currentLine > $line + static::SOURCE_PADDING)
			{
				break; // Exit loop after we have found what we were looking for
			}

			if($currentLine >= ($line - static::SOURCE_PADDING) && $currentLine <= ($line + static::SOURCE_PADDING))
			{
				$lines[] = $sourceCode;
			}
		}

		fclose($handle);

		return $lines;
	}

	/**
	 * Fixes the argument data and optionally ads source to each frame.
	 *
	 * @access  protected
	 * @param   array      $trace  Exception trace
	 * @return  array
	 */
	protected function modifyTrace(array $trace): array
	{
		foreach($trace as $frameKey => $frame)
		{
			// Makes the argument data web friendly

			if(!empty($frame['args']))
			{
				foreach($frame['args'] as $argumentKey => $argument)
				{
					ob_start();

					var_dump($argument);

					$trace[$frameKey]['args'][$argumentKey] = ob_get_clean();
				}
			}

			// Add source to the frame

			if(!empty($frame['file']) && !empty($frame['line']))
			{
				$trace[$frameKey]['source']         = $this->getFrameSource($frame['file'], $frame['line']);
				$trace[$frameKey]['source_padding'] = static::SOURCE_PADDING;
			}
		}

		return $trace;
	}

	/**
	 * Returns a detailed error page.
	 *
	 * @access  protected
	 * @param   bool       $returnAsJson  Should we return JSON?
	 * @return  string
	 */
	protected function getDetailedError(bool $returnAsJson): string
	{
		$data =
		[
			'type'    => $this->determineExceptionType($this->exception),
			'code'    => $this->exception->getCode(),
			'message' => $this->exception->getMessage(),
		];

		if($returnAsJson)
		{
			$data['trace'] = explode("\n", $this->exception->getTraceAsString());

			return json_encode($data);
		}
		else
		{
			$trace = $this->exception->getTrace();

			// Remove call to error handler from trace

			if($this->exception instanceof ErrorException)
			{
				$trace = array_slice($trace, 1);
			}

			// Add missing data to frame

			$trace[0]['file'] = $this->exception->getFile();
			$trace[0]['line'] = $this->exception->getLine();

			$data['trace'] = $this->modifyTrace($trace);

			// Is the var_dump data coming from xdebug?

			$data['xdebug'] = extension_loaded('xdebug') && ini_get('xdebug.overload_var_dump') !== '0' && ini_get('html_errors') !== '0';

			// Add superglobals

			$superGlobals =
			[
				'COOKIE'  => &$_COOKIE,
				'ENV'     => &$_ENV,
				'FILES'   => &$_FILES,
				'GET'     => &$_GET,
				'POST'    => &$_POST,
				'SERVER'  => &$_SERVER,
				'SESSION' => &$_SESSION,
			];

			// Return detailed error view

			return $this->view->render('mako-error::detailed', $data + ['superglobals' => $superGlobals, 'included_files' => get_included_files()]);
		}
	}

	/**
	 * Retruns a generic error page.
	 *
	 * @access  protected
	 * @param   bool       $returnAsJson  Should we return JSON?
	 * @return  string
	 */
	protected function getGenericError(bool $returnAsJson): string
	{
		$code = $this->exception->getCode();

		if($returnAsJson)
		{
			switch($code)
			{
				case 403:
					$message = 'You don\'t have permission to access the requested resource.';
					break;
				case 404:
					$message = 'The resource you requested could not be found. It may have been moved or deleted.';
					break;
				case 405:
					$message = 'The request method that was used is not supported by this resource.';
					break;
				default:
					$message = 'An error has occurred while processing your request.';
			}

			return json_encode(['message' => $message]);
		}
		else
		{
			$view = 'error';

			if($this->exception instanceof RequestException)
			{
				if($this->view->exists('mako-error::' . $code))
				{
					$view = $code;
				}
			}

			return $this->view->render('mako-error::' . $view);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(bool $showDetails = true)
	{
		$this->response->clear()->disableCaching()->disableCompression();

		// Set appropriate content type header

		if(($returnAsJson = $this->returnAsJson()) === true)
		{
			$this->response->type('application/json');
		}
		else
		{
			$this->response->type('text/html');
		}

		// Set the response body

		if($showDetails)
		{
			$this->response->body($this->getDetailedError($returnAsJson));
		}
		else
		{
			$this->response->body($this->getGenericError($returnAsJson));
		}

		// Send the response along with appropriate headers

		if($this->exception instanceof RequestException)
		{
			$status = $this->exception->getCode();

			if($this->exception instanceof MethodNotAllowedException)
			{
				$this->response->header('allows', implode(',', $this->exception->getAllowedMethods()));
			}
		}
		else
		{
			$status = 500;
		}

		$this->response->status($status)->send();

		// Return false to stop further error handling

		return false;
	}
}
