<?php
namespace TymFrontiers;
use \SOS\User;
require_once "../app.init.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");

$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : []
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$params = $gen->requestParam(
  [
    "user" =>["user","email"],
    "rdt" =>["rdt","url"],
    "token" =>["token","text", 4, 56],
    "password" =>["password","text", 8, 22],
    "password_repeat" =>["password_repeat","text", 8, 22],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["token", "user", "password","password_repeat",'CSRF_token','form']
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

$user = User::valExist($params["user"],"email");
if( !$user ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Invalid user/email supplied."],
    "message" => "Request failed"
  ]);
  exit;
}
if ( $params['password'] !== $params['password_repeat']) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[password] and [password_repeat] does not match."],
    "message" => "Request failed"
  ]);
  exit;
}
$pwd = Data::pwdHash($params['password']);
// change password
$base_db = MYSQL_BASE_DB;
$conn = new MySQLDatabase(MYSQL_SERVER, MYSQL_DEVELOPER_USERNAME, MYSQL_DEVELOPER_PASS);
if (!$conn->query("UPDATE {$base_db}.`user` SET password='{$conn->escapeValue($pwd)}' WHERE `email` = '{$conn->escapeValue($params['user'])}' LIMIT 1")) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to change password, please try again later."],
    "message" => "Request failed."
  ]);
  exit;
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!",
  "rdt" => !empty($params['rdt']) ? $params['rdt'] : WHOST . "/user/login"
]);
exit;
