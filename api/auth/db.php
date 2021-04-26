<?php
include_once '../../config/Database.php';
include_once '../session/SessionDatabase.php';

class AuthDatabase extends Database
{

    public function login($username, $password, $role)
    {
        $response = array("status" => 0, "msg" => "Operation not performed");

        $db = new Database();
        $con = $db->getConnection();

        $sql = "SELECT * FROM user WHERE sEmail = '$username' AND sPasswordSha512 = '$password' AND jRole=$role Limit 1";

        if ($result = $con->query($sql)) {
            $row_cnt = $result->num_rows;
            if ($row_cnt > 0) {
                $resp = array();
                if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $session = new SessionDatabase();
                    $sessionId = $session->add($row['userId']);
                    if($sessionId==null){
                        return  array("status" => 0, "msg" => "failed to create session");  
                    }

                    $resp = $session->getById($sessionId);
                    if($resp==null){
                        return array("status" => 0, "msg" => "failed to get session");  
                    }
                    $response = array("status" => 1, "msg" => "Success", "data" => $resp);
                }
            } else {
                $response = array("status" => 0, "msg" => "Login Failed");
            }
        } else {
            $response = array("status" => 0, "msg" => "Operation Failed");
        }

        return $response;
    }

}
