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
    <div class="grid-7-tablet grid-5-desktop center-tablet">
      <div class="sec-div color face-secondary bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-hashtag"></i> Account alias</h1>
          <p>Your account alias is your easier reference.</p>
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
            id="set-alias-form"
            class="block-ui"
            method="post"
            action="/src/SetAlias.php"
            data-path="/user"
            data-domain="<?php echo WHOST;?>"
            data-validate="false"
            onsubmit="sos.form.submit(this,Saved);return false;"
            >
            <input type="hidden" name="user" value="<?php echo $session->name; ?>">
            <input type="hidden" name="form" value="set-alias-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("set-alias-form");?>">

            <div class="grid-8-phone">
              <label for="alias"><i class="fas fa-asterisk fa-border fa-sm"></i> Alias:  (alphanumeric)</label>
              <input type="text" placeholder="alias" autocomplete="off" name="alias" id="alias" required>
            </div>
            <div class="grid-7-phone grid-4-tablet">
              <br>
              <button id="submit-form" type="submit" class="btn face-secondary"> <i class="fas fa-save"></i> Save </button>
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
  function Saved(data){
    if( data && data.status == '00' || data.errors.length < 1 ){
      if( ('callback' in param) && typeof window[param.callback] == 'function' ){
        faderBox.close();
        window[param.callback](data);
      }else{
        setTimeout(function(){
          faderBox.close();
          removeAlert();
        },1500);
      }
    }
  }
  (function(){
  })();
</script>
