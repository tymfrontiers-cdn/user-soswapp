<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";
\require_login(true);
$gen = new Generic;
$data = new Data;
$params = $gen->requestParam([
  "rdt" => ["rdt","url"],
  "reference" => ["reference","text", 5, 0]
],'get',["reference"]);
if (!$params) HTTP\Header::badRequest(true, "Email/OPT [reference] not supplied, contact developer.");
if (!$reference = $data->decodeDecrypt($params['reference'])) {
  HTTP\Header::badRequest(true, "Invalid Email/OPT [reference] supplied, contact developer.");
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>One last step | <?php echo PRJ_TITLE; ?></title>
    <?php include PRJ_INC_ICONSET; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="author" content="<?php echo PRJ_AUTHOR; ?>">
    <meta name="creator" content="<?php echo PRJ_CREATOR; ?>">
    <meta name="publisher" content="<?php echo PRJ_PUBLISHER; ?>">
    <meta name="robots" content='nofollow'>
    <!-- Theming styles -->
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/font-awesome-soswapp/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/theme-soswapp/css/theme.min.css">
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/theme-soswapp/css/theme-<?php echo PRJ_THEME; ?>.min.css">
    <!-- optional plugin -->
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/plugin-soswapp/css/plugin.min.css">
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/dnav-soswapp/css/dnav.min.css">
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/faderbox-soswapp/css/faderbox.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="<?php echo \html_style("base.css"); ?>">
  </head>
  <body>
    <?php \setup_page("user-signup", "user", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-8-tablet grid-6-laptop center-tablet">
          <div class="sec-div color blue bg-white drop-shadow">
            <header class="padding -p20 border -bmedium -bbottom">
              <h1 class="color-text">One last step to go</h1>
              <p>You are one step away from your account.</p>
            </header>
            <div class="padding -p20">
              <p>We need to confirm your email is reachable, please follow <code>link</code> in email sent to you verify your email.</p>
              <h3>Didn't see the email?</h3>
              <p>Some times the email might take up to 15 minutes to arrive, please refresh your folders.</p>
              <p>Alternatively, check your spam folder if you did not see it in your inbox, should you find our email in your spam folder? Kindly move to inbox and unmark our domain [<code><?php echo PRJ_DOMAIN; ?></code>] as spam for a smooth communication next time.</p>
              <h4> <i class="fas fa-frown"></i> I still did not see it!</h4>
              <p>No worries, hit resend and wait a while and it will arrive.</p>
              <form
                id="otp-resed1"
                class="block-ui color asphalt"
                method="post"
                action="/ResendOTP.php"
                data-path="/user/src"
                data-domain="<?php echo WHOST;?>"
                data-validate="false"
                onsubmit="sos.form.submit(this, otpResent); return false;"
              >
                <input type="hidden" name="reference" value="<?php echo $reference; ?>">
                <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('otp-resed1'); ?>">
                <input type="hidden" name="form" value="otp-resed1">
                <div class="grid-7-tablet">
                  <div id="res-cnt-view" class="align-c">
                    Resend in: <br>
                    <span class="bold font-1-5" id="cnt-timer">0:00</span>
                  </div>
                </div>
                <div class="grid-5-tablet">
                  <button type="submit" id="rsd-click" class="sos-btn blue"> Resend <i class="fas fa-arrow-right"></i></button>
                </div>

                <br class="c-f">
              </form>
            </div>
          </div>
        </div>
        <br class="c-f">
      </div>
    </section>
    <?php include PRJ_INC_FOOTER; ?>
    <!-- Required scripts -->
    <script src="<?php echo WHOST; ?>/7os/jquery-soswapp/js/jquery.min.js">  </script>
    <script src="<?php echo WHOST; ?>/7os/js-generic-soswapp/js/js-generic.min.js">  </script>
    <script src="<?php echo WHOST; ?>/7os/theme-soswapp/js/theme.min.js"></script>
    <!-- optional plugins -->
    <script src="<?php echo WHOST; ?>/7os/plugin-soswapp/js/plugin.min.js"></script>
    <script src="<?php echo WHOST; ?>/7os/dnav-soswapp/js/dnav.min.js"></script>
    <script src="<?php echo WHOST; ?>/7os/faderbox-soswapp/js/faderbox.min.js"></script>
    <!-- project scripts -->
    <script src="<?php echo \html_script ("base.min.js"); ?>"></script>
    <script src="<?php echo WHOST . "/user/assets/js/user.min.js" ?>"></script>
    <script type="text/javascript">
      $("#res-cnt-view").hide();
    </script>
  </body>
</html>
