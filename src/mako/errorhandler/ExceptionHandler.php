<?php

namespace mako\errorhandler;

use \Exception;
use \mako\Log;
use \mako\View;
use \mako\Config;
use \mako\Request;
use \mako\Response;
use \mako\reactor\CLI;
use \Whoops\Run as Whoops;
use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;

/**
 * Exception handler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ExceptionHandler
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Exception.
	 * 
	 * @var Exception
	 */

	protected $exception;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   Exception  $exception  Exception to handle
	 */

	public function __construct(Exception $exception)
	{
		$this->exception = $exception;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Displays the error in a CLI friendly format.
	 * 
	 * @access  protected
	 */

	protected function displayCLI()
	{
		$cli = new CLI();

		$message = vsprintf('%s: %s in %s at line %s' . PHP_EOL . PHP_EOL . '%s', array
		(
			get_class($this->exception),
			$this->exception->getMessage(),
			$this->exception->getFile(),
			$this->exception->getLine(),
			$this->exception->getTraceAsString(),
		));

		$cli->stderr($message . PHP_EOL);
	}

	/**
	 * Displays the error in a web friendly format.
	 * 
	 * @access  protected
	 */

	protected function displayWeb()
	{
		$request = Request::main();

		$response = new Response();

		if(Config::get('application.error_handler.display_errors') === true)
		{
			$whoops = new Whoops();

			$whoops->allowQuit(false);

			$whoops->writeToOutput(false);

			if($request instanceof Request && $request->isAjax())
			{
				$handler = new JsonResponseHandler();

				$response->type('application/json');
			}
			else
			{
				$handler = new PrettyPageHandler();

				$handler->setResourcesPath(__DIR__ . '/resources');

				switch($editor = Config::get('application.error_handler.open_with'))
				{
					case 'emacs':
					case 'macvim':
					case 'sublime':
					case 'textmate':
					case 'xdebug':
						$handler->setEditor($editor);
					break;
					default:
						$handler->setEditor(function($file, $line) use ($editor) {
							return $editor;
						});
				}
			}

			$whoops->pushHandler($handler);

			$response->body($whoops->handleException($this->exception));
		}
		else
		{
			if($request instanceof Request && $request->isAjax())
			{
				$response->body(json_encode(array('error' => array
				(
					'type'    => 'Error',
					'message' => 'Aw, snap! An error has occurred while processing your request.',
					'file'    => null,
					'line'    => null,
				))));

				$response->type('application/json');
			}
			else
			{
				$response->body(new View('_mako_.errors.error'));
			}
		}

		$response->send(500);
	}

	/**
	 * Handles the exception.
	 * 
	 * @access  public
	 */

	public function handle()
	{
		// Display error

		if(strtolower(PHP_SAPI) === 'cli')
		{
			$this->displayCLI();
		}
		else
		{
			$this->displayWeb();	
		}

		// Write to error log

		if(Config::get('application.error_handler.log_errors') === true)
		{
			$message = vsprintf('%s: %s in %s at line %s', array
			(
				get_class($this->exception),
				$this->exception->getMessage(),
				$this->exception->getFile(),
				$this->exception->getLine(),
			));

			Log::error($message);
		}
	}
}

/** -------------------- End of file -------------------- **/