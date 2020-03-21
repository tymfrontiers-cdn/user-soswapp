<?php
namespace TymFrontiers;
use \SOS\User,
    \Michelf\Markdown,
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
if( !empty($post['phone']) || !empty($post['country_code']) ){
  $post['phone'] = $data->phoneToIntl(\trim($post['phone']),\trim($post['country_code']));
}

$params = $gen->requestParam(
  [
    "name" =>["name","name"],
    "surname" =>["surname","name"],
    "email" =>["email","email"],
    "phone" =>["phone","tel"],
    "sex" =>["sex","option", ["MALE", "FEMALE"]],

    "country_code" =>["country_code","username",2,2],
    "state_code" =>["state_code","username",5,8],

    "password" =>["password","text",6,16],
    "password_repeat" =>["password_repeat","text",6,16],
    "accepted_terms" =>["accepted_terms","boolean"],

    "rdt" => ["rdt","url"],
    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["name", "surname", "email", "country_code", "state_code", "password", "CSRF_token", "form"]
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
if( $params['password'] !== $params['password_repeat'] ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[password]: does not match [password_repeat]"],
    "message" => "Request halted.",
    "rdt" => ""
  ]);
  exit;
}
if( !(bool)$params['accepted_terms'] ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["You must accept Terms and Conditions"],
    "message" => "Request halted.",
    "rdt" => ""
  ]);
  exit;
}
if( User::valExist($params['email'],"email") ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Email: [{$params['email']}] is not available"],
    "message" => "Request halted.",
    "rdt" => ""
  ]);
  exit;
}
if (!empty($params['phone'])) {
  if( User::valExist($params['phone'],'phone') ){
    echo \json_encode([
      "status" => "3.1",
      "errors" => ["Phone: [{$params['phone']}] is not available"],
      "message" => "Request halted.",
      "rdt" => ""
    ]);
    exit;
  }
}

// create the record
$country_code = $params['country_code'];
$rdt = $params['rdt'];

unset($params['form']);
unset($params['CSRF_token']);
unset($params['password_repeat']);
unset($params['rdt']);
unset($params['accepted_terms']);

// welcome email
$otp_code = Data::uniqueRand('', 12, Data::RAND_MIXED, false);
$otp_ref = Data::uniqueRand('', 16, Data::RAND_MIXED_LOWER);
$otp_qid = NULL;
// $otp_expiry = NULL;
$otp_expiry = \strtotime("+1 Month", \time());
$otp_expiry = \strftime("%Y-%m-%d %H:%M:%S",$otp_expiry);

$auth_param = [
  "user" => $data->encodeEncrypt($params['email']),
  "token" => $data->encodeEncrypt($otp_code),
  "reference" => $data->encodeEncrypt($otp_ref),
  "rdt" => $rdt,
];
$whost = WHOST;
$auth_link = Generic::setGet(WHOST . "/user/verify-email", $auth_param);
$subject = "Welcome to " . PRJ_TITLE;
$prj_title = PRJ_TITLE;
$prj_bot = PRJ_BOT_WELCOME;
$prj_icon = WHOST . "/assets/img/logo.png";
$prj_color_primary = PRJ_PRIMARY_COLOUR;
// OTP

$verify_msg = <<<VMSG
<h3>You need to verify your email</h3>
<p>Please follow the link below to verify your email for a smooth communication experience.</p>
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
border-radius:5px;">Verify your email</a></p>
VMSG;
if (!\file_exists(PRJ_ROOT . "/src/prj-user-welcome.md")) {
$message = <<<WELCOME
<header style="border-bottom: solid 5px {$prj_color_primary}; padding: 12px; margin-bottom: 8px;">
  <a href="{$whost}/user"><img style="max-width:40%; max-height:72px; margin:0 0 3px 3px; float:right" src="{$prj_icon}" alt="Logo"></a>
  <h1 style="margin: 1.5px; float:left; max-width:58%">{$subject}</h1>
  <br style="float:none; clear:both; padding:0; margin:0; height:0px;">
</header>
<section>
  <p>Hi {$params['name']}, <br> <br> My name is Bot. {$prj_bot}, I am excited to welcome you onboard {$prj_title}.
  {$verify_msg}
  <p>You can learn more about us at <a href="{$whost}">{$whost}</a>, <br> If your require any help; don't hesitate to contact me at <a href="{$whost}/contact-us">{$whost}/contact-us</a></p>
  <p>Have a wonderful time, <br> {$prj_bot}.</p>
</section>
WELCOME;
} else {
  $message = Markdown::defaultTransform(\file_get_contents(PRJ_ROOT . "/src/prj-user-welcome.md"));
  $message .= $verify_msg;
}
$message_text = "Hi {$params['name']}, Welcome to {$prj_title} \r\n Kindly follow link: {$auth_link} to verify your email.";
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
    'to' => "{$params['name']} {$params['surname']} <{$params['email']}>",
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
$otp->receiver = "{$params['name']} {$params['surname']} <{$params['email']}>";
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
// register user
$user = new User($params);
if ( empty($user->id()) ) {
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
      "message" => "Failed to create account.",
      "rdt" => ""
    ]);
    exit;
  } else {
    echo \json_encode([
      "status" => "4.1",
      "errors" => ["Failed to create account, try again later."],
      "message" => "Request failed",
      "rdt" => ""
    ]);
    exit;
  }
}

$user = User::authenticate($params['email'],$params["password"],$country_code);
if( !$user ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Automatic login failed, contact Admin."],
    "message" => "Login failed",
    "rdt" => ""
  ]);
  exit;
}
$remember = \strtotime("+ 1 Hour");
$session->login($user,$remember);
$rdt = empty($rdt)
  ? WHOST . "/user"
  : $rdt;
$rdt = Generic::setGet( WHOST . "/user/email-verification", ["rdt" => $rdt, "reference"=>$data->encodeEncrypt($otp_ref)]);
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Your account was created, and you are now logged in.",
  "rdt" => $rdt
]);
exit;
