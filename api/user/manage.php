<?php
include_once 'UserDatabase.php';
include_once '../../enum/EResponseCode.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array("status" => 0, "msg" => "failed");

if (isset($data['header']) && isset($data['payload'])) {

    $header = $data['header'];
    $payload = $data['payload'];

    if (isset($payload['operation'])) {
        $user = new UserDatabase();

        if ($payload['operation'] == 'GET') {
            if (isset($payload['userId'])) {
                $response = $user->get($payload['userId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }
        } else if ($payload['operation'] == 'GET_ALL') {
            $response = $user->getAll();
        } else if ($payload['operation'] == 'DELETE') {

            if (isset($payload['userId'])) {
                $response = $user->delete($payload['userId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }

        } elseif ($payload['operation'] == "ADD") {
            if (isset($payload['sFirstName']) && ($payload['sLastName']) && ($payload['sEmail']) &&
                isset($payload['sPasswordSha512']) && isset($payload['sMobile']) && isset($payload['jRole'])) {

                $user = new UserDatabase();
                $response = $user->add($payload['sFirstName'], $payload['sLastName'], $payload['sEmail'],
                    $payload['sPasswordSha512'], $payload['sMobile'], $payload['jRole']);

            } else {
                $response = array("status" => 0, "msg" => "Invalid input");
            }
        }
        elseif($payload['operation'] == "UPDATE") {
            if (isset ($payload['userId']) && ($payload['sFirstName']) && ($payload['sLastName']) && isset($payload['sEmail']) && isset($payload['sPasswordSha512']) && isset($payload['sMobile']) && isset($payload['jRole'])) {

                $user = new UserDatabase();
                $response = $user->update($payload['userId'],$payload['sFirstName'],$payload['sLastName'],$payload['sEmail'],$payload['sPasswordSha512'],$payload['sMobile'],$payload['jRole']);
            } else {
                $response = array("status" => 0, "msg" => "Invalid input");
            }
        }

    } else {

        $response = array("status" => 0, "msg" => "Please specfify operation");
    }
}

echo json_encode($response);
