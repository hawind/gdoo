<?php
class StiMySqlAdapter {
	private $connectionString = null;
	private $connectionInfo = null;
	private $link = null;
	
	private function getLastErrorResult() {
		if ($this->link->errno == 0) return StiResult::error("Unknown");
		return StiResult::error("[".$this->link->errno."] ".$this->link->error);
	}
	
	private function connect() {
		$this->link = new mysqli($this->connectionInfo->host, $this->connectionInfo->userId, $this->connectionInfo->password, $this->connectionInfo->database, $this->connectionInfo->port);
		if ($this->link->connect_error) return StiResult::error("[".$this->link->connect_errno."] ".$this->link->connect_error);
		if (!$this->link->set_charset($this->connectionInfo->charset)) return $this->getLastErrorResult();
		return StiResult::success();
	}
	
	private function disconnect() {
		if (!$this->link) return;
		$this->link->close();
	}
	
	public function parse($connectionString) {
		$info = new stdClass();
		$info->host = "";
		$info->port = 3306;
		$info->database = "";
		$info->userId = "";
		$info->password = "";
		$info->charset = "utf8";
		
		$parameters = explode(";", $connectionString);
		foreach($parameters as $parameter)
		{
			if (strpos($parameter, "=") < 1) continue;
			
			$spos = strpos($parameter, "=");
			$name = strtolower(trim(substr($parameter, 0, $spos)));
			$value = trim(substr($parameter, $spos + 1));
			
			switch ($name)
			{
				case "server":
				case "host":
				case "location":
					$info->host = $value;
					break;
						
				case "port":
					$info->port = $value;
					break;
						
				case "database":
				case "data source":
					$info->database = $value;
					break;
						
				case "uid":
				case "user":
				case "username":
				case "userid":
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
		switch ($meta->type) {
			// integer
			case 1:
			case 2:
			case 3:
			case 8:
			case 9:
				return 'int';
			
			// number (decimal)
			case 4:
			case 5:
			case 16:
			case 246:
				return 'number';
			
			// datetime
			case 7:
			case 10:
			case 11:
			case 12:
			case 13:
				return 'datetime';
			
			// array, string
			case 249:
			case 250:
			case 251:
			case 252:
			case 253:
			case 254:
				if ($meta->flags & 128) return 'array';
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
			$query = $this->link->query($queryString);
			if (!$query) return $this->getLastErrorResult();
			
			$result->types = array();
			$result->columns = array();
			$result->rows = array();
			
			while ($meta = $query->fetch_field()) {
				$result->columns[] = $meta->name;
				$result->types[] = $this->parseType($meta);
			}
			
			if ($query->num_rows > 0) {
				$isColumnsEmpty = count($result->columns) == 0;
				while ($rowItem = $query->fetch_assoc()) {
					$row = array();
					foreach ($rowItem as $key => $value) {
						if ($isColumnsEmpty && count($result->columns) < count($rowItem)) $result->columns[] = $key;
						$type = $result->types[count($row)];
						$row[] = ($type == 'array') ? base64_encode($value) : $value;
					}
					$result->rows[] = $row;
				}
			}
			
			$this->disconnect();
		}
		
		return $result;
	}
}