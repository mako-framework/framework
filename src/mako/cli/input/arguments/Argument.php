<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments;

use mako\cli\input\arguments\exceptions\ArgumentException;
use mako\utility\Str;

use function array_reverse;
use function explode;
use function is_array;
use function ltrim;
use function preg_match;
use function str_replace;
use function strpos;
use function vsprintf;

/**
 * Argument.
 */
class Argument
{
	/**
	 * Integer flag.
	 *
	 * @var int
	 */
	public const IS_INT = 2;

	/**
	 * Float flag.
	 *
	 * @var int
	 */
	public const IS_FLOAT = 4;

	/**
	 * Boolean flag.
	 *
	 * @var int
	 */
	public const IS_BOOL = 8;

	/**
	 * Array flag.
	 *
	 * @var int
	 */
	public const IS_ARRAY = 16;

	/**
	 * Optional flag.
	 *
	 * @var int
	 */
	public const IS_OPTIONAL = 32;

	/**
	 * Regex that matches allowed parameter names.
	 *
	 * @var string
	 */
	protected const NAME_REGEX = '/^(--)?(?!.*(--|__|-_|_-))[a-z][a-z0-9-_]+(?<!(-|_))$/i';

	/**
	 * Regex that matches allowed aliases.
	 *
	 * @var string
	 */
	protected const ALIAS_REGEX = '/^-[a-z]$/i';

	/**
	 * Argument alias.
	 *
	 * @var string|null
	 */
	protected $alias;

	/**
	 * Is the argument positional?
	 *
	 * @var bool
	 */
	protected $isPositional;

	/**
	 * Argument options.
	 *
	 * @var int
	 */
	protected $options;

	/**
	 * Default value.
	 *
	 * @var mixed
	 */
	protected $default;

	/**
	 * Constructor.
	 *
	 * @param array|string $name        Argument name
	 * @param string       $description Argument description
	 * @param int          $options     Argument options
	 * @param mixed        $default     Default return value (only used by optional arguments)
	 */
	public function __construct(
		protected array|string $name,
		protected string $description = '',
		int $options = 0,
		mixed $default = null
	)
	{
		[$name, $alias, $isPositional] = $this->parseName($this->name);

		$this->name = $name;

		$this->alias = $alias;

		$this->isPositional = $isPositional;

		$this->description = $description;

		$this->options = $this->getValidatedOptions($options);

		if($this->isBool() && !$this->isOptional())
		{
			$this->options |= static::IS_OPTIONAL;
		}

		$this->default = $default ?? ($this->isBool() ? false : ($this->isArray() ? [] : null));
	}

	/**
	 * Gets the default return value.
	 *
	 * @return mixed
	 */
	public function getDefaultValue(): mixed
	{
		return $this->default;
	}

	/**
	 * Returns a validated argument name.
	 *
	 * @param  string $name Argument name
	 * @return string
	 */
	protected function getValidatedName(string $name): string
	{
		if(preg_match(static::NAME_REGEX, $name) !== 1)
		{
			throw new ArgumentException(vsprintf('Invalid argument name [ %s ].', [$name]));
		}

		return $name;
	}

	/**
	 * Returns a validated alias.
	 *
	 * @param  string $alias Alias
	 * @return string
	 */
	protected function getValidatedAlias(string $alias): string
	{
		if(preg_match(static::ALIAS_REGEX, $alias) !== 1)
		{
			throw new ArgumentException(vsprintf('Invalid argument alias [ %s ].', [$alias]));
		}

		return $alias;
	}

	/**
	 * Parse the argument name.
	 *
	 * @param  array|string $name Argument name
	 * @return array
	 */
	protected function parseName(array|string $name): array
	{
		if(($isArray = is_array($name)) || strpos($name, '|') !== false)
		{
			[$name, $alias] = $isArray ? $name : array_reverse(explode('|', $name, 2));

			return [$this->getValidatedName($name), $this->getValidatedAlias($alias), false];
		}

		return [$this->getValidatedName($name), null, strpos($name, '-') === false];
	}

	/**
	 * Returns validated options.
	 *
	 * @param  int $options Argument options
	 * @return int
	 */
	protected function getValidatedOptions(int $options): int
	{
		if($this->isPositional && static::IS_BOOL === ($options & static::IS_BOOL))
		{
			throw new ArgumentException("Argument can't be both positional and a boolean flag.");
		}

		if(static::IS_BOOL === ($options & static::IS_BOOL) && static::IS_ARRAY === ($options & static::IS_ARRAY))
		{
			throw new ArgumentException("Argument can't be both a boolean flag and an array.");
		}

		if(static::IS_BOOL === ($options & static::IS_BOOL) && static::IS_INT === ($options & static::IS_INT))
		{
			throw new ArgumentException("Argument can't be both a boolean flag and an integer.");
		}

		if(static::IS_BOOL === ($options & static::IS_BOOL) && static::IS_FLOAT === ($options & static::IS_FLOAT))
		{
			throw new ArgumentException("Argument can't be both a boolean flag and a float.");
		}

		if(static::IS_FLOAT === ($options & static::IS_FLOAT) && static::IS_INT === ($options & static::IS_INT))
		{
			throw new ArgumentException("Argument can't be both a float and an integer.");
		}

		return $options;
	}

	/**
	 * Returns the argument name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns the normalized argument name.
	 *
	 * @return string
	 */
	public function getNormalizedName(): string
	{
		return Str::snakeToCamel(str_replace('-', '_', ltrim($this->name, '--')));
	}

	/**
	 * Returns the argument alias.
	 *
	 * @return string|null
	 */
	public function getAlias(): ?string
	{
		return $this->alias;
	}

	/**
	 * Returns the argument description.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * Is the argument positional?
	 *
	 * @return bool
	 */
	public function isPositional(): bool
	{
		return $this->isPositional;
	}

	/**
	 * Is the argument an integer?
	 *
	 * @return bool
	 */
	public function isInt(): bool
	{
		return static::IS_INT === ($this->options & static::IS_INT);
	}

	/**
	 * Is the argument a float?
	 *
	 * @return bool
	 */
	public function isFloat(): bool
	{
		return static::IS_FLOAT === ($this->options & static::IS_FLOAT);
	}

	/**
	 * Is the argument a boolean?
	 *
	 * @return bool
	 */
	public function isBool(): bool
	{
		return static::IS_BOOL === ($this->options & static::IS_BOOL);
	}

	/**
	 * Is the argument an array?
	 *
	 * @return bool
	 */
	public function isArray(): bool
	{
		return static::IS_ARRAY === ($this->options & static::IS_ARRAY);
	}

	/**
	 * Is the argument optional?
	 *
	 * @return bool
	 */
	public function isOptional(): bool
	{
		return static::IS_OPTIONAL === ($this->options & static::IS_OPTIONAL);
	}
}
