<?php

require_once("sms.php");
const INIPATH = "refonlinesmsconfig.ini";

class Db {
	// The database connection
	protected static $connection;

	/**
	 * Connect to the database
	 *
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
	public function connect() {

		// Try and connect to the database
		if(!isset(self::$connection)) {
			// Load configuration as an array. Use the actual location of your configuration file
			// Put the configuration file outside of the document root
			$config = parse_ini_file(INIPATH);
			self::$connection = new mysqli($config['host'],$config['username'],$config['password'],$config['dbname']);
		}

		// If connection was not successful, handle the error
		if(self::$connection === false) {
			// Handle error - notify administrator, log to a file, show an error screen, etc.
			return false;
		}
		return self::$connection;
	}

	/**
	 * Query the database
	 *
	 * @param $query The query string
	 * @return mixed The result of the mysqli::query() function
	 */
	public function query($query) {
		// Connect to the database
		$connection = $this -> connect();

		// Query the database
		$result = $connection -> query($query);

		return $result;
	}

	/**
	 * Fetch rows from the database (SELECT query)
	 *
	 * @param $query The query string
	 * @return bool False on failure / array Database rows on success
	 */
	public function select($query) {
		$rows = array();
		$result = $this -> query($query);
		if($result === false) {
			return false;
		}
		while ($row = $result -> fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Fetch the last error from the database
	 *
	 * @return string Database error message
	 */
	public function error() {
		$connection = $this -> connect();
		return $connection -> error;
	}

	/**
	 * Quote and escape value for use in a database query
	 *
	 * @param string $value The value to be quoted and escaped
	 * @return string The quoted and escaped string
	 */
	public function quote($value) {
		$connection = $this -> connect();
		return "'" . $connection -> real_escape_string($value) . "'";
	}
}

function getLocations() {
  $db = new Db();
  return $db->select("SELECT * FROM locations ORDER BY name");
}

function usedMobile($mobile) {
  $db = new Db();
  if ($db->select("SELECT location_id FROM vouchers WHERE mobileno='".$mobile."' AND lastupdate >= UNIX_TIMESTAMP((NOW() - INTERVAL 60 DAY))"))
    return true;
  else
    return false;
}

function sendSms($mobile, $location) {
  // get new voucher
  $db = new Db();
  // SELECT voucher FROM vouchers WHERE location_id='".$location."' AND mobileno='' LIMIT 1
	$voucher = $db->select("SELECT voucher FROM vouchers WHERE location_id='".$location."' AND mobileno IS NULL ORDER BY voucher LIMIT 1");
	if (!empty($voucher)) {
	  // UPDATE vouchers SET mobileno='".$mobile."', lastupdate = NOW() WHERE voucher=''
		$update = $db->query("UPDATE vouchers SET mobileno='".$mobile."', lastupdate = NOW() WHERE voucher='".$voucher[0]["voucher"]."'");
	  // send SMS
		$config = parse_ini_file(INIPATH);
	  $sms = new SMS("https://konsoleh.your-server.de/");
	  $domain = $config["smsdomain"]; // e.g.: «my-domain.de» (without www!)
	  $password = $config["smspassword"]; // your FTP password (transmission is encrypted)
	  $country = "+49"; // country code (e.g. "+49" for Germany)
	  $text = "Your voucher code is:" . $voucher[0]["voucher"]; // the desired text (up to max. 160 characters)
	  $sms->send($domain,$password,$country,$mobile,$text);
	  // return true
	  return true;
	} else {
		return false;
	}
}

function checkRecaptcha($captcha, $server) {
	$config = parse_ini_file(INIPATH);
	$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$config["secret"]."&response=".$captcha."&remoteip=".$server));
	return $response;
}

?>
