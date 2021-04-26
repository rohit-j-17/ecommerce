<?php
ob_start();

include_once 'db.php';
include_once '../../enum/EResponseCode.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array("status" => EResponseCode::FAILED, "msg" => "failed");

if (isset($data['email']) && isset($data['password']) && isset($data['role'])) {

    $username = $data['email'];
    $password = hash('sha512', $data['password']);
    $role = $data['role'];

    $db = new AuthDatabase();
	$response =  $db->login($username,$password,$role);
} else {
    $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "invalid input");
}

echo json_encode($response);
