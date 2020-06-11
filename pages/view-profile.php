<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
\require_login(false);

$errors = [];
$gen = new Generic;
$data = new Data;
$tym = new BetaTym;
$required = ["user"];
$pre_params = [
  "user" => ["user","username",3,12],
  "callback" => ["callback","username",3,35,[],'MIXED']
];
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if ($params && !$user = \SOS\User::profile($params['user'])) {
  $errors[] = "No account/profile found for [user] {$params['user']}";
}
?>

<input
  type="hidden"
  id="rparam"
  <?php if($params){ foreach($params as $k=>$v){
    echo "data-{$k}=\"{$v}\" ";
  } }?>
  >
<div id="fader-flow">
  <div class="view-space">
    <div class="padding -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-8-tablet grid-6-desktop center-tablet">
      <div class="sec-div color face-primary bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-user-crown"></i> My profile </h1>
        </header>

        <div class="padding -p20">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <h3>Your information on file</h3>
            <table class="horizontal">
              <tr>
                <th>Account ID</th>
                <td><?php echo $data->charSplit($user->id,4); ?></td>
              </tr>
              <tr>
                <th>Account alias</th>
                <td><?php echo $user->alias; ?></td>
              </tr>
              <tr>
                <th>Name</th>
                <td><?php echo $user->name; ?></td>
              </tr>
              <tr>
                <th>Surname</th>
                <td><?php echo $user->surname; ?></td>
              </tr>
              <tr>
                <th>Middle name</th>
                <td><?php echo @ $user->middle_name; ?></td>
              </tr>
              <tr>
                <th>Email</th>
                <td><?php echo $user->email; ?></td>
              </tr>
              <tr>
                <th>Phone</th>
                <td><?php echo !empty($user->phone) ? $data->phoneToLocal($user->phone) : '0000 000 0000'; ?></td>
              </tr>
              <tr>
                <th>Sex</th>
                <td><?php echo @ \ucfirst(\strtolower($user->sex)); ?></td>
              </tr>
              <tr>
                <th>Date of birth</th>
                <td><?php echo @ $tym->MDY($user->dob); ?></td>
              </tr>
              <tr>
                <th>Country</th>
                <td><?php echo "{$user->country_code} - {$user->country}"; ?></td>
              </tr>
              <tr>
                <th>State/province</th>
                <td><?php echo $user->state; ?></td>
              </tr>
              <tr>
                <th>City</th>
                <td><?php echo $user->city; ?></td>
              </tr>
              <tr>
                <th>Zip code</th>
                <td><?php echo $user->zip_code; ?></td>
              </tr>
              <tr>
                <th>Address</th>
                <td><?php echo $user->address; ?></td>
              </tr>
            </table>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  var param = $('#rparam').data();
  // (function(){
  // })();
</script>
