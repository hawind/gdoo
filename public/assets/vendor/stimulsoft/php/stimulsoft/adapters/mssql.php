<?php
class StiMsSqlAdapter {
	private $connectionString = null;
	private $connectionInfo = null;
	private $link = null;
	private $isMicrosoftDriver = false;
	
	private function getLastErrorResult() {
		$error = null;
		if ($this->isMicrosoftDriver) {
			if (($errors = sqlsrv_errors()) != null) {
				$error = $errors[count($errors) - 1];
				return StiResult::error("[".$error['code']."] ".$error['message']);
			}
		}
		else $error = mssql_get_last_message();
		
		if ($error) return StiResult::error($error);
		return StiResult::error("Unknown");
	}
	
	private function connect() {
		if ($this->isMicrosoftDriver) {
			if (!function_exists("sqlsrv_connect")) return StiResult::error("MS SQL driver not found. Please configure your PHP server to work with MS SQL.");
			$this->link = sqlsrv_connect(
					$this->connectionInfo->host, 
					array(
						"UID" => $this->connectionInfo->userId,
						"PWD" => $this->connectionInfo->password,
						"Database" => $this->connectionInfo->database,
						"LoginTimeout" => 10,
						"ReturnDatesAsStrings" => true,
						"CharacterSet" => $this->connectionInfo->charset
					));
			if (!$this->link) return $this->getLastErrorResult();
		}
		else {
			$this->link = mssql_connect($this->connectionInfo->host, $this->connectionInfo->userId, $this->connectionInfo->password);
			if (!$this->link) return $this->getLastErrorResult();
			$db = mssql_select_db($this->connectionInfo->database, $this->link);
			mssql_close($this->link);
			if (!$db) return $this->getLastErrorResult();
		}
		
		return StiResult::success();
	}
	
	private function disconnect() {
		if (!$this->link) return;
		$this->isMicrosoftDriver ? sqlsrv_close($this->link) : mssql_close($this->link);
	}
	
	public function parse($connectionString) {
		$info = new stdClass();
		$info->host = "";
		$info->database = "";
		$info->userId = "";
		$info->password = "";
		$info->charset = "UTF-8";
		
		$parameters = explode(";", $connectionString);
		foreach($parameters as $parameter) {
			if (strpos($parameter, "=") < 1) continue;
		
			$spos = strpos($parameter, "=");
			$name = strtolower(trim(substr($parameter, 0, $spos)));
			$value = trim(substr($parameter, $spos + 1));
			
			switch ($name) {
				case "server":
				case "data source":
					$info->host = $value;
					break;
						
				case "database":
				case "initial catalog":
					$info->database = $value;
					break;
						
				case "uid":
				case "user":
				case "user id":
					$info->userId = $value;
					break;
						
				case "pwd":
				case "password":
					$info->password = $value;
					break;
					
				case "charset":
					$info->charset = $value;
					break;
			}
		}
		
		$this->connectionString = $connectionString;
		$this->connectionInfo = $info;
	}
	
	private function parseType($meta) {
		switch ($meta["Type"]) {
			// integer
			case -6:
			case -5:
			case 4:
			case 5:
				return 'int';
			
			// number (decimal)
			case 2:
			case 3:
			case 6:
			case 7:
				return 'number';
			
			// datetime
			case -155:
			case -154:
			case -2:
			case 91:
			case 93:
				return 'datetime';
			
			// string
			case -152:
			case -10:
			case -9:
			case -8:
			case -1:
			case 1:
			case 12:
				return 'string';
		}
		
		// base64 array for unknown
		return 'array';
	}
	
	public function test() {
		$result = $this->connect();
		if ($result->success) $this->disconnect();
		return $result;
	}
	
	public function execute($queryString) {
		$result = $this->connect();
		if ($result->success) {
			$query = $this->isMicrosoftDriver ? sqlsrv_query($this->link, $queryString) : mssql_query($queryString, $this->link);
			if (!$query) return $this->getLastErrorResult();
			
			$result->types = array();
			$result->columns = array();
			$result->rows = array();
			
			if ($this->isMicrosoftDriver) {
				foreach (sqlsrv_field_metadata($query) as $meta) {
					$result->columns[] = $meta["Name"];
					$result->types[] = $this->parseType($meta);
				}
			}
			
			$isColumnsEmpty = count($result->columns) == 0;
			while ($rowItem = $this->isMicrosoftDriver ? sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC) : mssql_fetch_assoc($query)) {
				$row = array();
				foreach ($rowItem as $key => $value) {
					if ($isColumnsEmpty && count($result->columns) < count($rowItem)) $result->columns[] = $key;
					$row[] = $value;
				}
				$result->rows[] = $row;
			}
			$this->disconnect();
		}
	
		return $result;
	}
	
	function __construct() {
		$this->isMicrosoftDriver = !function_exists("mssql_connect");
	}
}