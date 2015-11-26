<?php
require_once("php/functions.php");
/* preset variables */
$error = array();
$success = false;
$mobile = "";
$location = "0";
$terms = 0;
/* check for location given in the link from pfSense */
if (isset($_GET["location"])) $location = $_GET["location"];
/* check for form submission */
if (isset($_POST["submit"])) {
  if (isset($_POST["mobile"])) $mobile = $_POST["mobile"];
  if (isset($_POST["location"])) $location = $_POST["location"];
  if (isset($_POST["terms"])) $terms = $_POST["terms"];
  // Validation
  if (($mobile == "") || (!preg_match("/(^\+49)|(^01[5-7][1-9])/", $mobile))) { // number format
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
      sendSms($mobile_tech, $location);
      $mobile = "";
      $terms = 0;
      $success = true;
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
              <div class="alert alert-success" role="alert"><b>Success:</b> Your voucher has been successfully sent to your mobile number.</div>
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
            <form method="post" action="<?php echo $_SERVER["PHP_SELF"];?>" class="form-horizontal">
              <h1 class="cover-heading">SMS Voucher for WLAN</h1>
              <p class="lead">Type in your mobile number below to receive a WLAN voucher. If not choosen, please pick a location where you want access to the internet.</p>
              <p class="lead">
                <div class="form-group">
                  <label for="mobile" class="col-sm-3 control-label">Mobile number:</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="e.g. 0123-1234567" name="mobile"<?php if ($mobile != "") echo ' value="'.$mobile.'"'; ?>>
                  </div>
                </div>
                <div class="form-group">
                  <label for="location" class="col-sm-3 control-label">Choose location:</label>
                  <div class="col-sm-9">
                    <select name="location" class="form-control">
                      <option value="0"<?php if ($location=="0") echo " selected"; ?>>choose...</option>
                      <?php // echo getLocations(); ?>
                      <option value="0001"<?php if ($location=="0001") echo " selected"; ?>>Erstaufnahme Fürstenfeldbruck [Fursty]</option>
                      <option value="0002"<?php if ($location=="0002") echo " selected"; ?>>Gemeinschaftsunterkunft Don Bosco, Germering [DonBosco]</option>
                      <option value="0003"<?php if ($location=="0003") echo " selected"; ?>>Traglufthalle Gilching [Gilching]</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-3 col-sm-9">
                    <div class="checkbox" align="left">
                      <label>
                        <input type="checkbox" name="terms" value="1"<?php if ($terms==1) echo " checked"; ?>> I agree to the <a href="terms.php">Terms of Service</a>.
                      </label>
                    </div>
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
