<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";

if ($session->isLoggedIn()) HTTP\Header::redirect(WHOST . "/user");
$gen = new Generic;
$data = new Data;
$params = $gen->requestParam([
  "rdt" => ["rdt","url"]
],'get',[]);
if (!$params) HTTP\Header::badRequest(true, "Email/OPT [reference] not supplied, contact developer.");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Reset your login password | <?php echo PRJ_TITLE; ?></title>
    <?php include PRJ_INC_ICONSET; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="keywords" content="password, reset, forgot, forget, login">
    <meta name="description" content="Reset your forgotten login password | <?php echo PRJ_TITLE; ?>">
    <meta name="author" content="<?php echo PRJ_AUTHOR; ?>">
    <meta name="creator" content="<?php echo PRJ_CREATOR; ?>">
    <meta name="publisher" content="<?php echo PRJ_PUBLISHER; ?>">
    <meta name="robots" content='index'>
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
    <?php \TymFrontiers\Helper\setup_page("user-signin", "user", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-8-tablet grid-6-laptop center-tablet">
          <div class="sec-div color blue bg-white drop-shadow">
            <header class="padding -p20 border -bmedium -bbottom">
              <h1 class="color-text">Reset login</h1>
            </header>
            <div class="padding -p20">
              <p>Enter your account email, if we find any account linked to it; we shall send you password reset link.</p>
              <form
                id="password-reset-form"
                class="block-ui color blue"
                method="post"
                action="/ResetPassword.php"
                data-path="/user/src"
                data-domain="<?php echo WHOST;?>"
                data-validate="false"
                onsubmit="sos.form.submit(this, resetSent); return false;"
              >
                <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('password-reset-form'); ?>">
                <input type="hidden" name="form" value="password-reset-form">
                <input type="hidden" name="reference" value="">
                <input type="hidden" name="rdt" value="<?php echo !empty($params['rdt']) ? $params['rdt'] : ""; ?>">
                <div class="grid-12-tablet">
                  <label for="email"> <i class="fas fa-asterisk fa-border"></i> Account email</label>
                  <input type="email" name="email" id="email" placeholder="email@domain.com" required>
                </div>
                <div class="grid-7-tablet">
                  <div id="res-cnt-view" class="align-c">
                    Resend in: <br>
                    <span class="bold font-1-5" id="cnt-timer">0:00</span>
                  </div>
                </div>
                <div class="grid-5-tablet">
                  <button type="submit" id="rsd-click" class="sos-btn blue"> <i class="fas fa-paper-plane"></i> <span id="btn-msg">Reset</span> </button>
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
