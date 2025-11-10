<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'OAuthHandler.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->setAccessType("offline");
$client->setPrompt("select_account consent");
$client->addScope("email");
$client->addScope("profile");

if (!isset($_GET['code'])) {
    header("Location: /");
    exit;
}


$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
if (isset($token['error'])) {
    exit("Error al obtener token: " . htmlspecialchars($token['error_description'] ?? $token['error']));
}

$client->setAccessToken($token['access_token']);
$google_oauth = new Google_Service_Oauth2($client);
$user_info = $google_oauth->userinfo->get();

$email = $user_info->email;
$name = $user_info->name;
$profile_picture = $user_info->picture;

$nameParts = explode(" ", $name);
$firstName = $nameParts[0] ?? '';
$lastName = isset($nameParts[1]) ? implode(" ", array_slice($nameParts, 1)) : '';

handleOAuthLogin($email, $firstName, $lastName, "Google OAuth");