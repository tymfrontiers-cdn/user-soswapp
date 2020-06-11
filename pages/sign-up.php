<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";
if ($session->isLoggedIn()) HTTP\Header::redirect(WHOST . "/user/dashboard");
$gen = new Generic;
$params = $gen->requestParam([
  "rdt" => ["rdt","url"],
],'get',[]);
try {
  $location = new Location();
} catch (\Exception $e) {
  // die($e->getMessage());
  $location = false;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Sign up | <?php echo PRJ_TITLE; ?></title>
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
        <div class="grid-4-laptop grid-5-desktop">
          <div class="sec-div padding -p20">
            <p class="align-c">
              <span class="fa-stack fa-3x">
                <i class="far fa-stack-2x fa-circle"></i>
                <i class="fas fa-stack-1x fa-user-shield"></i>
              </span>
            </p>
            <h1>Account Safety and password tips</h1>
            <p>Your Account Safety starts with your password. Avoid using weak &amp; easily guessable combinations for password.</p>
            <h3>Here are some guide to help you create new password</h3>
            <ul>
              <li>Your password should contain at least one upper case character and lower case character</li>
              <li>Include at least one/more numeric value.</li>
              <li>Also include one/more of these special characters:[</span> <code style="color: red; font-weight:bold">!@#$%^&*()/-_=+{}[];:,<.></code> ]</li>
              <li>A strong should be between 8-24 character length.</li>
              <li>Avoid using your name, phone number, email, date of birth and other personal detail in password combination.</li>
            </ul>
            <p>Build the habit of using strong/random password every time. Remember it is easier to prevent security breach than recovering from it.</p>
          </div>
        </div>
        <div class="grid-9-tablet grid-8-laptop grid-7-desktop push-right">
          <div class="sec-div color face-secondary bg-white drop-shadow">
            <header class="color-bg padding -p20">
              <span class="fa-stack fa-2x push-right">
                <i class="far fa-stack-2x fa-circle"></i>
                <i class="fas fa-stack-1x fa-plus"></i>
              </span>
              <h1> Create your new account</h1>
            </header>
            <div class="padding -p20">
              <p>Fill out fields and click sign up.</p>
              <blockquote>
                <p>Required fields are marked with (<b>*</b>) asterisk</p>
              </blockquote>
              <form
                class="block-ui color asphalt"
                id="user-signup-form"
                method="post"
                action="/SignUp.php"
                data-path="/user/src"
                data-domain="<?php echo WHOST;?>"
                data-validate="false"
                onsubmit="sos.form.submit(this,signUp); return false;"
              >
                <input type="hidden" name="rdt" value="<?php echo !empty($params['rdt']) ? $params['rdt'] : ''; ?>">
                <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('user-signup-form'); ?>">
                <input type="hidden" name="form" value="user-signup-form">
                <h4 class="padding -p5 margin -mnone"> <i class="fas fa-map-marker"></i> Region information</h4>
                <div class="grid-6-tablet">
                  <label><i class="fas fa-asterisk fa-border fa-sm"></i> Choose your country</label>
                  <select name="country_code" required>
                    <option value="">Choose a country</option>
                  <?php if ($countries = (new MultiForm(MYSQL_DATA_DB, 'country', 'code'))->findAll() ) {
                      foreach ($countries as $country) {
                        echo " <option value='{$country->code}'";
                        echo ($location && ($location->country_code == $country->code))
                          ? " selected "
                          : "";
                        echo ">{$country->name}</option>";
                      }
                    }
                   ?>
                  </select>
                </div>
                <div class="grid-6-tablet">
                  <label><i class="fas fa-asterisk fa-border fa-sm"></i> Choose your state/province</label>
                  <select name="state_code" required>
                    <option value="">Choose a state</option>
                    <optgroup label="States">
                      <?php if (($location && !empty($location->country_code)) && $states = (new MultiForm(MYSQL_DATA_DB, 'state', 'code'))->findBySql("SELECT * FROM :db:.:tbl: WHERE country_code='{$location->country_code}' ORDER BY  LOWER(`name`) = '-other', `name` ASC ") ) {
                        foreach ($states as $state) {
                          echo " <option value='{$state->code}'";
                          echo ($location && ($location->state_code == $state->code))
                          ? " selected "
                          : "";
                          echo ">{$state->name}</option>";
                        }
                      }
                      ?>
                    </optgroup>
                  </select>
                </div>
                <br class="c-f">
                <h4 class="padding -p5 margin -mnone"> <i class="fas fa-user"></i> Personal information</h4>
                <div class="grid-6-tablet">
                  <label for="name"><i class="fas fa-asterisk fa-border fa-sm"></i> Name</label>
                  <input type="text" name="name" autocomplete="given-name" id="name" placeholder="Name" required>
                </div>
                <div class="grid-6-tablet">
                  <label for="surname"><i class="fas fa-asterisk fa-border fa-sm"></i> Surname</label>
                  <input type="text" name="surname" autocomplete="family-name" id="surname" placeholder="Surname" required>
                </div>
                <div class="grid-7-tablet">
                  <label for="email"><i class="fas fa-asterisk fa-border fa-sm"></i> Email address</label>
                  <input type="email" name="email" autocomplete="email" id="email" placeholder="myname@website.com" required>
                </div>
                <div class="grid-5-tablet">
                  <label for="phone"> Phone number</label>
                  <input type="tel" name="phone" autocomplete="tel-local" id="phone" placeholder="0801 234 5678">
                </div>
                <div class="grid-7-tablet push-left">
                  <label> <i class="fas fa-venus-mars"></i> Gender </label> <br>
                  <input type="radio" name="sex" value="MALE" id="sex-MALE" checked>
                  <label for="sex-MALE">Male</label>
                  <input type="radio" name="sex" value="FEMALE" id="sex-FEMALE">
                  <label for="sex-FEMALE">Female</label>
                </div>
                <!-- <div class="grid-5-tablet">
                  <label for="dob"> Date of birth</label>
                  <input
                    type="date"
                    name="dob"
                    placeholder="YYYY/MM/DD"
                    id="dob"
                    autocomplete="bday"
                    min="<?php //echo \strftime("%Y-%m-%d",\strtotime("- 85 Years")); ?>"
                    max="<?php //echo \strftime("%Y-%m-%d",\strtotime("- 18 Years")); ?>"
                    >
                </div> -->
                <div class="grid-6-tablet">
                  <label for="password"><i class="fas fa-asterisk fa-border fa-sm"></i> New password</label>
                  <input type="password" name="password" autocomplete="off" placeholder="Password" required id="password">
                </div>
                <div class="grid-6-tablet">
                  <label for="password-repeat"><i class="fas fa-asterisk fa-border fa-sm"></i> Repeat password</label>
                  <input type="password" name="password_repeat" autocomplete="off" placeholder="Password" required id="password-repeat">
                </div>
                <div class="grid-12-tablet">
                  <label><i class="fas fa-asterisk fa-border fa-sm"></i> Kindly read and accept <a href="<?php echo WHOST . "/terms"; ?>" target="_blank"> <b><i class="fas fa-link"></i> Applicable Terms &amp; Conditions</b></a></label> <br>
                  <input type="checkbox" class="solid" name="accepted_terms" value="1" id="accept-terms" required>
                  <label for="accept-terms" class="bold color-text">I have read and accepted terms.</label>
                </div>
                <div class="grid-6-phone grid-4-tablet">
                  <button type="submit" class="btn face-primary"> <i class="fas fa-check"></i> Sign up</button>
                </div>
                <br class="c-f">
              </form>
            </div>
          </div>
          <p class="align-c padding -p20">
            I have an account - <a href="<?php echo Generic::setGet(WHOST . "/user/sign-in",["rdt"=>$params['rdt']]) ?>"> <i class="fas fa-sign-in-alt"></i> Sign in</a>
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
  </body>
</html>
