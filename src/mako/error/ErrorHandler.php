<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error;

use Closure;
use ErrorException;
use mako\error\handlers\HandlerInterface;
use mako\http\exceptions\HttpStatusException;
use mako\syringe\Container;
use Psr\Log\LoggerInterface;
use Throwable;

use function array_unique;
use function array_unshift;
use function error_get_last;
use function error_log;
use function error_reporting;
use function filter_var;
use function fwrite;
use function headers_sent;
use function http_response_code;
use function in_array;
use function ini_get;
use function ob_end_clean;
use function ob_get_level;
use function register_shutdown_function;
use function set_exception_handler;
use function sprintf;

/**
 * Error handler.
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
	 * @var \Closure|\Psr\Log\LoggerInterface|null
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

		$this->handle(Throwable::class, $this->getFallbackHandler());

		// Registers the exception handler

		$this->register();
	}

	/**
	 * Should errors be displayed?
	 *
	 * @return bool
	 */
	protected function displayErrors(): bool
	{
		$displayErrors = ini_get('display_errors');

		if(in_array($displayErrors, ['stderr', 'stdout']) || filter_var($displayErrors, FILTER_VALIDATE_BOOLEAN))
		{
			return true;
		}

		return false;
	}

	/**
	 * Write to output.
	 *
	 * @param string $output String to write to output
	 */
	protected function write(string $output): void
	{
		if(PHP_SAPI === 'cli' && ini_get('display_errors') === 'stderr')
		{
			fwrite(STDERR, $output);

			return;
		}

		echo $output;
	}

	/**
	 * Returns the fallback handler.
	 *
	 * @return \Closure
	 */
	protected function getFallbackHandler(): Closure
	{
		return function (Throwable $e): void
		{
			if($this->displayErrors())
			{
				$this->write('[ ' . $e::class . "]  {$e->getMessage()} on line [ {$e->getLine()} ] in [ {$e->getFile()} ]" . PHP_EOL);

				$this->write($e->getTraceAsString() . PHP_EOL);
			}
		};
	}

	/**
	 * Registers the exception handler.
	 */
	protected function register(): void
	{
		// Allows us to handle "fatal" errors

		register_shutdown_function(function (): void
		{
			$e = error_get_last();

			if($e !== null && (error_reporting() & $e['type']) !== 0 && !$this->disableShutdownHandler)
			{
				$this->handler(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));
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
	 * @return \Psr\Log\LoggerInterface|null
	 */
	public function getLogger(): ?LoggerInterface
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
	 * @param array|string $exceptionType Exception type or array of exception types
	 */
	public function dontLog($exceptionType): void
	{
		$this->dontLog = array_unique([...$this->dontLog, ...(array) $exceptionType]);
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
	 * @param \Closure|string $handler       Exception handler
	 * @param array           $parameters    Constructor parameters
	 */
	public function handle(string $exceptionType, $handler, array $parameters = []): void
	{
		array_unshift($this->handlers, ['exceptionType' => $exceptionType, 'handler' => $handler, 'parameters' => $parameters]);
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
	 * @param  string                                $handler    Handler class name
	 * @param  array                                 $parameters Constructor parameters
	 * @return \mako\error\handlers\HandlerInterface
	 */
	protected function handlerFactory(string $handler, array $parameters): HandlerInterface
	{
		return $this->container->get($handler, $parameters);
	}

	/**
	 * Handle the exception.
	 *
	 * @param  \Throwable      $exception  Exceotion
	 * @param  \Closure|string $handler    Exception handler
	 * @param  array           $parameters Constructor parameters
	 * @return mixed
	 */
	protected function handleException(Throwable $exception, $handler, array $parameters)
	{
		if($handler instanceof Closure)
		{
			return $handler($exception);
		}

		return $this->handlerFactory($handler, $parameters)->handle($exception);
	}

	/**
	 * Logs the exception.
	 *
	 * @param \Throwable $exception An exception object
	 */
	protected function logException(Throwable $exception): void
	{
		if($this->shouldExceptionBeLogged($exception))
		{
			try
			{
				$this->getLogger()->error($exception->getMessage(), ['exception' => $exception]);
			}
			catch(Throwable $e)
			{
				error_log(sprintf('%s on line %s in %s.', $e->getMessage(), $e->getLine(), $e->getLine()));
			}
		}
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
					if($this->handleException($exception, $handler['handler'], $handler['parameters']) !== null)
					{
						break;
					}
				}
			}
		}
		catch(Throwable $e)
		{
			if((PHP_SAPI === 'cli' || headers_sent() === false) && $this->displayErrors())
			{
				if(PHP_SAPI !== 'cli')
				{
					http_response_code($exception instanceof HttpStatusException ? $exception->getCode() : 500);
				}

				// Empty output buffers

				$this->clearOutputBuffers();

				// One of the exception handlers failed so we'll just show the user a generic error screen

				$this->getFallbackHandler()($exception);

				// We'll also show some information about how the exception handler failed

				$this->write('Additionally, the error handler failed with the following error:' . PHP_EOL);

				$this->getFallbackHandler()($e);

				// And finally we'll log the additional exception

				$this->logException($e);
			}
		}
		finally
		{
			try
			{
				$this->logException($exception);
			}
			finally
			{
				exit(1);
			}
		}
	}
}
