<?php
include_once 'CategoryDatabase.php';
include_once '../../enum/EResponseCode.php';
$json = file_get_contents('php://input');
$data = json_decode($json, true);

echo json_encode(perform($data));

function perform($data)
{
    $response = array("status" => EResponseCode::FAILED, "msg" => "failed");

    if (!(isset($data['header']) && isset($data['payload']))) {
        return array("status" => 0, "msg" => "Invalid input");
    }

    $header = $data['header'];
    $payload = $data['payload'];

    if (!isset($payload['operation'])) {
        return array("status" => EResponseCode::INVALID_INPUT, "msg" => "Please specify operation");
    }

    $category = new CategoryDatabase();

    if ($payload['operation'] == 'GET') {
        if (isset($payload['categoryId'])) {
            return $category = new CategoryDatabase();
            ->get($payload['categoryId']);
        } else {
            return array("status" => EResponseCode::INVALID_INPUT, "msg" => "invalid input");
        }
    } else if ($payload['operation'] == 'GET_ALL') {
        return $category = new CategoryDatabase();
        ->getAll();
    } else if ($payload['operation'] == 'DELETE') {

        if (isset($payload['categoryId'])) {
            return $category = new CategoryDatabase();
            ->delete($payload['categoryId']);
        } else {
            return array("status" => EResponseCode::INVALID_INPUT, "msg" => "invalid input");
        }
    } else {
        return array("status" => EResponseCode::INVALID_INPUT, "msg" => "operation not found");
    }

    return $response;

}
