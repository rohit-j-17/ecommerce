<?php
include_once 'ContactDatabase.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array("status" => 0, "msg" => "failed");

if (isset($data['header']) && isset($data['payload'])) {

    $header = $data['header'];
    $payload = $data['payload'];

    if (isset($payload['operation'])) {
        $contact = new ContactDatabase();

         if ($payload['operation'] == 'GET_ALL') {
            $response = $contact->getAll();

        }  elseif ($payload['operation'] == "ADD") {
            if (isset($payload['sName']) && ($payload['sEmail']) && ($payload['sMobile']) &&
                isset($payload['sMessage'])) {

                $contact = new ContactDatabase();
                $response = $contact->add($payload['sName'], $payload['sEmail'], $payload['sMobile'],
                    $payload['sMessage']);

            } else {
                $response = array("status" => 0, "msg" => "Invalid input");
            }
        }
    } else {

        $response = array("status" => 0, "msg" => "Please specfify operation");
    }
}

echo json_encode($response);
