<?php
namespace Ruckbeard\Database;

use Mysqli;

class Database extends Execute
{
    //Constants muse be defined by the user for this to work, preferably in a seperate file
    public $host = DB_HOST;
    public $username = DB_USER;
	public $password = DB_PASS;
	public $db_name = DB_NAME;
	
	public $link;
	public $error;
	
	public $execute;
	
	public function __construct() 
    {
		$this->connect();
	}
	
	/**
	 * @brief Connect to the database.
	 * @return If the connection fails, output the error and return false.
	 */
	private function connect() 
    {
        $this->link = new Mysqli($this->host, $this->username, $this->password, $this->db_name);
		
		if (!$this->link) {
			$this->error = "Connection Failed: " . $this->link->connect_error;
			return false;
		}
	}
}

