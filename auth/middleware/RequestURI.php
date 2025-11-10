<?php


function requestURI()
{

    $requestURI = 'http://' . $_SERVER['HTTP_HOST'] . "/";

    return $requestURI;
}