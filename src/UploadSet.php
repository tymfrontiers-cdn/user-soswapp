<?php
namespace TymFrontiers;
use \SOS\User;
require_once "../app.init.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
// \require_login();

$post = $_POST; // json data
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$file_upload_max_size = [
  "image" => (1024 * 1024 * 10),
  "audio" => (1024 * 1024 * 18),
  "document" => (1024 * 1024 * 10)
];
$params = $gen->requestParam(
  [
    "user" =>["user","username",5,12],
    "file_type" =>["file_type","option",\array_keys($file_upload_groups)],
    "MIN_FILE_SIZE" =>["MIN_FILE_SIZE","int",(1024 * 10),$file_upload_max_size['image']],
    "MAX_FILE_SIZE" =>["MAX_FILE_SIZE","int",(1024 * 10),$file_upload_max_size['image']],
    "set_as" => ["set_as","text",2,35],
    "set_ses_user" => ["set_ses_user","username",2,35,[],'LOWER'],
    "multi_set" => ["multi_set","boolean"],
    "crop" => ["crop","boolean"],
    "crop_x" => ["crop_x","int"],
    "crop_y" => ["crop_y","int"],
    "crop_w" => ["crop_w","int"],
    "crop_h" => ["crop_h","int"],
    "caption" => ["caption","text",5,55],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["user", "set_as", "file_type", "MIN_FILE_SIZE", "MAX_FILE_SIZE", "user", 'CSRF_token','form']
);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed"
  ]);
  exit;
}

if( !$http_auth ){
  if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
    $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
    echo \json_encode([
      "status" => "3." . \count($errors),
      "errors" => $errors,
      "message" => "Request failed."
    ]);
    exit;
  }
}
$file_db = MYSQL_FILE_DB;
$whost = WHOST;
$save_dir = PRJ_ROOT . "/storage/user-files/{$params['user']}";
// \define('FILE_DB',MYSQL_BASE_DB);
// \define('FILE_TBL',"file");
if (!\is_dir($save_dir)) {
  \mkdir($save_dir,0777,true);
}
// check availability of credentials
$user = new User($params['user']);
if( empty($user->id) ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Unknown [user]; {$params['user']} presented."],
    "message" => "Request halted."
  ]);
  exit;
}
if ((bool)$params['crop'] && ($params['crop_x'] <=0 || $params['crop_y'] <=0)) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Crop properties not present."],
    "message" => "Request halted."
  ]);
  exit;

}
// var_dump ($params['multi_set']);
// exit;
// check for previous settings
$set = false;
if ($set = (new MultiForm(MYSQL_FILE_DB, 'file_default','id'))->findBySql("SELECT * FROM :db:.:tbl: WHERE `user` ='{$params['user']}' AND set_key='{$params['set_as']}' LIMIT 1")) {
  $set = $set[0];
}

if (empty($_FILES)) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No file was attached with request."],
    "message" => "Request halted."
  ]);
  exit;
}
// echo " <tt> <pre>";
// \print_r($_FILES);
// echo "</pre></tt>";
// exit;
$attached_file = $_FILES['file-0-0'];
// check regularity
if (!\in_array($attached_file['type'],$file_upload_groups[$params['file_type']])) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Unaccepted file types selected, choose only: .".\implode(', .',\array_keys($file_upload_groups[$params['file_type']])).' file types'],
    "message" => "Request halted."
  ]);
  exit;
}
if ($attached_file['size'] < $params['MIN_FILE_SIZE'] || $attached_file['size'] > $params['MAX_FILE_SIZE']) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Irregular file byte/size. Must be Min: ".\file_size_unit($params['MIN_FILE_SIZE'])." | Max: ".\file_size_unit($params['MAX_FILE_SIZE'])],
    "message" => "Request halted."
  ]);
  exit;
}
$file = new File();
$file->load($save_dir);
$file->owner = $params['user'];
$file->privacy = "PUBLIC";
$file->caption = $params['caption'];
if (!$file->upload($attached_file)) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["File upload failed."],
    "message" => "Request halted."
  ]);
  exit;
}
$fid = $file->id;
if ($set && !(bool)$params['multi_set']) {
  // delete old file
  $delete = File::findById($set->file_id);
  if ($delete) $delete->destroy();;
}
if (!$set || (bool)$params['multi_set']) $set = new MultiForm(MYSQL_FILE_DB, 'file_default', 'id');
$set->user = $params['user'];
$set->set_key = $params['set_as'];
$set->file_id = $fid;
if (!$set->save()) {
  $file->destroy();
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Failed to update settings, try again later."],
    "message" => "Request halted."
  ]);
  exit;
}
$ses_key = $params['set_ses_user'];
if (!empty($params['set_ses_user']) && $session->isLoggedIn()) $session->user->$ses_key = $_SESSION['user']->$ses_key = $file->url();
// crop file
if ((bool)$params['crop']) {
  $file->crop_img = [
    "x" => $params['crop_x'],
    "y" => $params['crop_y'],
    "w" => $params['crop_w'],
    "h" => $params['crop_h']
  ];
  if (!$file->cropImage()) {
    echo \json_encode([
      "status" => "3.1",
      "errors" => ["Failed to crop image, contact Admin."],
      "message" => "Request incomplete."
    ]);
    exit;
  } else {
    // update file size
    if ($fsize = \filesize($file->fullPath())) {
      $database->query("UPDATE {$file_db}.`file_meta` SET _size = {$fsize} WHERE id={$fid} LIMIT 1");
    }
  }
}
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request completed successfully."
]);
exit;
