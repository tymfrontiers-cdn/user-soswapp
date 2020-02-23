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
    "email" =>["email","email"],
    "otp" =>["otp","username",3,16],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["email","otp",'CSRF_token','form']
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

if (!$user = User::find($session->name,'id') ) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to retrieve logged in user."],
    "message" => "Request halted."
  ]);
  exit;
}
$otp = new OTP\Email($mailgun_api_domain, $mailgun_api_key);

if (!$otp->verify($params['email'],$params['otp'])) {
  echo \json_encode([
    "status" => "4.2",
    "errors" => ["Invalid/expired OTP code presented.", "Enter a valid OTP code from your email or reload the page and start afresh."],
    "message" => "Request failed."
  ]);
  exit;
}
$base_db = MYSQL_BASE_DB;
if (!$database->query("UPDATE {$base_db}.user SET email='{$database->escapeValue($params['email'])}' WHERE _id='{$database->escapeValue($user->id)}' LIMIT 1")) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to save new email, Try again later."]
  ]);
  exit;
}
$session->user->email = $_SESSION['user']->email = $params['email'];

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
