<?php
require_once __DIR__ . '/../../vendor/autoload.php';

function requestURI()
{

    $requestURI = $_ENV['BASE_URL'] . "/";
    // $requestURI = 'http://' . $_SERVER['HTTP_HOST'] . "/";

    return $requestURI;
}