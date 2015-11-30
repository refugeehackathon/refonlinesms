<?php
require_once("php/Db.php");
/* preset variables */
$error = array();
$success = false;
$mobile = "";
$location = "0";
$terms = 0;
$captcha = "";
/* check for location given in the link from pfSense */
if (isset($_GET["location"]))
  $location = $_GET["location"];
/* check for form submission */
if (isset($_POST["submit"])) {
  if (isset($_POST["mobile"])) $mobile = strval($_POST["mobile"]);
  if (isset($_POST["location"])) $location = $_POST["location"];
  if (isset($_POST["terms"])) $terms = $_POST["terms"];
  if (isset($_POST["g-recaptcha-response"])) $captcha = $_POST['g-recaptcha-response'];
  // Validation
  if (($mobile == "") || (!preg_match("/(^\+49)|(^01[5-7][0-9])/", $mobile)) || (strlen($mobile)<11)) { // number format
    // "/(^\+49)|(^01[5-7][1-9])/"
    // "(?:\+\d+)?\s*(?:\(\d+\)\s*(?:[/–-]\s*)?)?\d+(?:\s*(?:[\s/–-]\s*)?\d+)*"
    // "/^\+[0-9]{0,3}[1-9] \([0-9]*[1-9]+\) ([0-9]| - )*[1-9]+$/"
    $error[] = 3;
  }
  if ($location == "0") { // no location selected
    $error[] = 2;
  }
  if ($terms != 1) { // terms not checked
    $error[] = 1;
  }
  if (checkRecaptcha($captcha, $_SERVER['REMOTE_ADDR'])->success != 1) {
    $error[] = 6;
  }
  if (count($error) == 0) {
    $mobile_tech = "";
    // remove everything except numbers
    $mobile_tech = preg_replace('/\D/', '', $mobile); // +49 (0) 177 - 2563235 -> 4901772563235
    // remove leading "0" and/or leading "49"
    $mobile_tech = ltrim($mobile_tech, "0"); // 004901772563235 -> 4901772563235
    $mobile_tech = ltrim($mobile_tech, "49"); // 4901772563235 -> 01772563235
    $mobile_tech = ltrim($mobile_tech, "0"); // 01772563235 -> 1772563235
    // check for already used number
    if (usedMobile($mobile_tech)) {
      $error[] = 4;
    }
    else {
      $success =
      $sended = sendSms($mobile_tech, $location);
      if ($sended) {
        $success = true;
        $mobile = "";
        $terms = 0;
      } else {
        $error[] = 5;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="publicplan GmbH, https://publicplan.de">
    <link rel="icon" href="images/favicon.ico">

    <title>Refugees Online e.V. - SMS Voucher for WLAN</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/cover.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="https://www.google.com/recaptcha/api.js"></script>

    <?php if ($success == true) { ?>
      <meta http-equiv="refresh" content="5; url=http://192.168.99.1:8002/index.php?zone=zone_01&redirurl=http%3A%2F%2Fwww.refugees-online%2F">
    <?php } ?>
  </head>

  <body>

    <div class="site-wrapper">

      <div class="site-wrapper-inner">

        <div class="cover-container">

          <div class="masthead clearfix">
            <div class="inner">
              <h3 class="masthead-brand"><img src="images/logo.png"></h3>
              <nav>
                <ul class="nav masthead-nav">
                  <li class="active"><a href="index.php">Home</a></li>
                  <li><a href="terms.php">Terms of Service</a></li>
                </ul>
              </nav>
            </div>
          </div>

          <div class="inner cover">
            <?php if ($success == true) { ?>
              <div class="alert alert-success" role="alert"><b>Success:</b> Your voucher has been successfully sent to your mobile number. <a href="http://192.168.99.1:8002/index.php?zone=zone_01&redirurl=http%3A%2F%2Fwww.refugees-online%2F">Click here to login!</a></div>
            <?php } ?>
            <?php if (in_array(1, $error)) { ?>
              <div class="alert alert alert-danger" role="alert"><b>Error:</b> Please accept our Terms of Service!</div>
            <?php } ?>
            <?php if (in_array(2, $error)) { ?>
              <div class="alert alert alert-danger" role="alert"><b>Error:</b> Please choose a location to get access for.</div>
            <?php } ?>
            <?php if (in_array(3, $error)) { ?>
              <div class="alert alert alert-danger" role="alert"><b>Error:</b> Your mobile number is not well formatted or empty, please check.</div>
            <?php } ?>
            <?php if (in_array(4, $error)) { ?>
              <div class="alert alert alert-danger" role="alert"><b>Error:</b> Your mobile number was already used to receive a voucher in the 60 days.</div>
            <?php } ?>
            <?php if (in_array(5, $error)) { ?>
              <div class="alert alert alert-danger" role="alert"><b>Error:</b> There are no more available voucher codes in our database. Please contact <a href="mailto:support@refugees-online.de">support@refugees-online.de</a>.</div>
            <?php } ?>
            <?php if (in_array(6, $error)) { ?>
              <div class="alert alert alert-danger" role="alert"><b>Error:</b> Please make sure that you are no robot!</div>
            <?php } ?>
            <form method="post" action="<?php echo $_SERVER["PHP_SELF"];?>" class="form-horizontal">
              <h1 class="cover-heading">Get your WiFi voucher by SMS!</h1>
              <p>You can obtain a free WiFi voucher for 60 days by filling the form below. You need to have a German mobile number, foreign numbers will not be accepted. The provider of the WiFi service may block your account at its sole discretion if your data is suspect to fraud (e.g. providing an incorrect name).</br>
                You must read and agree to the Terms of Service below. <a href="terms.php" target="_blank">Click here</a> for the Terms of Service in Arabic, French, Urdu, Krio, Tigrinya and many other languages.</p></p>
              <p>
                <div class="form-group">
                  <label for="mobile" class="col-sm-3 control-label">Mobile number:</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="e.g. 0123-1234567" name="mobile"<?php if ($mobile != "") echo ' value="'.$mobile.'"'; ?>>
                  </div>
                </div>
                <?php if ((in_array(2, $error)) || ($location == "0")) { ?>
                <div class="form-group">
                  <label for="location" class="col-sm-3 control-label">Choose location:</label>
                  <div class="col-sm-9">
                    <select name="location" class="form-control">
                      <option value="0"<?php if ($location=="0") echo " selected"; ?>>choose...</option>
                      <?php // echo getLocations(); ?>
                      <option value="1"<?php if ($location=="1") echo " selected"; ?>>Erstaufnahme Fürstenfeldbruck [Fursty]</option>
                      <option value="2"<?php if ($location=="2") echo " selected"; ?>>Gemeinschaftsunterkunft Don Bosco, Germering [DonBosco]</option>
                      <option value="3"<?php if ($location=="3") echo " selected"; ?>>Traglufthalle Gilching [Gilching]</option>
                    </select>
                  </div>
                </div>
                <?php } else { ?>
                <input type="hidden" name="location" value="<?php echo $location; ?>" />
                <?php } ?>
                <div class="form-group">
                  <div class="col-sm-offset-3 col-sm-9">
                    <div class="checkbox" align="left">
                      <label>
                        <input type="checkbox" name="terms" value="1"<?php if ($terms==1) echo " checked"; ?>> I agree to the <a href="terms.php" target="_blank">Terms of Service</a>.
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-3 col-sm-9">
                    <div class="g-recaptcha" data-sitekey="6LfbBxITAAAAAN4eckOP5VU9EvwF7Dr9FRkTeavb"></div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12">
                    <button type="submit" name="submit" value="Get Voucher!" class="btn btn-lg btn-default">Get Voucher!</button>
                  </div>
                </div>
              </p>
            </form>
          </div>

          <div class="mastfoot">
            <div class="inner">
              <p>Cover template for <a href="http://getbootstrap.com" target="_blank">Bootstrap</a>, by <a href="https://twitter.com/mdo" target="_blank">@mdo</a>. <a href="https://github.com/refugeehackathon/refonlinesms" target="_blank">Help improving refonlinesms on Github.</a></p>
            </div>
          </div>

        </div>

      </div>

    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
