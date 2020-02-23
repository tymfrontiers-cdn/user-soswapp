<?php
namespace TymFrontiers;
use \SOS\User;
\require_login(true);
$user = User::profile($session->name);
$tym = new BetaTym;
$data = new Data;
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title><?php echo "{$user->name} {$user->surname}"; ?> | <?php echo PRJ_TITLE; ?></title>
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
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/fancybox-soswapp/css/fancybox.min.css">
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/jcrop-soswapp/css/jcrop.min.css">
    <!-- optional plugin -->
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/plugin-soswapp/css/plugin.min.css">
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/dnav-soswapp/css/dnav.min.css">
    <link rel="stylesheet" href="<?php echo WHOST; ?>/7os/faderbox-soswapp/css/faderbox.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="<?php echo \html_style("base.min.css"); ?>">
    <link rel="stylesheet" href="<?php echo WHOST . "/user/assets/css/user.min.css"; ?>">
  </head>
  <body>
    <?php \setup_page("user", "user", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-12-tablet">
          <div class="sec-div padding -p20 border -bthin -bbottom color grey">
            <h1 class="font-3">My dashboard</h1>
            <p>Welcome to your account dashboard</p>
          </div>
        </div>
        <div class="grid-5-laptop">
          <div class="sec-div padding -p20" id="dash-usr">
            <div class="grid-5-tablet grid-12-laptop">
              <div class="align-c color face-primary color-bg" id="avatar-box">
                <a href="<?php echo $session->user->avatar; ?>" data-caption="My profile avatar" data-fancybox="single">
                  <img src="<?php echo $session->user->avatar; ?>" alt="Avatar">
                </a>
                <button type="button" onclick="faderBox.url('<?php echo WHOST . "/user/set-image"; ?>',{set_title:'Profile avatar', set_as : 'USER.AVATAR', set_ses_user : 'avatar', aspect_ratio : 'square', color : 'face-primary'},{exitBtn:true});" id="avatar-set-btn" class="sos-btn face-primary"> <i class="fas fa-edit"></i> Change</button>
              </div>
            </div>
            <div class="grid-7-tablet grid-12-laptop">
              <div class="sec-div">
                <h1> <i class="fas fa-user-crown"></i> <?php echo "{$session->user->name} {$session->user->surname}"; ?></h1>
                <table class="horizontal">
                  <tr title="Account ID/Alias">
                    <th><i class="fas fa-hashtag"></i></th>
                    <td><?php echo "$user->id", (!empty($user->alias) ? " (@{$user->alias})" : ""); ?></td>
                  </tr>
                  <tr title="Contact email address">
                    <th><i class="fas fa-envelope"></i></th>
                    <td><?php echo $user->email; ?></td>
                  </tr>
                  <tr title="Contact phone">
                    <th><i class="fas fa-phone"></i></th>
                    <td><?php echo !empty($user->phone) ? $data->phoneToLocal($user->phone) : '0000 000 0000'; ?></td>
                  </tr>
                  <tr title="Date of Birth">
                    <th><i class="fas fa-birthday-cake"></i></i></th>
                    <td><?php echo !empty($user->dob) ? $tym->MDY($user->dob) : '0000-00-00'; ?></td>
                  </tr>
                  <tr title="Gender">
                    <th><i class="fas fa-venus-mars"></i></i></th>
                    <td><?php echo !empty($user->sex) ? \ucfirst(\strtolower($user->sex)) : 'NULL'; ?></td>
                  </tr>
                  <tr title="Location">
                    <th><i class="fas fa-map-marker"></i></i></th>
                    <td><?php echo  "{$user->state}/{$user->country}"; ?></td>
                  </tr>
                </table>
              </div>
            </div>
            <br class="c-f">
          </div>
        </div>
        <div class="grid-7-laptop"></div>

        <br class="c-f">
      </div>
    </section>
    <?php include PRJ_INC_FOOTER; ?>
    <!-- Required scripts -->
    <script src="<?php echo WHOST; ?>/7os/jquery-soswapp/js/jquery.min.js">  </script>
    <script src="<?php echo WHOST; ?>/7os/js-generic-soswapp/js/js-generic.min.js">  </script>
    <script src="<?php echo WHOST; ?>/7os/fancybox-soswapp/js/fancybox.min.js">  </script>
    <script src="<?php echo WHOST; ?>/7os/jcrop-soswapp/js/jcrop.min.js">  </script>
    <script src="<?php echo WHOST; ?>/7os/theme-soswapp/js/theme.min.js"></script>
    <!-- optional plugins -->
    <script src="<?php echo WHOST; ?>/7os/plugin-soswapp/js/plugin.min.js"></script>
    <script src="<?php echo WHOST; ?>/7os/dnav-soswapp/js/dnav.min.js"></script>
    <script src="<?php echo WHOST; ?>/7os/faderbox-soswapp/js/faderbox.min.js"></script>
    <!-- project scripts -->
    <script src="<?php echo \html_script ("base.min.js"); ?>"></script>
    <script src="<?php echo WHOST . "/user/assets/js/user.min.js"; ?>"></script>
  </body>
</html>
