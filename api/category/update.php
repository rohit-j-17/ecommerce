<?php
include_once 'CategoryDatabase.php';
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

    if (isset($payload['operation'])) {
        if ($payload['operation'] == "UPDATE") {
            if (isset($payload['categoryId']) && isset($payload['sName']) && isset($payload['sDescription'])) {

                $category = new CategoryDatabase();
                $response = $category->update($payload['categoryId'],$payload['sName'], $payload['sDescription'], $imgObj);
            } else {
                return array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid input");
            }
        }
    } else {
        return array("status" => EResponseCode::INVALID_INPUT, "msg" => "operation not found");
    }

    return $response;

}
