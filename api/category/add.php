<?php
include_once 'CategoryDatabase.php';
include_once '../session/SessionDatabase.php';
include_once '../../enum/EUserRole.php';
include_once '../../enum/EResponseCode.php';

echo json_encode(perform());

function perform()
{
    $response = array("status" => EResponseCode::FAILED, "msg" => "failed");

    if (!isset($_POST['input'])) {
        return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid input");
    }

    $imgObj = null;
    if (isset($_FILES['image'])) {
        $imgObj = $_FILES['image'];
    }

    if ($imgObj == null) {
        return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "no image added");
    }

    $json = $_POST['input'];
    $data = json_decode($json, true);

    if (!(isset($data['header']) && isset($data['payload']))) {
        return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid input");
    }

    $header = $data['header'];
    $payload = $data['payload'];

    if (!isset($header['sToken'])) {
        return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Please add session token");
    }

    $token = $header['sToken'];
    $sessionDB = new SessionDatabase();

    $session = $sessionDB->getByToken($token);

    if ($session == null) {
        return $response = array("status" => EResponseCode::INVALID_SESSION, "msg" => "Invalid session");
    }

    if ($session['user']['jRole'] != EUserRole::ADMIN) {
        return $response = array("status" => EResponseCode::UNAUTHORIZED, "msg" => "Un-authorized operation");
    }

    if (!isset($payload['operation'])) {
        return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Please specify operation");
    }
 
    $categoryDB = new CategoryDatabase();

    if ($payload['operation'] == "ADD") {

        if (!(isset($payload['sName']) && isset($payload['sDescription']) && isset($imgObj))) {
            return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid input");
        }
        
        $response = $categoryDB->add($payload['sName'], $payload['sDescription'], $imgObj);
    } else {
        return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "operation not found");
    }

    return $response;

}


