<?php
/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments;

use mako\cli\input\arguments\exceptions\ArgumentException;
use mako\cli\input\arguments\exceptions\InvalidArgumentException;
use mako\cli\input\arguments\exceptions\MissingArgumentException;
use mako\cli\input\arguments\exceptions\UnexpectedValueException;
use RuntimeException;

use function array_shift;
use function current;
use function explode;
use function preg_match;
use function strlen;
use function strpos;
use function substr;
use function vsprintf;

/**
 * Argument parser.
 *
 * @author Frederic G. Østby
 */
class ArgvParser
{
    /**
     * Regex that matches integers.
     *
     * @var string
     */
    const REGEX_INT = '/(^(\-?)0$)|(^(\-?)[1-9]\d*$)/';

    /**
     * Regex that matches floats.
     *
     * @var string
     */
    const REGEX_FLOAT = '/(^(\-?)0\.\d+$)|(^(\-?)[1-9]\d*\.\d+$)/';

    /**
     * Argv.
     *
     * @var array
     */
    protected $argv;

    /**
     * Arguments.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Map.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Positional arguments.
     *
     * @var array
     */
    protected $positionals = [];

    /**
     * Parsed arguments.
     *
     * @var array
     */
    protected $parsed = [];

    /**
     * Constructor.
     *
     * @param array $argv      Argv
     * @param array $arguments Array of arguments
     */
    public function __construct(array $argv, array $arguments = [])
    {
        $this->argv = $argv;

       $this->addArguments($arguments);
    }

    /**
     * Returns the registered arguments.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns an argument based on its name.
     *
     * @param  string   $name Argument name
     * @return Argument
     */
    protected function getArgument(string $name): Argument
    {
        if(!isset($this->map[$name]))
        {
            throw new InvalidArgumentException(vsprintf('Unknown argument [ %s ].', [$name]));
        }

        return $this->arguments[$this->map[$name]];
    }

    /**
     * Add argument.
     *
     * @param  Argument $argument Argument
     * @return void
     */
    public function addArgument(Argument $argument): void
    {
        $name = $argument->getName();

        $normalizedName = $argument->getNormalizedName();

        // Ensure that the argument name is unique

        if(isset($this->arguments[$normalizedName]))
        {
            throw new RuntimeException(vsprintf('Ambiguous argument name. [ %s ] will collide with [ %s ].', [$name, $this->arguments[$normalizedName]->getName()]));
        }

        // Check if the argument has an alias and that it's unique

        if(!empty($alias = $argument->getAlias()))
        {
            if(isset($this->map[$alias]))
            {
                throw new RuntimeException(vsprintf('Duplicate alias detected [ %s ]. The alias of [ %s ] will collide with the alias of [ %s ].', [$alias, $name, $this->getArgument($alias)->getName()]));
            }

            $this->map[$alias] = $normalizedName;
        }

        // Add to array of positionals if it's a positional argument

        if($argument->isPositional())
        {
            $this->positionals[] = $name;
        }

        // Add mapping and argument

        $this->map[$name] = $normalizedName;

        $this->arguments[$normalizedName] = $argument;

        // Reset the parsed array since we have added new arguments

        $this->parsed = [];
    }

    /**
     * Add arguments.
     *
     * @param  array $arguments Array of arguments
     * @return void
     */
    public function addArguments(array $arguments): void
    {
        foreach($arguments as $argument)
        {
            $this->addArgument($argument);
        }
    }

    /**
     * Casts the value to the desired type.
     *
     * @param  Argument              $argument Argument
     * @param  string|null           $token    Token
     * @param  string|bool           $value    Value
     * @return string|bool|float|int
     */
    protected function castValue(Argument $argument, ?string $token, $value)
    {
        if($argument->isInt())
        {
            if(preg_match(static::REGEX_INT, $value) !== 1)
            {
                throw new UnexpectedValueException(vsprintf('The [ %s ] argument expects an integer.', [$token ?? $argument->getName()]));
            }

            $value = (int) $value;
        }
        elseif($argument->isFloat())
        {
            if(preg_match(static::REGEX_FLOAT, $value) !== 1)
            {
                throw new UnexpectedValueException(vsprintf('The [ %s ] argument expects a float.', [$token ?? $argument->getName()]));
            }

            $value = (float) $value;
        }

        return $value;
    }

    /**
     * Store the value.
     *
     * @param  Argument    $argument Argument
     * @param  string|null $token    Token
     * @param  string|bool $value    Value
     * @return void
     */
    protected function storeValue(Argument $argument, ?string $token, $value): void
    {
        if($value === null)
        {
            throw new ArgumentException(vsprintf('Missing value for argument [ %s ].', [$token]));
        }

       $value = $this->castValue($argument, $token, $value);

        if($argument->isArray())
        {
            $this->parsed[$argument->getNormalizedName()][] = $value;
        }
        else
        {
            $this->parsed[$argument->getNormalizedName()] = $value;
        }
    }

    /**
     * Stores option values.
     *
     * @param  Argument    $argument Argument
     * @param  string      $token    Token
     * @param  string|null $value    Value
     * @param  array       $tokens   Remaining tokens
     * @return void
     */
    protected function storeOptionValue(Argument $argument, string $token, ?string $value, array &$tokens): void
    {
        if($argument->isBool())
        {
            if($value !== null)
            {
                throw new ArgumentException(vsprintf('The [ %s ] argument is a boolean and does not accept values.', [$token]));
            }

            $value = true;
        }
        else
        {
            if(($next = current($tokens)) !== false && strpos($next, '-') !== 0)
            {
                $value = array_shift($tokens);
            }
        }

        $this->storeValue($argument, $token, $value);
    }

    /**
     * Parses an option.
     *
     * @param  string $token  Token
     * @param  array  $tokens Remaining tokens
     * @return void
     */
    protected function parseOption(string $token, array &$tokens): void
    {
        $value = null;

        if(strpos($token, '=') !== false)
        {
            [$token, $value] = explode('=', $token, 2);
        }

        $this->storeOptionValue($this->getArgument($token), $token, $value, $tokens);
    }

    /**
     * Parses an alias.
     *
     * @param  string $token  Token
     * @param  array  $tokens Remaining tokens
     * @return void
     */
    protected function parseAlias(string $token, array &$tokens): void
    {
        $value = null;

        if(strlen($token) > 2)
        {
            [$token, $value] = [substr($token, 0, 2), substr($token, 2)];
		}

		$argument = $this->getArgument($token);

		if($value !== null && $argument->isBool())
		{
			$continue = $value;

			$value = null;
		}

		$this->storeOptionValue($argument, $token, $value, $tokens);

		if(isset($continue))
		{
			$this->parseAlias("-{$continue}", $tokens);
		}
    }

    /**
     * Parses a positional argument.
     *
     * @param  string $token       Token
     * @param  array  $positionals Remaining positional arguments
     * @param  array  $tokens      Remaining tokens
     * @return void
     */
    protected function parsePositional(string $token, array &$positionals, array &$tokens): void
    {
        if(empty($positionals))
        {
            throw new InvalidArgumentException('Unknown positional argument.');
        }

        $argument = $this->arguments[array_shift($positionals)];

        while(true)
        {
            $this->storeValue($argument, null, $token);

            if(!$argument->isArray() || ($next = current($tokens)) === false || strpos($next, '-') === 0)
            {
                break;
            }

            $token = array_shift($tokens);
        }
    }

    /**
     * Parses the arguments.
     *
     * @return array
     */
    public function parse(): array
    {
        if(empty($this->parsed))
        {
            // Parse input

            $tokens = $this->argv;

            $positionals = $this->positionals;

            while(($token = array_shift($tokens)) !== null)
            {
                if(strpos($token, '--') === 0)
                {
                    $this->parseOption($token, $tokens);
                }
                elseif(strpos($token, '-') === 0)
                {
                    $this->parseAlias($token, $tokens);
                }
                else
                {
                    $this->parsePositional($token, $positionals, $tokens);
                }
            }

            // Ensure that all required arguments are set

            foreach($this->arguments as $normalizedName => $argument)
            {
                if($argument->isBool() && !isset($this->parsed[$normalizedName]))
                {
                    $this->parsed[$normalizedName] = false;
                }
                elseif(!$argument->isOptional() && !isset($this->parsed[$normalizedName]))
                {
                    throw new MissingArgumentException(vsprintf('Missing required argument [ %s ].', [$argument->getName()]));
                }
            }
        }

        return $this->parsed;
    }
}
