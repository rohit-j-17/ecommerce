<?php
include_once 'OrderDatabase.php';
include_once '../session/SessionDatabase.php';
include_once '../../enum/EResponseCode.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

echo json_encode(perform($data));

function perform($data)
{

    $response = array("status" => 0, "msg" => "failed");

    if (!(isset($data['header']) && isset($data['payload']))) {
        return array("status" => 0, "msg" => "Invalid input");
    }

    $header = $data['header'];
    $payload = $data['payload'];

    if (!isset($header['sToken'])) {
        return array("status" => 0, "msg" => "Please add session token");
    }

    $token = $header['sToken'];
    $sessionDB = new SessionDatabase();

    $session = $sessionDB->getByToken($token);

    if ($session == null) {
        return array("status" => 0, "msg" => "Invalid session");
    }

    $sessionUser = $session['user'];

    if (!isset($payload['operation'])) {
        return array("status" => 0, "msg" => "Please specify operation");
    }

    $orderDB = new OrderDatabase();

    if ($payload['operation'] == 'GET') {

        if (isset($payload['orderId'])) {
            return $orderDB->get($sessionUser, $payload['orderId']);
        } else {
            return array("status" => 0, "msg" => "invalid input");
        }

    } else if ($payload['operation'] == 'GET_ALL') {

        return $orderDB->getAll($sessionUser);

    }
    // else if ($payload['operation'] == 'DELETE') {

    //     if (isset($payload['orderId'])) {
    //         $response = $orderDB->delete($sessionUser,$payload['orderId']);
    //     } else {
    //         $response = array("status" => 0, "msg" => "invalid input");
    //     }
    // }
    elseif ($payload['operation'] == "ADD") {

        if (isset($payload['dTotalAmount']) && isset($payload['jDeliveryMode']) && isset($payload['jPaymentMode']) &&
            isset($payload['orderItems']) && sizeof($payload['orderItems']) > 0) {

            return $orderDB->add($sessionUser, $payload['dTotalAmount'],
                $payload['jDeliveryMode'], $payload['jPaymentMode'], $payload['orderItems']);

        } else {
            return array("status" => 0, "msg" => "Invalid input");
        }
    } elseif ($payload['operation'] == "UPDATE_STATUS") {

        if (isset($payload['orderId']) && isset($payload['currentStatus'])) {

            return $orderDB->update($sessionUser, $payload['orderId'], $payload['currentStatus']);

        } else {
            return array("status" => 0, "msg" => "Invalid input");
        }
    } else {
        return array("status" => 0, "msg" => "operation not found");
    }

    return $response;
}
