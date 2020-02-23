<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";
$gen = new Generic;
$data = new Data;
$params = $gen->requestParam([
  "rdt" => ["rdt","url"],
  "user" => ["user","text", 5, 0],
  "token" => ["token","text", 5, 0],
],'get',["user","token"]);
if (!$params) HTTP\Header::badRequest(true, "Email/OPT parameters not well set, contact developer.");
if (!$token = $data->decodeDecrypt($params['token'])) {
  HTTP\Header::badRequest(true, "Invalid Email/OPT [token] supplied, contact developer.");
}
$email = $data->decodeDecrypt($params['user']);
if (!$email) {
  HTTP\Header::badRequest(true, "Invalid Email/OPT [user] supplied, contact developer.");
}
$otp = new OTP\Email($mailgun_api_domain, $mailgun_api_key);
if (!$otp->verify($email, $token)) {
  HTTP\Header::unauthorized(true, "Invalid email verification link followed, contact admin");
}

$rdt = empty($params["rdt"])
  ? WHOST . "/user/sign-in"
  : $params["rdt"];
if ($session->isLoggedIn()) {
  HTTP\Header::redirect(WHOST . "/user");
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Rset your login password | <?php echo PRJ_TITLE; ?></title>
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
    <?php \setup_page("user-signin", "user", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-7-tablet grid-5-laptop center-tablet">
          <div class="sec-div color blue bg-white drop-shadow">
            <header class="padding -p20 border -bmedium -bbottom">
              <span class="fa-stack fa-2x push-right color-text">
                <i class="far fa-stack-2x fa-circle"></i>
                <i class="fas fa-stack-1x fa-key"></i>
              </span>
              <h1>Set new password</h1>
            </header>
            <div class="padding -p20">
              <form
                id="pwd-reset-form"
                class="block-ui"
                method="post"
                action="/ResetPwd.php"
                data-path="/user/src"
                data-domain="<?php echo WHOST;?>"
                data-validate="false"
                onsubmit="sos.form.submit(this, PwdReset); return false;"
              >
              <input type="hidden" name="rdt" value="<?php echo $rdt; ?>">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="user" value="<?php echo $email; ?>">
                <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('pwd-reset-form'); ?>">
                <input type="hidden" name="form" value="pwd-reset-form">

                <div class="grid-12-tablet">
                  <label for="password"><i class="fas fa-asterisk fa-border fa-sm"></i>New Password</label>
                  <input type="password" name="password" autocomplete="off" placeholder="Password" required id="password">
                </div>
                <div class="grid-12-tablet">
                  <label for="password-rpt"><i class="fas fa-asterisk fa-border fa-sm"></i>Repeat Password</label>
                  <input type="password" name="password_repeat" autocomplete="off" placeholder="PasswordRepeat" required id="password-rpt">
                </div>
                <div class="grid-5-tablet">
                  <button type="submit" id="rsd-click" class="sos-btn blue"><i class="fas fa-key"></i> Set now </button>
                </div>

                <br class="c-f">
              </form>
            </div>
          </div>
          <p class="align-c padding -p20">
            I don't have an account  <a href="<?php echo Generic::setGet(WHOST . "/user/sign-up",["rdt"=>$params['rdt']]) ?>"> <i class="fas fa-plus"></i> Sign up now</a>
             |  <a href="<?php echo Generic::setGet(WHOST . "/user/password-reset",["rdt"=>$params['rdt']]) ?>"> <i class="fas fa-sync-alt"></i> Reset password</a>
          </p>
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
