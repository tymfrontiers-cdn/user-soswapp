<?php
namespace TymFrontiers;
use \SOS\User;
require_once "../app.init.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login();

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
    "alias" =>["alias","username", 5, 12, [], "MIXED"],
    // "user" =>["user","username",5,12],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["alias",'CSRF_token','form']
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
if ((new MultiForm(MYSQL_BASE_DB, "user_alias", "user"))->valExist($params['alias'], "alias")) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[alias]: '{$params['alias']}' is not available."],
    "message" => "Request halted."
  ]);
  exit;
}
$user = $session->name;
$is_new = false;
if (!$alias = (new MultiForm(MYSQL_BASE_DB, "user_alias", "user"))->findById($user)) {
  $is_new = true;
  $alias = new MultiForm(MYSQL_BASE_DB, "user_alias", "user");
}
$alias->user = $user;
$alias->alias = $params['alias'];
$done = $is_new
  ? $alias->create()
  : $alias->update();
if (!$done) {
  $do_errors = [];
  $alias->mergeErrors();
  $more_errors = (new InstanceError($alias))->get('',true);
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
      "message" => "Nothing changed."
    ]);
    exit;
  }
}
$session->user->alias = $_SESSION['user']->alias = $params['alias'];
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
