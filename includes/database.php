<?php

//==============================================================================================
//  ______      _        _                      _____ _
//  |  _  \    | |      | |                    /  __ \ |
//  | | | |__ _| |_ __ _| |__   __ _ ___  ___  | /  \/ | __ _ ___ ___
//  | | | / _` | __/ _` | '_ \ / _` / __|/ _ \ | |   | |/ _` / __/ __|
//  | |/ / (_| | || (_| | |_) | (_| \__ \  __/ | \__/\ | (_| \__ \__ \
//  |___/ \__,_|\__\__,_|_.__/ \__,_|___/\___|  \____/_|\__,_|___/___/
//
//
//==============================================================================================

class Database {

	public $timer = 0;
	public $dbo = null;
	public $log_file = null;

	public $affected_rows;
	public $insert_id;

	public function __construct($log=false) {
		$timer = microtime(true);
		$this->dbo = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
		mysql_select_db(DB_DATABASE, $this->dbo);
		// mysql_set_charset("iso-8859-1", $this->dbo);
		$this->timer += microtime(true) - $timer;
		if($log == true) {
			$this->log_file = fopen(DB_LOG_FILE, 'a');
		}
	}

	public function query($sql) {

		$timer = microtime(true);
		//$escaped_sql = $this->dbo->real_escape_string($sql);
		$escaped_sql = $sql;
		$res = mysql_query($escaped_sql, $this->dbo);
		$this->affected_rows = mysql_affected_rows($this->dbo);
		$this->insert_id = mysql_insert_id($this->dbo);
		$this->timer += microtime(true) - $timer;

		if($this->log_file != null) {
			$time = microtime(true) - $timer;
			fwrite($this->log_file, $sql.'|'.$time.PHP_EOL);
		}

		if($res == false) {
			echo 'Error: '.mysql_error($this->dbo).'<br/>';
			echo 'SQL: '.$escaped_sql.'<br/>';
		}
		return new Result($res, $this);
	}

	public function __destruct() {
		if($this->log_file != null) {
			fclose($this->log_file);
		}
		mysql_close($this->dbo);
		unset($this->dbo);
	}

}

//==============================================================================================
//  ______                _ _     _____ _
//  | ___ \              | | |   /  __ \ |
//  | |_/ /___  ___ _   _| | |_  | /  \/ | __ _ ___ ___
//  |    // _ \/ __| | | | | __| | |   | |/ _` / __/ __|
//  | |\ \  __/\__ \ |_| | | |_  | \__/\ | (_| \__ \__ \
//  \_| \_\___||___/\__,_|_|\__|  \____/_|\__,_|___/___/
//
//
//==============================================================================================

class Result {

	public $dbo;
	public $dbr;
	public $num_rows;
	public $affected_rows;

	public function __construct($dbr, $dbo) {

		$this->dbr = $dbr;
		if(gettype($this->dbr) != "boolean") {
			$this->num_rows = mysql_num_rows($this->dbr);
		}
		$this->dbo = $dbo;
	}

	public function fetch_object() {
		$timer = microtime(true);
		$obj = mysql_fetch_object($this->dbr);
		$this->dbo->timer += microtime(true) - $timer;
		if($obj === false) $obj = null;
		return $obj;
	}

	public function fetch_array() {
		$timer = microtime(true);
		$ar = mysql_fetch_array($this->dbr);
		$this->dbo->timer += microtime(true) - $timer;
		if($ar === false) $ar = null;
		return $ar;
	}

	public function fetch_row() {
		$timer = microtime(true);
		$row = mysql_fetch_row($this->dbr);
		$this->dbo->timer += microtime(true) - $timer;
		if($row === false) $row = null;
		return $row;
	}

	public function fetch_assoc() {
		$timer = microtime(true);
		$row = mysql_fetch_assoc($this->dbr);
		$this->dbo->timer += microtime(true) - $timer;
		if($row === false) $row = null;
		return $row;
	}

	public function data_seek($offset) {
		$timer = microtime(true);
		mysql_data_seek($this->dbr, $offset);
		$this->dbo->timer += microtime(true) - $timer;
	}

	public function __destruct() {
		if(is_bool($this->dbr) == false && $this->dbr != null) {
			mysql_free_result($this->dbr);
		}
		unset($this->dbr);
	}
}

?>