<?php

require_once("Db.php");
//require_once("sms.php");

function getLocations() {
  $db = new Db();
  return $db->select("SELECT * FROM locations ORDER BY name");
}

function usedMobile($mobile) {
  $db = new Db();
  if ($db->select("SELECT location_id FROM vouchers WHERE mobileno='".$mobile."' AND lastupdate <= UNIX_TIMESTAMP((NOW() - INTERVAL 30 DAY))"))
    return true;
  else
    return false;
}

function sendSms($mobile, $location) {
  // get new voucher
  $db = new Db();
  // SELECT voucher FROM vouchers WHERE location_id='".$location."' AND mobileno='' LIMIT 1
  // UPDATE vouchers SET mobileno='".$mobile."', lastupdate = NOW() WHERE voucher=''
  // send SMS
  $sms = new SMS("https://konsoleh.your-server.de/");
  $domain = "refugees-online.de"; // e.g.: «my-domain.de» (without www!)
  $password = "A6tdmGjVtnAeJn34"; // your FTP password (transmission is encrypted)
  $land = "+49"; // country code (e.g. "+49" for Germany)
  $mobile = ""; // cellphone number (e.g. "1631234567")
  $text = "xxx " . $code . " xxx"; // the desired text (up to max. 160 characters)
  $sms->send($domain,$password,$country,$mobile,$text);
  // return true
  return true;
}

?>
