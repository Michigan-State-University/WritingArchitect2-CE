<?php
	class Database {		
		// TODO: Change the following values to match your database
		private $host = '127.0.0.1';
		private $db_name = 'changeme';
		private $username = 'changeme';
		private $password = 'changeme';
		private $conn;

		public function __construct()
		{
			$this->host = $this->env_or_default('WA_DB_HOST', $this->host);
			$this->db_name = $this->env_or_default('WA_DB_NAME', $this->db_name);
			$this->username = $this->env_or_default('WA_DB_USERNAME', $this->username);
			$this->password = $this->env_or_default('WA_DB_PASSWORD', $this->password);
		}

		private function env_or_default($name, $default)
		{
			$value = getenv($name);
			return $value === false || $value === '' ? $default : $value;
		}
		
		// DBConnect
	public function connect()
	{
			$this->conn = null;
			
			try {
				//echo 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';Uid=' . $this->username . ';Pwd=' . $this->password;
			$this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4', $this->username, $this->password);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Connections Error: ' . $e->getMessage();
			}
			return $this->conn;
		}
	}
