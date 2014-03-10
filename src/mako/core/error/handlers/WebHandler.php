<?php

namespace mako\core\error\handlers;

use \Exception;
use \ErrorException;
use \mako\http\Request;
use \mako\http\Response;

/**
 * Web handler.
 * 
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class WebHandler extends \mako\core\error\handlers\Handler implements \mako\core\error\handlers\HandlerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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
	 * Response charset.
	 * 
	 * @var string
	 */

	protected $charset = 'UTF-8';

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the request instance.
	 * 
	 * @access  public
	 * @param   \mako\http\Request  $request  Request instance
	 */

	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Sets the response instance.
	 * 
	 * @access  public
	 * @param   \mako\http\Response  $response  Response instance
	 */

	public function setResponse(Response $response)
	{
		$this->response = $response;
	}

	/**
	 * Sets the response charset.
	 * 
	 * @param  string  $charset  Response charset
	 */

	public function setCharset($charset)
	{
		$this->charset = $charset;
	}

	/**
	 * Renders the error page.
	 * 
	 * @access  public
	 * @param   string  $__type__  Error type
	 * @param   array   $__data__  Error data
	 * @return  string
	 */

	protected function renderErrorPage($__type__, $__data__ = [])
	{
		extract($__data__, EXTR_REFS);
		
		ob_start();

		include(__DIR__ . '/resources/' . $__type__ . '.php');

		return ob_get_clean();
	}

	/**
	 * Returns the source code of a trace frame.
	 * 
	 * @access  protected
	 * @param   string     $file     File path
	 * @param   int        $line     Frame line
	 * @return  string
	 */

	protected function getFrameSource($file, $line)
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
				$lines[] = htmlspecialchars($sourceCode, ENT_QUOTES, $this->charset);
			}
		}

		fclose($handle);
		
		return $lines;
	}

	/**
	 * Fixes the argument data and optionally ads source to each frame.
	 * 
	 * @access  protected
	 * @param   array      $trace      Exception trace
	 * @param   boolean    $addSource  (optional) Add source code to each frame?
	 * @return  array
	 */

	protected function modifyTrace(array $trace, $addSource = true)
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

					$trace[$frameKey]['args'][$argumentKey] = htmlspecialchars(ob_get_clean(), ENT_QUOTES, $this->charset);
				}
			}

			// Add source to the frame

			if($addSource && !empty($frame['file']) && !empty($frame['line']))
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
	 * @param   boolean    $returnJSON  Should we return JSON?
	 * @return  string
	 */

	protected function getDetailedError($returnJSON)
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

		// Return the error details

		$data = 
		[
			'type'    => $this->determineExceptionType($this->exception),
			'code'    => $this->exception->getCode(),
			'message' => $this->exception->getMessage(),
			'trace'   => $this->modifyTrace($trace, !$returnJSON),
		];

		if($returnJSON)
		{
			return json_encode($data);
		}
		else
		{
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

			return $this->renderErrorPage('detailed', $data + ['charset' => $this->charset, 'superglobals' => $superGlobals]);
		}
	}

	/**
	 * Retruns a generic error page.
	 * 
	 * @access  protected
	 * @param   boolean    $returnJSON  Should we return JSON?
	 * @return  string
	 */

	public function getGenericError($returnJSON)
	{
		if($returnJSON)
		{
			return json_encode(['message' => 'An error has occurred while processing your request.']);
		}
		else
		{
			return $this->renderErrorPage('generic', ['charset' => $this->charset]);
		}
	}

	/**
	 * Handles the exception.
	 * 
	 * @access  public
	 * @param   boolean  $showDetails  (optional) Show error details?
	 * @return  boolean
	 */

	public function handle($showDetails = true)
	{
		// Should we return JSON?

		if(($returnJSON = $this->request->isAjax()) === true)
		{
			$this->response->type('application/json');
		}

		// Set the response body

		if($showDetails)
		{
			$this->response->body($this->getDetailedError($returnJSON));
		}
		else
		{
			$this->response->body($this->getGenericError($returnJSON));
		}

		// Send the response along with a status 500 error header

		$this->response->status(500)->send();

		// Return false to stop further error handling

		return false;
	}
}

/** -------------------- End of file -------------------- **/