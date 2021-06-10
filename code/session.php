<?php
require "../vendor/autoload.php";
use \Firebase\JWT\JWT;

function isValidSession()
{
    $result = false;
    if (isset($_COOKIE['jwt'])){
        $token =(array) JWT::decode($_COOKIE['jwt'], "YOUR_SECRET_KEY",array('HS256'));
        $now = time();
        if ($token['exp'] > $now && $token['nbf'] <= $now){
          $result=true;
        }
    }
    return $result;
}