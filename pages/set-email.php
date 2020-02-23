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
      <div class="sec-div color face-secondary bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-envelope"></i> Change contact email</h1>
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
            id="set-email-form"
            class="block-ui send-otp"
            method="post"
            action="/src/SendEmailOTP.php"
            data-path="/user"
            data-domain="<?php echo WHOST;?>"
            data-validate="false"
            onsubmit="sos.form.submit(this,doneSetting); return false;"
            >
            <input type="hidden" name="MUST_NOT_EXIST" value="1">
            <input type="hidden" name="code_variant" value="<?php echo Data::RAND_MIXED_UPPER; ?>">
            <input type="hidden" name="code_length" value="8">
            <input type="hidden" name="form" value="set-email-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("set-email-form");?>">
            <div class="grid-12-tablet">
              <p class="font-1-3">Your current email is <b><?php echo $session->user->email; ?></b></p>
            </div>
            <div class="grid-7-tablet">
              <label for="email"><i class="fas fa-asterisk fa-border fa-sm"></i> New email</i></label>
              <input type="email" name="email" placeholder="email@gomain.come" autocomplete="email" id="email" required>
            </div>
            <div class="grid-5-tablet">
              <div class="hide-first">
                <label for="otp">Enter OTP code</label>
                <input type="text" placeholder="000 000" class="font-1-3 vcode-text cap" autocomplete="off" name="otp" id="otp">
              </div>
            </div>
            <br class="c-f">
            <div class="grid-7-phone grid-5-tablet">
              <br>
              <button id="submit-form" type="submit" class="btn face-secondary"> <i class="fas fa-angle-right"></i> Continue </button>
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
      var form = $('#set-email-form');
      if (form.hasClass('send-otp')) {
        form.removeClass('send-otp')
          .attr('action','/src/SetEmail.php')
          .data('path','/user');
        $('.hide-first').show();
        $('input[name=otp]').prop('required',true).focus();
        setTimeout(function(){
          removeAlert();
        },2500);
      } else {
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
  }
  (function(){
    $('.hide-first').hide();
  })();
</script>
