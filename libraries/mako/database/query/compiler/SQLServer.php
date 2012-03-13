<?php

namespace mako\database\query\compiler;

/**
* Compiles SQL Server queries.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class SQLServer extends \mako\database\query\Compiler
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Wrapper used to escape table and column names.
	*
	* @var string
	*/
	
	protected $wrapper = '[%s]';

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Compiles a SELECT query.
	*
	* @access  public
	* @return  string
	*/

	public function select()
	{
		if(empty($this->query->limit))
		{
			// No limit so we can just execute a normal query

			return parent::select();
		}
		else
		{

			if(empty($this->query->offset))
			{
				// No offset so we can just use the TOP clause

				$sql  = $this->query->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
				$sql .= 'TOP ' . $this->query->limit . ' ';
				$sql .= $this->columns($this->query->columns);
				$sql .= ' FROM ';
				$sql .= $this->wrap($this->query->table);
				$sql .= $this->joins($this->query->joins);
				$sql .= $this->wheres($this->query->wheres);
				$sql .= $this->groupings($this->query->groupings);
				$sql .= $this->orderings($this->query->orderings);
				$sql .= $this->havings($this->query->havings);
			}
			else
			{
				// There is an offset so we need to emulate the OFFSET clause with ANSI-SQL

				$order = $this->orderings($this->query->orderings);

				if(empty($order))
				{
					$order = 'ORDER BY (SELECT 0)';
				}

				$sql  = $this->query->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
				$sql .= $this->columns($this->query->columns);
				$sql .= ' FROM ';
				$sql .= $this->wrap($this->query->table);
				$sql .= $this->joins($this->query->joins);
				$sql .= $this->wheres($this->query->wheres);
				$sql .= $this->groupings($this->query->groupings);
				$sql .= $this->havings($this->query->havings);

                $limit  = $this->query->offset + $this->query->limit;
                $offset = $this->query->offset + 1;

				$sql = 'SELECT * FROM (SELECT ROW_NUMBER() OVER (' . $order . ') AS mako_rownum, ' . $sql . ') AS m1 WHERE mako_rownum BETWEEN ' . $offset . ' AND ' . $limit;
			}

			return array('sql' => $sql, 'params' => $this->params);
		}
	}
}

/** -------------------- End of file --------------------**/