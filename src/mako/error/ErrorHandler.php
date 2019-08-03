<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error;

use Closure;
use ErrorException;
use mako\error\handlers\HandlerInterface;
use mako\syringe\Container;
use Psr\Log\LoggerInterface;
use Throwable;

use function array_merge;
use function array_unique;
use function array_unshift;
use function error_get_last;
use function error_reporting;
use function get_class;
use function ob_end_clean;
use function ob_get_level;
use function register_shutdown_function;
use function set_exception_handler;

/**
 * Error handler.
 *
 * @author Frederic G. Østby
 */
class ErrorHandler
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Is the shutdown handler disabled?
	 *
	 * @var bool
	 */
	protected $disableShutdownHandler = false;

	/**
	 * Exception handlers.
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * Logger instance.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Exception types that shouldn't be logged.
	 *
	 * @var array
	 */
	protected $dontLog = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(?Container $container = null)
	{
		$this->container = $container ?? new Container;

		// Add a basic exception handler to the stack

		$this->fallbackHandler();

		// Registers the exception handler

		$this->register();
	}

	/**
	 * Adds basic fallback handler to the stack.
	 */
	protected function fallbackHandler(): void
	{
		$this->handle(Throwable::class, function($e): void
		{
			echo '[ ' . get_class($e) . "]  {$e->getMessage()} on line [ {$e->getLine()} ] in [ {$e->getFile()} ]";

			echo PHP_EOL;

			echo $e->getTraceAsString();
		});
	}

	/**
	 * Registers the exception handler.
	 */
	protected function register(): void
	{
		// Allows us to handle "fatal" errors

		register_shutdown_function(function(): void
		{
			$e = error_get_last();

			if($e !== null && (error_reporting() & $e['type']) !== 0 && !$this->disableShutdownHandler)
			{
				$this->handler(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));

				exit(1);
			}
		});

		// Set the exception handler

		set_exception_handler([$this, 'handler']);
	}

	/**
	 * Set logger instance or closure that returns a logger instance.
	 *
	 * @param \Closure|\Psr\Log\LoggerInterface $logger Logger
	 */
	public function setLogger($logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * Return logger instance.
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	public function getLogger(): LoggerInterface
	{
		if($this->logger instanceof Closure)
		{
			return ($this->logger)();
		}

		return $this->logger;
	}

	/**
	 * Disables logging for an exception type.
	 *
	 * @param string|array $exceptionType Exception type or array of exception types
	 */
	public function dontLog($exceptionType): void
	{
		$this->dontLog = array_unique(array_merge($this->dontLog, (array) $exceptionType));
	}

	/**
	 * Disables logging for an exception type.
	 *
	 * @deprecated 7.0
	 * @param string|array $exceptionType Exception type or array of exception types
	 */
	public function disableLoggingFor($exceptionType): void
	{
		$this->dontLog($exceptionType);
	}

	/**
	 * Disables the shutdown handler.
	 */
	public function disableShutdownHandler(): void
	{
		$this->disableShutdownHandler = true;
	}

	/**
	 * Prepends an exception handler to the stack.
	 *
	 * @param string          $exceptionType Exception type
	 * @param string|\Closure $handler       Exception handler
	 */
	public function handle(string $exceptionType, $handler): void
	{
		array_unshift($this->handlers, ['exceptionType' => $exceptionType, 'handler' => $handler]);
	}

	/**
	 * Clears all error handlers for an exception type.
	 *
	 * @param string $exceptionType Exception type
	 */
	public function clearHandlers(string $exceptionType): void
	{
		foreach($this->handlers as $key => $handler)
		{
			if($handler['exceptionType'] === $exceptionType)
			{
				unset($this->handlers[$key]);
			}
		}
	}

	/**
	 * Replaces all error handlers for an exception type with a new one.
	 *
	 * @param string   $exceptionType Exception type
	 * @param \Closure $handler       Exception handler
	 */
	public function replaceHandlers(string $exceptionType, Closure $handler): void
	{
		$this->clearHandlers($exceptionType);

		$this->handle($exceptionType, $handler);
	}

	/**
	 * Clear output buffers.
	 */
	protected function clearOutputBuffers(): void
	{
		while(ob_get_level() > 0) ob_end_clean();
	}

	/**
	 * Should the exception be logged?
	 *
	 * @param  \Throwable $exception An exception object
	 * @return bool
	 */
	protected function shouldExceptionBeLogged(Throwable $exception): bool
	{
		if($this->logger === null)
		{
			return false;
		}

		foreach($this->dontLog as $exceptionType)
		{
			if($exception instanceof $exceptionType)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Creates and returns an error handler instance.
	 *
	 * @param  string                                $handler Handler class name
	 * @return \mako\error\handlers\HandlerInterface
	 */
	protected function handlerFactory(string $handler): HandlerInterface
	{
		return $this->container->get($handler);
	}

	/**
	 * Handle the exception.
	 *
	 * @param  string|\Closure $handler   Exception handler
	 * @param  \Throwable      $exception Exceotion
	 * @return mixed
	 */
	protected function handleException($handler, Throwable $exception)
	{
		if($handler instanceof Closure)
		{
			return $handler($exception);
		}

		return $this->handlerFactory($handler)->handle($exception);
	}

	/**
	 * Handles uncaught exceptions.
	 *
	 * @param \Throwable $exception An exception object
	 */
	public function handler(Throwable $exception): void
	{
		try
		{
			// Empty output buffers

			$this->clearOutputBuffers();

			// Loop through the exception handlers

			foreach($this->handlers as $handler)
			{
				if($exception instanceof $handler['exceptionType'])
				{
					if($this->handleException($handler['handler'], $exception) !== null)
					{
						break;
					}
				}
			}

			// Log exception

			if($this->shouldExceptionBeLogged($exception))
			{
				$this->getLogger()->error($exception->getMessage(), ['exception' => $exception]);
			}
		}
		catch(Throwable $e)
		{
			// Empty output buffers

			$this->clearOutputBuffers();

			// One of the exception handlers failed so we'll just show the user a generic error screen

			echo "{$e->getMessage()} on line [ {$e->getLine()} ] in [ {$e->getFile()} ]" . PHP_EOL;
		}

		exit(1);
	}
}
