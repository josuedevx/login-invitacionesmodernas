<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$appId = $_ENV['FACEBOOK_APP_ID'];
$redirectUri = urlencode($_ENV['BASE_URL'] . '/auth/FBRedirect.php');
$scope = 'email,public_profile';

$fbLoginUrl = "https://www.facebook.com/v18.0/dialog/oauth?client_id={$appId}&redirect_uri={$redirectUri}&scope={$scope}&response_type=code";

header("Location: $fbLoginUrl");
exit;