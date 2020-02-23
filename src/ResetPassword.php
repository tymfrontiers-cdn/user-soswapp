<?php
namespace TymFrontiers;
use \SOS\User,
    \Mailgun\Mailgun;
require_once "../app.init.php";
require_once APP_BASE_INC;

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
    "email" =>["email","email"],

    "rdt" => ["rdt","url"],
    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["email", "CSRF_token", "form"]
);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed",
    "rdt" => ""
  ]);
  exit;
}

if( !$http_auth ){
  if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
    $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
    echo \json_encode([
      "status" => "3." . \count($errors),
      "errors" => $errors,
      "message" => "Request failed.",
      "rdt" => ""
    ]);
    exit;
  }
}
//
// echo " <tt> <pre>";
// print_r($params);
// echo "</pre></tt>";
// exit;
if( !User::valExist($params['email'],"email") ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No account found for Email: [{$params['email']}]"],
    "message" => "Request halted.",
    "rdt" => ""
  ]);
  exit;
}

// create the record
$rdt = $params['rdt'];


// welcome email
$otp_code = Data::uniqueRand('', 12, Data::RAND_MIXED, false);
$otp_ref = Data::uniqueRand('', 16, Data::RAND_MIXED_LOWER);
$otp_qid = NULL;
// $otp_expiry = NULL;
$otp_expiry = \strtotime("+3 Hours", \time());
$otp_expiry = \strftime("%Y-%m-%d %H:%M:%S",$otp_expiry);

$auth_param = [
  "user" => $data->encodeEncrypt($params['email']),
  "token" => $data->encodeEncrypt($otp_code),
  "rdt" => $rdt,
];
$whost = WHOST;
$auth_link = Generic::setGet(WHOST . "/user/do-pwd-reset", $auth_param);
$subject = "Password reset instruction";
$prj_title = PRJ_TITLE;
$prj_bot = PRJ_BOT_HELP;
$prj_icon = WHOST . "/assets/img/logo.png";
$prj_color_primary = PRJ_PRIMARY_COLOUR;
// OTP

$verify_msg = <<<VMSG
<h3>You requested password reset</h3>
<p>Please follow the link below to create new password.</p>
<p><a href="{$auth_link}"
style="font-weight:bold;
display:inline-block;
padding:12px 15px;
background-color:#e4e4e4;
color:black;
border:solid 3px #cbcbcb;
text-decoration:none;
-webkit-border-radius:5px;
-ms-border-radius:5px;
-moz-border-radius:5px;
border-radius:5px;">Reset now</a></p>
VMSG;
$message = <<<WELCOME
<header style="border-bottom: solid 5px {$prj_color_primary}; padding: 12px; margin-bottom: 8px;">
  <a href="{$whost}/user"><img style="width:auto; height:72px; margin:0 0 3px 3px; float:right" src="{$prj_icon}" alt="Logo"></a>
  <h1 style="margin: 1.5px">{$subject}</h1>
  <br style="float:none; clear:both; padding:0; margin:0; height:0px;">
</header>
<section>
  {$verify_msg}
  <p><b>NOTE:</b> If you did not request a password reset, ignore this message and do not forward this message to anyone.</p>
  <p>Have a wonderful time, <br> {$prj_bot}.</p>
</section>
WELCOME;

$message_text = "Hi, Please follow link: {$auth_link} to reset your login password.";
// send message right away
if (empty($mailgun_api_key) || empty($mailgun_api_domain)) {
  echo \json_encode([
    "status" => "5.1",
    "errors" => ["Please contact developer to create Mailgun API and assign \$mailgun_api_domain and \$mailgun_api_key respectively."],
    "message" => "Variable error.",
    "rdt" => ""
  ]);
  exit;
}
try {
  $mgClient = Mailgun::create($mailgun_api_key);
  $result = $mgClient->messages()->send($mailgun_api_domain, [
    'from' => PRJ_SUPPORT_EMAIL,
    'to' => "{$params['email']}",
    'subject' => $subject,
    'text' => $message_text,
    'html' => $message
  ]);
  if(
    \is_object($result) &&
    !empty($result->getId()) &&
    \strpos($result->getId(), $mailgun_api_domain) !== false
  ){
    $otp_qid = $result->getId();
  }
} catch (\Exception $e) {
  echo \json_encode([
    "status" => "5.2",
    "errors" => ["We were unable to email you, confirm that you have entered a correct receiving email and try again.", $e->getMessage()],
    "message" => "Communication error.",
    "rdt" => ""
  ]);
  exit;
}
// save detail
$otp = new MultiForm(MYSQL_LOG_DB, 'otp_email', 'id');
$otp->ref = $otp_ref;
$otp->user = $params['email'];
$otp->qid = $otp_qid;
$otp->code = $otp_code;
$otp->expiry = $otp_expiry;
$otp->subject = $subject;
$otp->message = $message;
$otp->message_text = $message_text;
$otp->sender = PRJ_SUPPORT_EMAIL;
$otp->receiver = "{$params['email']}";
if (!$otp->create()) {
  if (!empty($otp->errors['query'])) {
    $errs = (new InstanceError($otp, true))->get("query",true);
    echo \json_encode([
      "status" => "4." . \count($errs),
      "errors" => $errs,
      "message" => "Communication error.",
      "rdt" => ""
    ]);
    exit;
  }
}
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Your password reset link has been sent to your email.",
  "reference" => $otp_ref
]);
exit;
