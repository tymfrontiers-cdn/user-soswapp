<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
\require_login(false);

$errors = [];
$gen = new Generic;
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
          <h1> <i class="fas fa-map-marker"></i> Mailing info</h1>
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
            id="set-mailing-form"
            class="mat-ui"
            method="post"
            action="/src/SetMailingInfo.php"
            data-path="/user"
            data-domain="<?php echo WHOST;?>"
            data-validate="false"
            onsubmit="sos.form.submit(this,doneSetting);return false;"
            >
            <input type="hidden" name="user" value="<?php echo $session->name; ?>">
            <input type="hidden" name="form" value="set-mailing-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("set-mailing-form");?>">

            <div class="grid-8-tablet">
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
            <div class="grid-7-tablet">
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
              <label for="zip-code" class="placeholder">Zip code</label>
              <input type="text" name="zip_code" id="zip-code" value="<?php echo $user->zip_code; ?>" autocomplete="postal-code">
            </div>
            <div class="grid-12-tablet">
              <label for="address" class="placeholder"> Address: House no., street, landmark</label>
              <textarea id="address" name="address" class="autosize" ><?php echo $user->address; ?></textarea>
            </div>

            <div class="grid-7-phone grid-5-tablet">
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
    shrinkPlaceholder();
    $('select[name=state_code]').change(function(){
      if( $(this).val().length > 0 ){
        $('select[name=city_code]').fetchLocal({type:'city',code:$(this).val()});
      }
    });
  })();
</script>
