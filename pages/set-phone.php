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
          <h1> <i class="fas fa-phone"></i> My contact phone</h1>
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
            id="set-phone-form"
            class="block-ui"
            method="post"
            action="/src/SetPhone.php"
            data-path="/user"
            data-domain="<?php echo WHOST;?>"
            data-validate="false"
            onsubmit="sos.form.submit(this,doneSetting);return false;"
            >
            <input type="hidden" name="user" value="<?php echo $user->id; ?>">
            <input type="hidden" name="country_code" value="<?php echo $user->country_code; ?>">
            <input type="hidden" name="form" value="set-phone-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("set-phone-form");?>">

            <div class="grid-8-tablet">
              <label for="phone"><i class="fas fa-asterisk fa-border fa-sm"></i> Phone number</label>
              <input type="tel" placeholder="0801 234 5678" name="phone" id="phone" value="<?php echo !empty($user->phone) ? $data->phoneToLocal($user->phone) : ""; ?>" autocomplete="tel-local">
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
