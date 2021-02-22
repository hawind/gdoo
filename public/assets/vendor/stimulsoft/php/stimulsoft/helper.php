<?php
require_once 'classes.php';
require_once 'adapters/mysql.php';
require_once 'adapters/mssql.php';

function stiErrorHandler($errNo, $errStr, $errFile, $errLine) {
	$result = StiResult::error("[".$errNo."] ".$errStr." (".$errFile.", Line ".$errLine.")");
	StiResponse::json($result);
}

function stiShutdownFunction() {
	$err = error_get_last();
	if ($err != null && (($err["type"] & E_COMPILE_ERROR) || ($err["type"] & E_ERROR) || ($err["type"] & E_CORE_ERROR) || ($err["type"] & E_RECOVERABLE_ERROR))) {
		$result = StiResult::error("[".$err["type"]."] ".$err["message"]." (".$err["file"].", Line ".$err["line"].")");
		StiResponse::json($result);
	}
}

class StiHandler {
	
	private function checkEventResult($event, $args) {
		if (isset($event)) $result = $event($args);
		if (!isset($result)) $result = StiResult::success();
		if ($result === true) return StiResult::success();
		if ($result === false) return StiResult::error();
		if (gettype($result) == "string") return StiResult::error($result);
		if (isset($args)) $result->object = $args;
		return $result;
	}
	
	private function getQueryParameters($query) {
		$result = array();
		while (strpos($query, "{") !== false) {
			$query = substr($query, strpos($query, "{") + 1);
			$parameterName = substr($query, 0, strpos($query, "}"));
			$result[$parameterName] = null;
		}
		
		return $result;
	}
	
	private function applyQueryParameters($query, $values) {
		$result = "";
		while (strpos($query, "{") !== false) {
			$result .= substr($query, 0, strpos($query, "{"));
			$query = substr($query, strpos($query, "{") + 1);
			$parameterName = substr($query, 0, strpos($query, "}"));
			if (isset($values) && isset($values[$parameterName]) && !is_null($values[$parameterName])) $result .= strval($values[$parameterName]);
			else $result .= "{".$parameterName."}";
			$query = substr($query, strpos($query, "}") + 1);
		}
		
		return $result.$query;
	}
	
//--- Events

	public $onBeginProcessData = null;
	private function invokeBeginProcessData($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->database = $request->database;
		$args->connectionString = isset($request->connectionString) ? base64_decode(str_rot13($request->connectionString)) : null;
		$args->queryString = isset($request->queryString) ? base64_decode(str_rot13($request->queryString)) : null;
		$args->dataSource = isset($request->dataSource) ? $request->dataSource : null;
		$args->connection = isset($request->connection) ? $request->connection : null;
		if (isset($request->queryString)) $args->parameters = $this->getQueryParameters($request->queryString);
		
		$result = $this->checkEventResult($this->onBeginProcessData, $args);
		if (isset($result->object->queryString) && isset($args->parameters)) $result->object->queryString = $this->applyQueryParameters($result->object->queryString, $args->parameters);
		return $result;
	}
	
	public $onEndProcessData = null;
	private function invokeEndProcessData($request, $result) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->result = $result;
		return $this->checkEventResult($this->onEndProcessData, $args);
	}
	
	public $onCreateReport = null;
	private function invokeCreateReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		return $this->checkEventResult($this->onCreateReport, $args);
	}
	
	public $onOpenReport = null;
	private function invokeOpenReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		return $this->checkEventResult($this->onOpenReport, $args);
	}
	
	public $onSaveReport = null;
	private function invokeSaveReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->report = $request->report;
		$args->reportJson = $request->reportJson;
		$args->fileName = $request->fileName;
		return $this->checkEventResult($this->onSaveReport, $args);
	}
	
	public $onSaveAsReport = null;
	private function invokeSaveAsReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->report = $request->report;
		$args->reportJson = $request->reportJson;
		$args->fileName = $request->fileName;
		return $this->checkEventResult($this->onSaveAsReport, $args);
	}
	
	public $onPrintReport = null;
	private function invokePrintReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->fileName = $request->fileName;
		return $this->checkEventResult($this->onPrintReport, $args);
	}
	
	public $onBeginExportReport = null;
	private function invokeBeginExportReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->settings = $request->settings;
		$args->format = $request->format;
		$args->fileName = $request->fileName;
		return $this->checkEventResult($this->onBeginExportReport, $args);
	}
	
	public $onEndExportReport = null;
	private function invokeEndExportReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->format = $request->format;
		$args->fileName = $request->fileName;
		$args->data = $request->data;
		return $this->checkEventResult($this->onEndExportReport, $args);
	}
	
	public $onEmailReport = null;
	private function invokeEmailReport($request) {
		$settings = new StiEmailSettings();
		$settings->to = $request->settings->email;
		$settings->subject = $request->settings->subject;
		$settings->message = $request->settings->message;
		$settings->attachmentName = $request->fileName.'.'.$this->getFileExtension($request->format);
		
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->settings = $settings;
		$args->format = $request->format;
		$args->fileName = $request->fileName;
		$args->data = base64_decode($request->data);
		
		$result = $this->checkEventResult($this->onEmailReport, $args);
		if (!$result->success) return $result;
		
		$guid = substr(md5(uniqid().mt_rand()), 0, 12);
		if (!file_exists('tmp')) mkdir('tmp');
		file_put_contents('tmp/'.$guid.'.'.$args->fileName, $args->data);
		
		// Detect auth mode
		$auth = $settings->host != null && $settings->login != null && $settings->password != null;
		
		$mail = substr(PHP_VERSION, 0, 1) == '5' ? new PHPMailer(true) : new PHPMailer\PHPMailer\PHPMailer(true);
		if ($auth) $mail->IsSMTP();
		try {
			$mail->CharSet = $settings->charset;
			$mail->IsHTML(false);
			$mail->From = $settings->from;
			$mail->FromName = $settings->name;
				
			// Add Emails list
			$emails = preg_split('/,|;/', $settings->to);
			foreach ($emails as $settings->to) {
				$mail->AddAddress(trim($settings->to));
			}
			
			// Fill email fields
			$mail->Subject = htmlspecialchars($settings->subject);
			$mail->Body = $settings->message;
			$mail->AddAttachment('tmp/'.$guid.'.'.$args->fileName, $settings->attachmentName);
			
			// Fill auth fields
			if ($auth) {
				$mail->Host = $settings->host;
				$mail->Port = $settings->port;
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = $settings->secure;
				$mail->Username = $settings->login;
				$mail->Password = $settings->password;
			}
			
			$mail->Send();
		}
		catch (phpmailerException $e) {
			$error = strip_tags($e->errorMessage());
			return StiResult::error($error);
		}
		catch (Exception $e) {
			$error = strip_tags($e->getMessage());
		}
		
		unlink('tmp/'.$guid.'.'.$args->fileName);
		
		if (isset($error)) return StiResult::error($error);
		return $result;
	}
	
	public $onDesignReport = null;
	private function invokeDesignReport($request) {
		$args = new stdClass();
		$args->sender = $request->sender;
		$args->fileName = $request->fileName;
		return $this->checkEventResult($this->onDesignReport, $args);
	}
	
//--- Methods
	
	public function registerErrorHandlers() {
		set_error_handler("stiErrorHandler");
		register_shutdown_function("stiShutdownFunction");
	}
	
	public function process($response = true) {
		$result = $this->innerProcess();
		if ($response) StiResponse::json($result);
		return $result;
	}
	
	
//--- Private methods
	
	private function createConnection($args) {
		switch ($args->database) {
			case StiDatabaseType::MySQL: $connection = new StiMySqlAdapter(); break;
			case StiDatabaseType::MSSQL: $connection = new StiMsSqlAdapter(); break;
			case StiDatabaseType::Firebird: $connection = new StiFirebirdAdapter(); break;
			case StiDatabaseType::PostgreSQL: $connection = new StiPostgreSqlAdapter(); break;
			case StiDatabaseType::Oracle: $connection = new StiOracleAdapter(); break;
		}
		
		if (isset($connection)) {
			$connection->parse($args->connectionString);
			return StiResult::success(null, $connection);
		}
		
		return StiResult::error("Unknown database type [".$args->database."]");
	}
	
	private function innerProcess() {
		$request = new StiRequest();
		$result = $request->parse();
		if ($result->success) {
			switch ($request->event) {
				case StiEventType::BeginProcessData:
				case StiEventType::ExecuteQuery:
					$result = $this->invokeBeginProcessData($request);
					if (!$result->success) return $result;
					$queryString = $result->object->queryString;
					$result = $this->createConnection($result->object);
					if (!$result->success) return $result;
					$connection = $result->object;
					if (isset($queryString)) $result = $connection->execute($queryString);
					else $result = $connection->test();
					$result = $this->invokeEndProcessData($request, $result);
					if (!$result->success) return $result;
					if (isset($result->object) && isset($result->object->result)) return $result->object->result;
					return $result;
					
				case StiEventType::CreateReport:
					return $this->invokeCreateReport($request);
					
				case StiEventType::OpenReport:
					return $this->invokeOpenReport($request);
					
				case StiEventType::SaveReport:
					return $this->invokeSaveReport($request);
					
				case StiEventType::SaveAsReport:
					return $this->invokeSaveReport($request);
					
				case StiEventType::PrintReport:
					return $this->invokePrintReport($request);
					
				case StiEventType::BeginExportReport:
					return $this->invokeBeginExportReport($request);
					
				case StiEventType::EndExportReport:
					return $this->invokeEndExportReport($request);
						
				case StiEventType::EmailReport:
					return $this->invokeEmailReport($request);
					
				case StiEventType::DesignReport;
					return $this->invokeDesignReport($request);
			}
			
			$result = StiResult::error("Unknown event [".$request->event."]");
		}
		
		return $result;
	}
	
	private function getFileExtension($format) {
		switch ($format) {
			case StiExportFormat::Html:
			case StiExportFormat::Html5:
				return "html";
				
			case StiExportFormat::Pdf:
				return "pdf";
				
			case StiExportFormat::Excel2007:
				return "xlsx";
				
			case StiExportFormat::Word2007:
				return "docx";
				
			case StiExportFormat::Csv:
				return "csv";
		}
		return "";
	}
}


//---------- Helper ----------//


class StiHelper {
	public static function createOptions() {
		$options = new stdClasS();
		$options->handler = "handler.php";
		$options->timeout = 30;
		
		return $options;
	}
	
	public static function initialize($options) {
		if (!isset($options)) $options = StiHelper::createOptions();
?>
	<script type="text/javascript">
		StiHelper.prototype.process = function (args, callback) {
			if (args) {
				if (args.event == 'BeginProcessData') {
					args.preventDefault = true;
					if (args.database == 'XML' || args.database == 'JSON' || args.database == 'Excel')
						return callback(null);
					if (args.database == 'Data from DataSet, DataTables')
						return callback(args);
				}
				var command = {};
				for (var p in args) {
					if (p == 'report' && args.report != null) command.report = JSON.parse(args.report.saveToJsonString());
					else if (p == 'settings' && args.settings != null) command.settings = args.settings;
					else if (p == 'data') command.data = Stimulsoft.System.Convert.toBase64String(args.data);
					else if (p == 'connectionString' || p == 'queryString') command[p] = jsHelper.getStringValue(args[p]);
					else command[p] = args[p];
				}
				
				var isNullOrEmpty = function (value) {
					return value == null || value === '' || value === undefined;
				}
				var json = JSON.stringify(command);
				if (!callback) callback = function (message) {
					if (Stimulsoft.System.StiError.errorMessageForm && !isNullOrEmpty(message)) {
						var obj = JSON.parse(message);
						if (!obj.success || !isNullOrEmpty(obj.notice)) {
							var message = isNullOrEmpty(obj.notice) ? 'There was some error' : obj.notice;
							Stimulsoft.System.StiError.errorMessageForm.show(message, obj.success);
						}
					}
				}
				jsHelper.send(json, callback);
			}
		}
		
		StiHelper.prototype.send = function (json, callback) {
			try {
				var request = new XMLHttpRequest();
				request.open('post', this.url, true);
				request.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
				request.setRequestHeader('Cache-Control', 'max-age=0');
				request.setRequestHeader('Pragma', 'no-cache');
				request.timeout = this.timeout * 1000;
				request.onload = function () {
					if (request.status == 200) {
						var responseText = request.responseText;
						request.abort();
						callback(responseText);
					}
					else {
						Stimulsoft.System.StiError.showError('[' + request.status + '] ' + request.statusText, false);
					}
				};
				request.onerror = function (e) {
					var errorMessage = 'Connect to remote error: [' + request.status + '] ' + request.statusText;
					Stimulsoft.System.StiError.showError(errorMessage, false);
				};
				request.send(json);
			}
			catch (e) {
				var errorMessage = 'Connect to remote error: ' + e.message;
				Stimulsoft.System.StiError.showError(errorMessage, false);
				request.abort();
			}
		};
		
		StiHelper.prototype.getStringValue = function (value) {
			return Stimulsoft.System.Convert.toBase64String(value).replace(/[a-zA-Z]/g, function (c) {
				return String.fromCharCode((c <= "Z" ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26);
			});
		};
		
		StiHelper.prototype.getUrlVars = function (json, callback) {
			var vars = {};
			var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,
				function (m, key, value) {
					vars[key] = decodeURI(value);
			});
			return vars;
		}
		
		function StiHelper(url, timeout) {
			this.url = url;
			this.timeout = timeout;
		}
		
		jsHelper = new StiHelper('<?php echo $options->handler; ?>', <?php echo $options->timeout; ?>);
</script>
<?php
	}
	
	public static function createHandler() {
?>jsHelper.process(arguments[0], arguments[1]);
<?php
	}
}