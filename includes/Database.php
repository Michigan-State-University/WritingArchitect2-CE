<?php
	class Database {		
		// TODO: Change the following values to match your database
		private $host = '127.0.0.1';		
		private $db_name = 'changeme';
		private $username = 'changeme';
		private $password = 'changeme';
		private $conn;
		
		// DBConnect
	public function connect()
	{
			$this->conn = null;
			
			try {
				//echo 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';Uid=' . $this->username . ';Pwd=' . $this->password;
			$this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Connections Error: ' . $e->getMessage();
			}
			return $this->conn;
		}
	}
