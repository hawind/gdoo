<?php

class StiConnectionInfo {
	public $host = "";
	public $port = "";
	public $database = "";
	public $userId = "";
	public $password = "";
	public $charset = "";
	public $dsn = "";
	public $privilege = "";
	public $dataPath = "";
	public $schemaPath = "";
}

class StiSender {
	const Viewer = "Viewer";
	const Designer = "Designer";
}

class StiDatabaseType {
	const XML = "XML";
	const JSON = "JSON";
	const MySQL = "MySQL";
	const MSSQL = "MS SQL";
	const PostgreSQL = "PostgreSQL";
	const Firebird = "Firebird";
	const Oracle = "Oracle";
}

class StiEventType {
	const ExecuteQuery = "ExecuteQuery";
	const BeginProcessData = "BeginProcessData";
	//const EndProcessData = "EndProcessData";
	const CreateReport = "CreateReport";
	const OpenReport = "OpenReport";
	const SaveReport = "SaveReport";
	const SaveAsReport = "SaveAsReport";
	const PrintReport = "PrintReport";
	const BeginExportReport = "BeginExportReport";
	const EndExportReport = "EndExportReport";
	const EmailReport = "EmailReport";
	const DesignReport = "DesignReport";
}

class StiExportFormat {
	const Html = "Html";
	const Html5 = "Html5";
	const Pdf = "Pdf";
	const Excel2007 = "Excel2007";
	const Word2007 = "Word2007";
	const Csv = "Csv";
}

class StiRequest {
	public $sender = null;
	public $event = null;
	public $connectionString = null;
	public $queryString = null;
	public $database = null;
	public $report = null;
	public $data = null;
	public $fileName = null;
	public $format = null;
	public $settings = null;

	public function parse() {
		$data = file_get_contents("php://input");
		
		$obj = json_decode($data);
		if ($obj == null) return StiResult::error("JSON parser error");
		
		if (isset($obj->sender)) $this->sender = $obj->sender;
		if (isset($obj->command)) $this->event = $obj->command;
		if (isset($obj->event)) $this->event = $obj->event;
		if (isset($obj->connectionString)) $this->connectionString = $obj->connectionString;
		if (isset($obj->queryString)) $this->queryString = $obj->queryString;
		if (isset($obj->database)) $this->database = $obj->database;
		if (isset($obj->dataSource)) $this->dataSource = $obj->dataSource;
		if (isset($obj->connection)) $this->connection = $obj->connection;
		if (isset($obj->data)) $this->data = $obj->data;
		if (isset($obj->fileName)) $this->fileName = $obj->fileName;
		if (isset($obj->format)) $this->format = $obj->format;
		if (isset($obj->settings)) $this->settings = $obj->settings;
		if (isset($obj->report)) {
			$this->report = $obj->report;
			if (defined('JSON_UNESCAPED_SLASHES')) $this->reportJson = json_encode($this->report, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			else {
				// for PHP 5.3
				$this->reportJson = str_replace('\/', '/', json_encode($this->report));
				$this->reportJson = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
					return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
				}, $this->reportJson);
			}
		}
		
		return StiResult::success(null, $this);
	}
}

class StiResponse {
	public static function json($result, $exit = true) {
		unset($result->object);
		if (defined('JSON_UNESCAPED_SLASHES')) echo json_encode($result, JSON_UNESCAPED_SLASHES);
		else echo json_encode($result);
		if ($exit) exit;
	}
}

class StiResult {
	public $success = true;
	public $notice = null;
	public $object = null;

	public static function success($notice = null, $object = null) {
		$result = new StiResult();
		$result->success = true;
		$result->notice = $notice;
		$result->object = $object;
		return $result;
	}

	public static function error($notice = null) {
		$result = new StiResult();
		$result->success = false;
		$result->notice = $notice;
		return $result;
	}
}

class StiEmailSettings {
	/** Email address of the sender */
	public $from = null;

	/** Name and surname of the sender */
	public $name = "John Smith";

	/** Email address of the recipient */
	public $to = null;

	/** Email Subject */
	public $subject = null;

	/** Text of the Email */
	public $message = null;

	/** Attached file name */
	public $attachmentName = null;

	/** Charset for the message */
	public $charset = "UTF-8";

	/** Address of the SMTP server */
	public $host = null;

	/** Port of the SMTP server */
	public $port = 465;

	/** The secure connection prefix - ssl or tls */
	public $secure = "ssl";

	/** Login (Username or Email) */
	public $login = null;

	/** Password */
	public $password = null;
}

class StiDatabaseEventArgs {
	public $sender = null;
	public $database = null;
	public $connectionInfo = null;
	public $queryString = null;

	function __construct($sender, $database, $connectionInfo, $queryString = null) {
		$this->sender = $sender;
		$this->database = $database;
		$this->connectionInfo = $connectionInfo;
		$this->queryString = $queryString;
	}
}

class StiReportEventArgs {
	public $sender = null;
	public $report = null;

	function __construct($sender, $report = null) {
		$this->sender = $sender;
		$this->report = $report;
	}
}

class StiExportReportEventArgs {
	public $sender = null;
	public $settings = null;
	public $format = null;
	public $fileName = null;
	public $data = null;

	function __construct($settings, $format, $fileName, $data = null) {
		$this->settings = $settings;
		$this->format = $format;
		$this->fileName = $fileName;
		$this->data = $data;
	}
}

class StiSaveReportEventArgs {
	public $sender = null;
	public $report = null;
	public $fileName = null;

	function __construct($report, $fileName) {
		$this->report = $report;
		$this->fileName = $fileName;
	}
}

class StiDesignReportEventArgs {
	public $fileName = null;

	function __construct($fileName) {
		$this->fileName = $fileName;
	}
}