<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use mako\pagination\Pagination;

/**
 * Query builder convenience methods.
 *
 * @author Frederic G. Østby
 */
trait QueryConvenienceTrait
{
	/**
	 * Adds a WHERE equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function eq($column, $value)
	{
		return $this->where($column, '=', $value);
	}

	/**
	 * Adds a WHERE not equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function notEq($column, $value)
	{
		return $this->where($column, '<>', $value);
	}

	/**
	 * Adds a WHERE less than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function lt($column, $value)
	{
		return $this->where($column, '<', $value);
	}

	/**
	 * Adds a WHERE less than or equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function lte($column, $value)
	{
		return $this->where($column, '<=', $value);
	}

	/**
	 * Adds a WHERE greater than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function gt($column, $value)
	{
		return $this->where($column, '>', $value);
	}

	/**
	 * Adds a WHERE greater than or requals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function gte($column, $value)
	{
		return $this->where($column, '>=', $value);
	}

	/**
	 * Adds a WHERE like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function like($column, $value)
	{
		return $this->where($column, 'LIKE', $value);
	}

	/**
	 * Adds a WHERE not like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function notLike($column, $value)
	{
		return $this->where($column, 'NOT LIKE', $value);
	}

	/**
	 * Adds a raw WHERE equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function eqRaw($column, $value)
	{
		return $this->whereRaw($column, '=', $value);
	}

	/**
	 * Adds a raw WHERE not equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function notEqRaw($column, $value)
	{
		return $this->whereRaw($column, '<>', $value);
	}

	/**
	 * Adds a raw WHERE less than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function ltRaw($column, $value)
	{
		return $this->whereRaw($column, '<', $value);
	}

	/**
	 * Adds a raw WHERE less than or equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function lteRaw($column, $value)
	{
		return $this->whereRaw($column, '<=', $value);
	}

	/**
	 * Adds a raw WHERE greater than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function gtRaw($column, $value)
	{
		return $this->whereRaw($column, '>', $value);
	}

	/**
	 * Adds a raw WHERE greater than or requals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function gteRaw($column, $value)
	{
		return $this->whereRaw($column, '>=', $value);
	}

	/**
	 * Adds a raw WHERE like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function likeRaw($column, $value)
	{
		return $this->whereRaw($column, 'LIKE', $value);
	}

	/**
	 * Adds a raw WHERE not like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function notLikeRaw($column, $value)
	{
		return $this->whereRaw($column, 'NOT LIKE', $value);
	}

	/**
	 * Adds a OR WHERE equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orEq($column, $value)
	{
		return $this->orWhere($column, '=', $value);
	}

	/**
	 * Adds a OR WHERE not equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orNotEq($column, $value)
	{
		return $this->orWhere($column, '<>', $value);
	}

	/**
	 * Adds a OR WHERE less than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orLt($column, $value)
	{
		return $this->orWhere($column, '<', $value);
	}

	/**
	 * Adds a OR WHERE less than or requals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orLte($column, $value)
	{
		return $this->orWhere($column, '<=', $value);
	}

	/**
	 * Adds a OR WHERE greater than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orGt($column, $value)
	{
		return $this->orWhere($column, '>', $value);
	}

	/**
	 * Adds a OR WHERE greater than or requals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orGte($column, $value)
	{
		return $this->orWhere($column, '>=', $value);
	}

	/**
	 * Adds a OR WHERE like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orLike($column, $value)
	{
		return $this->orWhere($column, 'LIKE', $value);
	}

	/**
	 * Adds a OR WHERE not like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orNotLike($column, $value)
	{
		return $this->orWhere($column, 'NOT LIKE', $value);
	}

	/**
	 * Adds a raw OR WHERE equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orEqRaw($column, $value)
	{
		return $this->orWhereRaw($column, '=', $value);
	}

	/**
	 * Adds a raw OR WHERE not equals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orNotEqRaw($column, $value)
	{
		return $this->orWhereRaw($column, '<>', $value);
	}

	/**
	 * Adds a raw OR WHERE less than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orLtRaw($column, $value)
	{
		return $this->orWhereRaw($column, '<', $value);
	}

	/**
	 * Adds a raw OR WHERE less than or requals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orLteRaw($column, $value)
	{
		return $this->orWhereRaw($column, '<=', $value);
	}

	/**
	 * Adds a raw OR WHERE greater than clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orGtRaw($column, $value)
	{
		return $this->orWhereRaw($column, '>', $value);
	}

	/**
	 * Adds a OR WHERE greater than or requals clause.
	 *
	 * @access public
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orGteRaw($column, $value)
	{
		return $this->orWhereRaw($column, '>=', $value);
	}

	/**
	 * Adds a raw OR WHERE like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orLikeRaw($column, $value)
	{
		return $this->orWhereRaw($column, 'LIKE', $value);
	}

	/**
	 * Adds a raw OR WHERE not like clause.
	 *
	 * @param  string                     $column Column name
	 * @param  mixed                      $value  Value
	 * @return \mako\database\query\Query
	 */
	public function orNotLikeRaw($column, $value)
	{
		return $this->orWhereRaw($column, 'NOT LIKE', $value);
	}
}
