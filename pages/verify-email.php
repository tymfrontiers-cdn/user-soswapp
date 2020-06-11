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
  "user" => ["user","text", 5, 0],
  "token" => ["token","text", 5, 0],
  "reference" => ["reference","text", 5, 0]
],'get',["reference"]);
if (!$params) HTTP\Header::badRequest(true, "Email/OPT [reference] not supplied, contact developer.");
if (!$reference = $data->decodeDecrypt($params['reference'])) {
  HTTP\Header::badRequest(true, "Invalid Email/OPT [reference] supplied, contact developer.");
}
if (!$token = $data->decodeDecrypt($params['token'])) {
  HTTP\Header::badRequest(true, "Invalid Email/OPT [token] supplied, contact developer.");
}
$email = $data->decodeDecrypt($params['user']);
if (!$email || $email !== $session->user->email) {
  HTTP\Header::badRequest(true, "Invalid Email/OPT [user] supplied, contact developer.");
}
$otp = new OTP\Email($mailgun_api_domain, $mailgun_api_key);
if (!$otp->verify($email, $token)) {
  HTTP\Header::unauthorized(true, "Invalid email verification link followed, contact admin");
}
// verify and go
$success = false;
$base_db = MYSQL_BASE_DB;
if ($database->query("UPDATE `{$base_db}`.`user` SET status='ACTIVE' WHERE email = '{$database->escapeValue($email)}' LIMIT 1")){
  $success = true;
  $session->logout();
}
$rdt = !empty($rdt) ? $rdt : WHOST . "/user/login";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Email verification | <?php echo PRJ_TITLE; ?></title>
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
    <?php \TymFrontiers\Helper\setup_page("user-signup", "user", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-8-tablet grid-6-laptop center-tablet">
          <div class="sec-div color blue bg-white drop-shadow">
            <header class="padding -p20 border -bmedium -bbottom">
              <h1 class="color-text"><?php echo $success ? "Your are good to go!" : "Update failed."; ?></h1>
            </header>
            <div class="padding -p20">
              <?php if ($success) { ?>
                <p>
                  Thank, your email has been verified.
                  <a href="<?php echo $rdt; ?>"> Continue <i class="fas fa-angle-double-right"></i></a>
                </p>

              <?php } else { ?>
                <p>Sorry your email verification was not successful, please contact admin for manual verification.</p>
              <?php } ?>
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
