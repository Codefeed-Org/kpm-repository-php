<?php
require "../vendor/autoload.php";
use \Firebase\JWT\JWT;

if(isset($_COOKIE['jwt'])){
    setcookie('jwt',"",time()-3600,"/");
}
header("Location: /index.php");
exit;