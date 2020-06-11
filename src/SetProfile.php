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
$params = $gen->requestParam(
  [
    "user" =>["user","username",5,12],
    "name" =>["name","name"],
    "surname" =>["surname","name"],
    "dob" =>[
      "dob",
      "date",
      \strftime("%Y-%m-%d",\strtotime("- 85 Years")),
      \strftime("%Y-%m-%d",\strtotime("- 18 Years"))
    ],
    "sex" =>["sex","option",["MALE","FEMALE"]],
    "state_code" =>["state_code","username",5,5],
    "city_code" =>["city_code","username",5,8],
    "zip_code" =>["zip_code","username",5,8],
    "address" =>["address","text",5,128],

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

$user = (new MultiForm(MYSQL_BASE_DB,'user_profile','user'))->findById($params['user']);
if( !$user ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["User profile not found.",$database->last_query],
    "message" => "Request failed"
  ]);
  exit;
}

foreach ($params as $prop=>$value) {
  if (!empty($value)) $user->$prop = $value;
}
if (!$user->update()) {
  $do_errors = [];
  $user->mergeErrors();
  $more_errors = (new InstanceError($user))->get('',true);
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
      "message" => "No profile info was changed."
    ]);
    exit;
  }
}
// die($database->last_query);
foreach ($params as $prop=>$value) {
  if (!empty($value) && \property_exists($session->user,$prop)) $session->user->$prop = $_SESSION['user']->$prop = $value;
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Profile updated!"
]);
exit;
