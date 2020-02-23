<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
\require_login(false);

$errors = [];
$gen = new Generic;
$data = new Data;
$required = [];
$pre_params = [
  "callback" => ["callback","username",3,35,[],'MIXED']
];
// if( empty($_GET['id']) ) $required[] = 'owner';
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
$user = \SOS\User::find($session->name);
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
      <div class="sec-div color blue bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-user-circle"></i> My profile</h1>
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
            <form
            id="set-profile-form"
            class="block-ui"
            method="post"
            action="/src/SetProfile.php"
            data-path="/user"
            data-domain="<?php echo WHOST;?>"
            data-validate="false"
            onsubmit="sos.form.submit(this,doneSetting);return false;"
            >
            <input type="hidden" name="user" value="<?php echo $user->id; ?>">
            <input type="hidden" name="country_code" value="<?php echo $user->country_code; ?>">
            <input type="hidden" name="form" value="set-profile-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("set-profile-form");?>">

            <div class="grid-6-tablet">
              <label for="name"><i class="fas fa-asterisk fa-border fa-sm"></i> Name </label>
              <input type="text" placeholder="First name" autocomplete="given-name" name="name" value="<?php echo $user->name ?>" id="name" required>
            </div>
            <div class="grid-6-tablet">
              <label for="surname"><i class="fas fa-asterisk fa-border fa-sm"></i> Surname </label>
              <input type="text" placeholder="Surname" autocomplete="family-name" name="surname" value="<?php echo $user->surname ?>" id="surname" required>
            </div>
            <div class="grid-5-tablet">
              <label for="dob" class="placeholder shrink"><i class="fas fa-asterisk fa-border fa-sm"></i> Date of Birth</label>
              <input
                type="date"
                name="dob"
                id="dob"
                required
                autocomplete="bday"
                min="<?php echo \strftime("%Y-%m-%d",\strtotime("- 85 Years")); ?>"
                max="<?php echo \strftime("%Y-%m-%d",\strtotime("- 18 Years")); ?>"
                value="<?php echo $user->dob; ?>"
              >
            </div>
            <div class="grid-6-tablet">
              <label class="bold"><i class="fas fa-asterisk fa-border fa-sm"></i> Sex</label> <br>
              <input type="radio" class="solid" name="sex" id="sex-male" value="MALE" <?php echo $user->sex == 'MALE' ? 'checked' : ''; ?>>
              <label for="sex-male" class="color-text">Male</label>

              <input type="radio" class="solid" name="sex" id="sex-female" value="FEMALE" <?php echo $user->sex == 'FEMALE' ? 'checked' : ''; ?>>
              <label for="sex-female" class="color-text">Female</label>
            </div>
            <br class="c-f">
            <div class="grid-6-tablet">
              <label class="placeholder shrink "><i class="fas fa-asterisk fa-border fa-sm"></i> State/province</label>
              <select name="state_code" required>
                <option value="">* Choose state</option>
                <optgroup label="States">
                  <?php
                  $states = (new MultiForm(MYSQL_DATA_DB,'state','code'))->findBySql("SELECT name,code FROM :db:.:tbl: WHERE country_code = '{$database->escapeValue($user->country_code)}' ORDER BY name ASC");
                  if( !empty($states) ){
                    foreach($states as $state){
                      echo " <option value='{$state->code}'";
                      echo $user->state_code == $state->code
                        ? ' selected'
                        : '';
                      echo ">{$state->name}</option>";
                    }
                  }
                  ?>
                </optgroup>
              </select>
            </div>
            <div class="grid-6-tablet">
              <label class="placeholder shrink"><i class="fas fa-asterisk fa-border fa-sm"></i> City</label>
              <select name="city_code" required>
                <option value="">* Choose city</option>
                <optgroup label="Cities">
                  <?php
                  $cities = (new MultiForm(MYSQL_DATA_DB,'city','code'))->findBySql("SELECT name,code FROM :db:.:tbl: WHERE state_code = '{$database->escapeValue($user->state_code)}' ORDER BY name ASC");
                  if( !empty($cities) ){
                    foreach($cities as $city){
                      echo " <option value='{$city->code}'";
                      echo $user->city_code == $city->code
                        ? ' selected'
                        : '';
                      echo ">{$city->name}</option>";
                    }
                  }
                  ?>
                </optgroup>
              </select>
            </div>
            <div class="grid-5-tablet">
              <label for="zip-code">Zip code</label>
              <input type="text" placeholder="ZIPCODE" name="zip_code" id="zip-code" value="<?php echo $user->zip_code; ?>" autocomplete="postal-code">
            </div>
            <div class="grid-12-tablet">
              <label for="address" class="placeholder"> Address: House no., street, landmark</label>
              <textarea id="address" name="address" class="autosize" ><?php echo $user->address; ?></textarea>
            </div>
            <div class="grid-7-phone grid-4-tablet">
              <br>
              <button id="submit-form" type="submit" class="btn blue"> <i class="fas fa-save"></i> Save </button>
            </div>

            <br class="c-f">
          </form>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  var param = $('#rparam').data();
  function doneSetting(data){
    if( data && data.status == '00' || data.errors.length < 1 ){
      if( ('callback' in param) && typeof window[param.callback] == 'function' ){
        faderBox.close();
        window[param.callback](data);
      }else{
        setTimeout(function(){
          faderBox.close();
          removeAlert();
        },3500);
      }
    }
  }
  (function(){
    $('select[name=state_code]').change(function(){
      if( $(this).val().length > 0 ){
        $('select[name=city_code]').fetchLocal({type:'city',code:$(this).val()});
      }
    });
  })();
</script>
