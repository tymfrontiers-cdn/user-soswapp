<?php
namespace TymFrontiers;
require_once "../app.init.php";
require_once APP_BASE_INC;
\require_login(false);

$errors = [];
$gen = new Generic;
$required = ['set_as','set_title','aspect_ratio'];
$aspect_ration = [
  'square' => 1,
  'rectangle' => 2
];
$pre_params = [
  "callback" => ["callback","username",3,35,[],'MIXED'],
  "color" => ["color","username",3,35,[],'LOWER',['-']],
  "set_as" => ["set_as","text",3,96],
  "set_title" => ["set_title","text",3,35],
  "set_ses_user" => ["set_ses_user","text",3,35],
  "aspect_ratio" => ["aspect_ratio","option",\array_keys($aspect_ration)],
];
// if( empty($_GET['id']) ) $required[] = 'owner';
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
$set_file = false;
if (!empty($params['set_as'])) {
  if ($set = (new MultiForm(MYSQL_FILE_DB, 'file_default','id'))->findBySql("SELECT file_id AS fid FROM :db:.:tbl: WHERE `user` ='{$session->name}' AND set_key='{$params['set_as']}' LIMIT 1")) {
    $set_file = File::findById($set[0]->fid);
  }
}
$upload_accept = [];
foreach ($file_upload_groups['image'] as $ext=>$mime) {
  $upload_accept[] = ".{$ext}";
  $upload_accept[] = $mime;
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
    <br class="c-f">
    <div class="grid-11-laptop grid-10-desktop center-laptop">
      <div class="sec-div color <?php echo !empty($params['color']) ? $params['color'] : 'grey'; ?> bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-image"></i> <?php echo $params['set_title']; ?></h1>
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
            <div id="crop-base">
              <img src="<?php if ($set_file) echo $set_file->url(); ?>" class="prev-set" id="crop-image" alt="">
              <label for="input-image" class="sos-btn <?php echo !empty($params['color']) ? $params['color'] : 'grey'; ?>" id="input-trigger"> <i class="fas fa-image"></i> Choose new photo</label>
            </div>
            <br>
            <form
              id="set-image-form"
              method="post"
              class="block-ui padding -p20"
              enctype="multipart/form-data"
              action="/src/UploadSet.php"
              data-path="/user"
              data-domain="<?php echo WHOST;?>"
              data-validate="false"
              onsubmit="sos.form.submit(this,saved);return false;"
            >
            <input type="hidden" name="MIN_FILE_SIZE" value="<?php echo 1024 * 15; ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 1024 * 1024 * 5; ?>">
            <input type="hidden" name="file_type" value="image">
            <input type="hidden" name="set_as" value="<?php echo $params['set_as']; ?>">
            <input type="hidden" name="set_ses_user" value="avatar">
            <input type="hidden" name="crop" value="0">
            <input type="hidden" name="crop_x" value="">
            <input type="hidden" name="crop_y" value="">
            <input type="hidden" name="crop_w" value="">
            <input type="hidden" name="crop_h" value="">
            <input type="hidden" name="user" value="<?php echo $session->name; ?>">
            <input type="hidden" name="form" value="set-image-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("set-image-form");?>">
            <input
              type="file"
              name="avatar"
              id="input-image"
              class="hidden"
              required
              accept="<?php echo \implode(',',$upload_accept); ?>"
            >
            <div class="grid-8-tablet hide-first">
              <p> <i class="fas fa-info-circle"></i> Drag to select crop area</p>
            </div>
            <div class="grid-4-tablet hide-first">
              <button type="button" onclick="prepSubmit();" class="sos-btn <?php echo !empty($params['color']) ? $params['color'] : 'grey'; ?>"> <i class="fas fa-upload"></i> Crop & Save</button>
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
  var params = $('#rparam').data();
  params.cropProp = {
    crop_x : 0,
    crop_y : 0,
    crop_w : 0,
    crop_h : 0
  };
  var aspect_ratio = {
    square : 1,
    rectangle : 2
  };
  function saved(data){
    if( data && data.status == '00' || data.errors.length < 1 ){
      if( ('callback' in params) && typeof window[params.callback] == 'function' ){
        faderBox.close();
        window[params.callback](data);
      }else{
        setTimeout(function(){
          faderBox.close();
          removeAlert();
        },1500);
      }
    }
  }
  function prepSubmit() {
    if ( params.cropProp.crop_y > 0 || params.cropProp.crop_x > 0) {
      $('input[name=crop]').val(1);
      $.each(params.cropProp, function(i,v){
        $('input[name='+i+']').val(v);
      });
    }
    $('#set-image-form').submit();
  }
  (function(){
    $('.hide-first').hide();
    $('#input-image').change(function(){
      if(this.files.length > 0){
        $('#crop-image').readFileURL(this);
        $('#crop-base').addClass('engaged');
        $('#crop-image').removeClass('prev-set');
        $('.hide-first').show();
        setTimeout(function(){
          $('#crop-image').Jcrop({
            aspectRatio: aspect_ratio[params.aspect_ratio],
            onSelect: function(c){
              params.cropProp.crop_x = c.x;
              params.cropProp.crop_y = c.y;
              params.cropProp.crop_w = c.w;
              params.cropProp.crop_h = c.h;
            },
            minSize : [( params.aspect_ratio == 'square' ? 280 : 380 ), 0],
            boxWidth : $(document).find('#crop-base').innerWidth()
          });
        },1500);
      } else {
        $('#crop-base').removeClass('engaged');
      }
    });
  })();
</script>
