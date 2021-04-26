<?php
include_once 'SessionDatabase.php';
include_once '../../enum/EResponseCode.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array("status" => 0, "msg" => "failed");

$session = new SessionDatabase();
$session->getByToken('abc');
exit;

if (isset($data['header']) && isset($data['payload'])) {

    $header = $data['header'];
    $payload = $data['payload'];

    if (isset($payload['operation'])) {
        $session = new SessionDatabase();

        if ($payload['operation'] == 'GET') {
            if (isset($payload['sessionId'])) {
                $response = $session->get($payload['sessionId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }
        } else if ($payload['operation'] == 'GET_ALL') {
            $response = $session->getAll();
        } else if ($payload['operation'] == 'DELETE') {

            if (isset($payload['sessionId'])) {
                $response = $session->delete($payload['sessionId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }

        }
          elseif($payload['operation'] == "ADD"){
                if (isset($payload['sToken']) && ($payload['sUserId'])) {

                    $session = new SessionDatabase();
                    $response = $session->add($payload['sToken'],$payload['sUserId']);
                } else {
                    $response = array("status" => 0, "msg" => "Invalid input");
                }
            }

    } else {

        $response = array("status" => 0, "msg" => "Please specfify operation");
    }

}

echo json_encode($response);
