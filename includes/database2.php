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

class Database
{

	private static $m_Hostname = DB_HOSTNAME;
	private static $m_Username = DB_USERNAME;
	private static $m_Password = DB_PASSWORD;
	private static $m_Database = DB_DATABASE;

	public static $m_Timer = 0;

	private $m_DBO;

	public $affected_rows;
	public $insert_id;

	public function __construct()
	{
		$timer = microtime(true);
		$this->m_DBO = new mysqli(self::$m_Hostname, self::$m_Username, self::$m_Password, self::$m_Database);
		self::$m_Timer += microtime(true) - $timer;
	}

	public function query($sql)
	{
		$timer = microtime(true);
		//$escaped_sql = $this->m_DBO->real_escape_string($sql);
		$escaped_sql = $sql;
		$res = $this->m_DBO->query($escaped_sql);
		$this->affected_rows = $this->m_DBO->affected_rows;
		$this->insert_id = $this->m_DBO->insert_id;
		self::$m_Timer += microtime(true) - $timer;
		if($res == false)
		{
			echo 'Error: '.$this->m_DBO->error.'<br/>';
			echo 'SQL: '.$escaped_sql.'<br/>';
		}
		return new Result($res);
	}

	public static function Get_Timer()
	{
		return self::$m_Timer;
	}

	public function __destruct()
	{
		$this->m_DBO->kill($this->m_DBO->thread_id);
		$this->m_DBO->close();
		unset($this->m_DBO);
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

class Result
{
	protected $m_DBR;

	public $num_rows;

	public function __construct()
	{
		$argsArray = func_get_args();
	    if(count($argsArray) == 1)
	    {
			$this->m_DBR = $argsArray[0];
			if(isset($this->m_DBR->num_rows) == true)
			{
				$this->num_rows = $this->m_DBR->num_rows;
			}
		}
		else
		{
			$this->m_DBR = null;
		}
	}

	public function fetch_object()
	{
		$timer = microtime(true);
		$obj = $this->m_DBR->fetch_object();
		Database::$m_Timer += microtime(true) - $timer;
		return $obj;
	}

	public function fetch_array()
	{
		$timer = microtime(true);
		$ar = $this->m_DBR->fetch_array();
		Database::$m_Timer += microtime(true) - $timer;
		return $ar;
	}

	public function fetch_row()
	{
		$timer = microtime(true);
		$row = $this->m_DBR->fetch_row();
		Database::$m_Timer += microtime(true) - $timer;
		return $row;
	}

	public function fetch_assoc()
	{
		$timer = microtime(true);
		$row = $this->m_DBR->fetch_assoc();
		Database::$m_Timer += microtime(true) - $timer;
		return $row;
	}

	public function data_seek($offset)
	{
		$timer = microtime(true);
		$this->m_DBR->data_seek($offset);
		Database::$m_Timer += microtime(true) - $timer;
	}

	public function __destruct()
	{
		if(is_bool($this->m_DBR) == false && $this->m_DBR != null)
		{
			$this->m_DBR->close();
		}
		unset($this->m_DBR);
	}
}

$app->database = new Database();

?>