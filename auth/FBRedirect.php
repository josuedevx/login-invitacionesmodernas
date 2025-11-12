<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'OAuthHandler.php';

$appId = $_ENV['FACEBOOK_APP_ID'];
$appSecret = $_ENV['FACEBOOK_APP_SECRET'];
$redirectUri = $_ENV['BASE_URL'] . '/auth/FBRedirect.php';
// $redirectUri = $_ENV['FACEBOOK_REDIRECT_URI'];

if (!isset($_GET['code']) || $_GET['error'] === 'access_denied') {
    // echo "Error: no se recibió el código.";
    header('Location: /./');
    exit;
}

$code = $_GET['code'];


$tokenUrl = "https://graph.facebook.com/v18.0/oauth/access_token?" . http_build_query([
    'client_id' => $appId,
    'redirect_uri' => $redirectUri,
    'client_secret' => $appSecret,
    'code' => $code,
]);

$response = file_get_contents($tokenUrl);
$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    // echo "Error al obtener token de acceso";
    header('Location: /./');
    exit;
}

$accessToken = $tokenData['access_token'];
$userInfoUrl = "https://graph.facebook.com/me?fields=id,name,email&access_token={$accessToken}";
$userInfoResponse = file_get_contents($userInfoUrl);
$user = json_decode($userInfoResponse, true);


if (!isset($user['email'])) {
    echo "No se pudo obtener el correo electrónico del usuario.";
    exit;
}

$email = $user['email'];
$name = $user['name'];

$nameParts = explode(" ", $name);
$firstName = $nameParts[0] ?? '';
$lastName = isset($nameParts[1]) ? implode(" ", array_slice($nameParts, 1)) : '';

handleOAuthLogin($email, $firstName, $lastName, "Facebook OAuth");