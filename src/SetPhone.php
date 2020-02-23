<?php
namespace TymFrontiers;
use \SOS\User;
require_once "../app.init.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
// \require_login();

$data = new Data;
\header("Content-Type: application/json");
$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : (
    !empty($_GET) ? $_GET : []
    )
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
if( !empty($post['phone']) || !empty($post['country_code']) ){
  $post['phone'] = $data->phoneToIntl(\trim($post['phone']),\trim($post['country_code']));
}
$params = $gen->requestParam(
  [
    "user" =>["user","username",5,12],
    "phone" =>["phone","tel"],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["user",'CSRF_token','form']
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

$user = (new MultiForm(MYSQL_BASE_DB,'user','_id'))->findById($params['user']);
if( !$user ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["User info not found.",$database->last_query],
    "message" => "Request failed"
  ]);
  exit;
}
if(\SOS\User::valExist($params['phone'],'phone') && $params['phone'] !== $user->phone ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[phone]: {$params['phone']} not available."],
    "message" => "Request failed"
  ]);
  exit;
}
$base_db = MYSQL_BASE_DB;
if (!$database->query("UPDATE `{$base_db}`.`user` SET phone='{$params['phone']}' WHERE _id='{$database->escapeValue($params['user'])}' LIMIT 1")) {
  $do_errors = [];
  // $user->mergeErrors();
  $more_errors = (new InstanceError($database))->get('',true);
  if (!empty($more_errors)) {
    foreach ($more_errors as $method=>$errs) {
      foreach ($errs as $err){
        $do_errors[] = $err;
      }
    }
    echo \json_encode([
      "status" => "4." . \count($do_errors),
      "errors" => $do_errors,
      "message" => "Failed to complete request, try again later."
    ]);
    exit;
  } else {
    echo \json_encode([
      "status" => "0.1",
      "errors" => [],
      "message" => "Phone not updated."
    ]);
    exit;
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Profile updated!"
]);
exit;
