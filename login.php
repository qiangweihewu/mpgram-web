<?php

include 'redirect.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'mp.php';

MP::startSession();

// Set JSON content type for all responses
header('Content-Type: application/json');

if(!defined('LOGIN_CAPTCHA')) define('LOGIN_CAPTCHA', true);

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

// Get JSON input for API requests
$json_input = file_get_contents('php://input');
if ($json_input) {
    $data = json_decode($json_input, true);
    if ($data && isset($data['phone'])) {
        $_POST['phone'] = $data['phone'];
    }
}

$theme = 0;
$ua = '';
$iev = MP::getIEVersion();
if($iev > 0 && $iev < 4) $theme = 1;
$theme = MP::getSettingInt('theme', $theme, true);
$post = (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Series60/3') === false) && ($iev < 4 && $iev == 0);

$lng = MP::initLocale();

function json_error($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function json_success($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

set_error_handler('exceptions_error_handler');

include 'themes.php';
Themes::setTheme($theme);

$revoked = isset($_GET['revoked']);
$logout = false;
$wrong = isset($_GET['wrong']);

$user = null;
$nouser = true;

$phone = $_GET['phone'] ?? $_POST['phone'] ?? null;

$user = MP::getUser();

$ipass = $_GET['ipass'] ?? $_POST['ipass'] ?? null;

// Check session existance
$nouser = $user == null || $user === false || empty($user) || strlen($user) < 32 || strlen($user) > 200 || !file_exists(sessionspath.$user.'.madeline');

function removeSession($logout=false) {
    global $user;
    $_SESSION = [];
    MP::delcookie('user');
    MP::delcookie('code');
    MP::delcookie('PHPSESSID');
    try {
        // Remove all session files
        if(file_exists(sessionspath.$user.'.madeline')) {
            if($logout) {
                try {
                    $MP = MP::getMadelineAPI($user, true);
                    $MP->logout();
                    unset($MP);
                } catch (Exception) {}
            }
            try {
                if(PHP_OS_FAMILY === "Linux") {
                    exec('kill -9 `ps -ef | grep -v grep | grep '.$user.'.madeline | awk \'{print $2}\'`');
                }
            } catch (Exception) {}
            MP::deleteSessionFile($user);
        }
    } catch (Exception $e) {
        json_error($e->getMessage());
    }
}

if(isset($_GET['logout']) || $revoked || $wrong) {
    $logout = true;
    $nouser = true;
    removeSession(($_GET['logout'] ?? '') == '2' && !$nouser);
    $user = null;
}

$MP = null;
if($user != null && !$logout && !$nouser) {
    // Already logged in
    if(isset($_COOKIE['code']) && !empty($_COOKIE['code'])) {
        json_success(['redirect' => 'chats.php']);
    } else {
        $MP = MP::getMadelineAPI($user, true);
        if($MP->getAuthorization() === 3) {
            MP::cookie('code', '1', time() + (86400 * 365));
            json_success(['redirect' => 'chats.php']);
        }
        if($phone === null) {
            unset($MP);
            removeSession();
            json_success([
                'needPhone' => true,
                'revoked' => $revoked,
                'wrong' => $wrong
            ]);
        }
    }
}

if(defined('INSTANCE_PASSWORD') && INSTANCE_PASSWORD !== null) {
    if($ipass === null || $ipass != INSTANCE_PASSWORD) {
        json_error('Instance password required');
    }
}

if($phone !== null) {
    $p = $phone;
    if(empty($p) || strlen($p) < 10 || !is_numeric(str_replace('-','',str_replace('+','', $p)))) {
        json_error($lng['wrong_number_format']);
    }
    
    if(!isset($_SESSION['captcha_entered']) && LOGIN_CAPTCHA) {
        if(!isset($_POST['c']) && !isset($_GET['c'])) {
            json_success(['needCaptcha' => true]);
        } else {
            $c = $_POST['c'] ?? $_GET['c'] ?? null;
            if(!isset($_SESSION['captcha']) || strtolower($c) !== $_SESSION['captcha']) {
                json_error($lng['wrong_captcha']);
            }
            $_SESSION['captcha_entered'] = 1;
        }
    }

    if(!isset($user) || $nouser) {
        $_SESSION['user'] = $user = rtrim(strtr(base64_encode(hash('sha384', sha1(md5($phone.rand(0,1000).random_bytes(6))).random_bytes(30), true)), '+/', '-_'), '=');
        MP::cookie('user', $user, time() + (86400 * 365));
        $MP = MP::getMadelineAPI($user, true);
    }

    try {
        if(isset($_POST['pass']) || isset($_GET['pass'])) {
            $password = $_POST['pass'] ?? $_GET['pass'] ?? null;
            $MP->complete2faLogin($password);
            MP::cookie('code', '1', time() + (86400 * 365));
            json_success(['redirect' => 'chats.php']);
        } elseif(isset($_POST['code']) || isset($_GET['code'])) {
            $code = $_POST['code'] ?? $_GET['code'] ?? null;
            if(!empty($code) && is_numeric($code)) {
                $a = $MP->completePhoneLogin($code);
                if(isset($a['_'])) {
                    switch($a['_']) {
                        case 'account.noPassword':
                            json_error($lng['no_pass_code']);
                        case 'account.password':
                            json_success(['needPassword' => true]);
                        case 'account.needSignup':
                            json_error($lng['need_signup']);
                        default:
                            MP::cookie('code', '1', time() + (86400 * 365));
                            json_success(['redirect' => 'chats.php']);
                    }
                }
            }
            json_success(['needCode' => true]);
        }

        $MP->phoneLogin($phone);
        json_success(['needCode' => true]);
        
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if(strpos($msg, 'PHONE_NUMBER_INVALID') !== false) {
            json_error($lng['wrong_number_format']);
        } elseif(strpos($msg, 'PHONE_CODE_INVALID') !== false) {
            json_error($lng['phone_code_invalid']);
        } elseif(strpos($msg, 'PHONE_CODE_EXPIRED') !== false) {
            json_error($lng['phone_code_expired']);
        } else {
            json_error($msg);
        }
    }
} else {
    json_success([
        'needPhone' => true,
        'revoked' => $revoked,
        'wrong' => $wrong
    ]);
}