<?php
include_once 'AddressDatabase.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array("status" => 0, "msg" => "failed");

if (isset($data['header']) && isset($data['payload'])) {

    $header = $data['header'];
    $payload = $data['payload'];

    if (isset($payload['operation'])) {
        $address = new AddressDatabase();

        if ($payload['operation'] == 'GET') {
            if (isset($payload['addressId'])) {
                $response = $address->get($payload['addressId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }
        } else if ($payload['operation'] == 'GET_ALL') {
            $response = $address->getAll();
        } else if ($payload['operation'] == 'DELETE') {

            if (isset($payload['addressId'])) {
                $response = $address->delete($payload['addressId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }

        } elseif ($payload['operation'] == "ADD") {
            if (isset($payload['userId']) && ($payload['sLine1']) && ($payload['sLine2']) &&
                isset($payload['sCity']) && isset($payload['sState']) && isset($payload['jPincode'])) {

                $address = new AddressDatabase();
                $response = $address->add($payload['userId'], $payload['sLine1'], $payload['sLine2'],
                    $payload['sCity'], $payload['sState'], $payload['jPincode']);

            } else {
                $response = array("status" => 0, "msg" => "Invalid input");
            }
        }
    } else {

        $response = array("status" => 0, "msg" => "Please specfify operation");
    }
}

echo json_encode($response);
