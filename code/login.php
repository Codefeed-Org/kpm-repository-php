<?php
require "../vendor/autoload.php";
use \Firebase\JWT\JWT;
include_once 'database.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();
if ($data == null){
    http_response_code(401);
    die(json_encode(["message" => "Invalid data."]));
}
$pwd_peppered = hash_hmac("sha256", $data->password, "kpm");

$table_name = 'public.users';
$query = "SELECT id, \"password\", email FROM " . $table_name . " WHERE email = ?";

$stmt = $conn->prepare( $query );
$stmt->bindParam(1, $data->email);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(password_verify($pwd_peppered,$row['password'])){
        $secret_key = "YOUR_SECRET_KEY";
        $issuer_claim = "THE_ISSUER"; // this can be the servername
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim - 1; //not before in seconds
        $expire_claim = $issuedat_claim + 3600; // expire time in seconds
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $row['id'],
                "email" => $row['email']
        ));
        http_response_code(200);
        $jwt = JWT::encode($token, $secret_key);
        setcookie("jwt", $jwt, $expire_claim,"/");    
        echo json_encode(
            [
                'message' => 'Successful login.',
                'email' => $row['email'],
                'expireAt' => $expire_claim
            ]);            
    } else{
        http_response_code(401);
        die(json_encode(['message' => 'Login failed.']));
    }
} else {
    http_response_code(401);
    die(json_encode(["message" => "Login failed."]));
}