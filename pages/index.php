<?php
namespace TymFrontiers;
use \SOS\User;
\require_login(true);
$user = User::profile($session->name);
$tym = new BetaTym;
$data = new Data;
$dashlist = (new MultiForm(MYSQL_BASE_DB, "user_dashlist", "id"))->findAll();
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
    <script type="text/javascript">
      let avatar_param = {
        type : "image",
        owner : "<?php echo $session->name; ?>",
        set_dmn : "<?php echo PRJ_BASE_DOMAIN; ?>",
        set_as : "USER.AVATAR",
        set_ttl : "Set as profile avatar",
        set_cb : "updateAvatar",
        crp_cb : "updateAvatar",
        upl_multiple : 0,
        crp_shape : "square"
      }
    </script>
  </head>
  <body>

    <?php \TymFrontiers\Helper\setup_page("user", "user", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-4-laptop">
          <div class="sec-div padding -p20" id="dash-usr">
            <div class="grid-5-tablet grid-12-laptop">
              <div class="align-c color face-primary color-bg" id="avatar-box">
                <a href="<?php echo $session->user->avatar; ?>" data-caption="My profile avatar" data-fancybox="single">
                  <img src="<?php echo $session->user->avatar; ?>" alt="Avatar">
                </a>
                <button type="button" onclick="sos.faderBox.url(location.origin + '/file-manager/uploader-popup', avatar_param, {exitBtn:true});" id="avatar-set-btn" class="sos-btn face-primary"> <i class="fas fa-edit"></i> Change</button>
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
                <br class="c-f">

                <button type="button" class="sos-btn sht-btn" name="button" onclick="location.href = '<?php echo WHOST . "/user/settings" ?>'"><i class="fas fa-user-cog"></i> Account setting</button>

                <button type="button" class="sos-btn grey sht-btn" onclick="sos.faderBox.url(location.origin + '/user/view-profile', {user : '<?php echo $user->id; ?>'}, {exitBtn: true});"> <i class="fas fa-user-circle"></i> View full profile</button>

                <button type="button" onclick="sos.faderBox.url(location.origin + '/file-manager/uploader-popup', avatar_param, {exitBtn:true});" id="usr-avatar-set-btn" class="sos-btn sht-btn"> <i class="fas fa-edit"></i> Change profile avatar</button>
              </div>
            </div>
            <br class="c-f">
          </div>
        </div>
        <div class="grid-8-laptop">
          <div class="sec-div padding -p20">
            <h1>Get started</h1>
            <p>Tools and links to get you going.</p>
            <ul id="usr-dashlist">
            <?php if ($dashlist): foreach($dashlist as $dash): ?>
              <li>
                <a href="<?php echo $dash->path; ?>" <?php if(!empty($dash->onclick)) { echo $dash->onclick; } ?> <?php if ( !empty($dash->classname)) { echo "class='{$dash->classname}'"; } ?>>
                  <span class="fa-stack fa-3x">
                    <i class="fas fa-circle fa-stack-2x"></i>
                    <i class="fad <?php echo $dash->icon; ?> fa-stack-1x fa-inverse"></i>
                  </span>
                </a>
                <h3><a href="<?php echo $dash->path; ?>" <?php if(!empty($dash->onclick)) { echo $dash->onclick; } ?> <?php if ( !empty($dash->classname)) { echo "class='{$dash->classname}'"; } ?>><?php echo $dash->title; ?></a></h3>
                <p><?php echo $dash->subtitle; ?></p>
              </li>
            <?php endforeach; endif; ?>
            </ul>
          </div>

        </div>

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
