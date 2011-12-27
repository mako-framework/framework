<?php

namespace mako\session
{
	use \PDO;
	use \PDOException;
	use \mako\Database as MDatabase;

	/**
	* Redis adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Database extends \mako\session\Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Database object.
		*/

		protected $db;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   array   Configuration
		*/

		public function __construct(array $config)
		{
			parent::__construct();

			$this->db = MDatabase::instance($config['configuration']);
		}

		/**
		* Destructor.
		*
		* @access  public
		*/

		public function __destruct()
		{
			session_write_close();

			// Fixes issue with Debian and Ubuntu session garbage collection

			if(mt_rand(1, 100) === 100)
			{
				$this->gc(0);
			}
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Returns session data.
		*
		* @access  public
		* @param   string  Session id
		* @return  string
		*/

		public function read($id)
		{
			try
			{
				$stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = :id");

				$stmt->bindParam(':id', $id, PDO::PARAM_STR);

				$stmt->execute();

				$session = $stmt->fetch();

				if($session !== false)
				{
					return $session->data;
				}
				else
				{
					return '';
				}
			}
			catch(PDOException $e)
			{
				return '';
			}
		}

		/**
		* Writes data to the session.
		*
		* @access  public
		* @param   string  Session id
		* @param   string  Session data
		*/

		public function write($id, $data)
		{
			try
			{
				$stmt = $this->db->prepare("SELECT id FROM sessions WHERE id = :id");

				$stmt->bindParam(':id', $id, PDO::PARAM_STR);

				$stmt->execute();

				if($stmt->rowCount() > 0)
				{
					$stmt = $this->db->prepare("UPDATE sessions SET data = :data, expires = :expires WHERE id = :id");

					$stmt->bindParam(':id', $id, PDO::PARAM_STR);
					$stmt->bindParam(':data', $data, PDO::PARAM_STR);
					$stmt->bindValue(':expires', (time() + $this->maxLifetime), PDO::PARAM_INT);

					$stmt->execute();
				}
				else
				{
					$stmt = $this->db->prepare("INSERT INTO sessions (id, data, expires) VALUES (:id, :data, :expires)");

					$stmt->bindParam(':id', $id, PDO::PARAM_STR);
					$stmt->bindParam(':data', $data, PDO::PARAM_STR);
					$stmt->bindValue(':expires', (time() + $this->maxLifetime), PDO::PARAM_INT);

					return $stmt->execute();
				}
			}
			catch(PDOException $e)
			{
				return false;
			}
		}

		/**
		* Destroys the session.
		*
		* @access  public
		* @param   string   Session id
		* @return  boolean
		*/

		public function destroy($id)
		{
			try
			{
				$stmt = $this->db->prepare("DELETE FROM sessions WHERE id = :id");

				$stmt->bindParam(':id', $id, PDO::PARAM_STR);

				$stmt->execute();

				return (bool) $stmt->rowCount();
			}
			catch(PDOException $e)
			{
				return false;
			}
		}

		/**
		* Garbage collector.
		*
		* @access  public
		* @param   int      Lifetime in secods
		* @return  boolean
		*/

		public function gc($maxLifetime)
		{
			try
			{
				$stmt = $this->db->prepare("DELETE FROM sessions WHERE expires < :expires");

				$stmt->bindValue(':expires', time(), PDO::PARAM_INT);

				return $stmt->execute();
			}
			catch(PDOException $e)
			{
				return false;
			}
		}
	}
}

/** -------------------- End of file --------------------**/