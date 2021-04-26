<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../config/AppConfig.php';
include_once '../../utility/DateTimeHelper.php';
include_once '../../enum/EResponseCode.php';

class SessionDatabase extends Database
{
    public function getById($sessionId)
    {
        $responseCode = 0 ;
        $con = $this->getConnection();
        $data = null;
        
        try {
            $sql = "SELECT * FROM session WHERE sessionId =$sessionId AND jStatus=1 ORDER BY sessionId desc";

            if ($result = $con->query($sql)) {
                if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data = array();
                    $data['session'] = $row;
                    $userId = $row['userId'];

                    $sql1 = "SELECT * FROM user WHERE userId =$userId AND jStatus=1 ORDER BY userId desc LIMIT 1";

                    if ($result1 = $con->query($sql1)) {
                        if ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) {
                            $data['user'] = $row1;
                            if(isset($data['user']['sPasswordSha512'])){
                                unset($data['user']['sPasswordSha512']);
                            }
                            $responseCode = 1;
                        }
                    }
                }
            }
        } catch (Exception $e) {
        } finally {
            $con->close();
        }

        if ($responseCode == 1) {
            return $data;
        }

        return null;
    }

    public function getByToken($token)
    {
        $responseCode = 0 ;
        $dtStamp = DateTimeHelper::getCurrentDatetime();
        $data = null;
        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        try {
            $sql = "SELECT * FROM session WHERE sToken = '$token' AND jStatus=1
            AND '$dtStamp' >= dtStart AND '$dtStamp' <= dtEnd
            ORDER BY sessionId desc LIMIT 1";

            if ($result = $con->query($sql)) {
                if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data = $row;

                    $sessionId = $row['sessionId'];
                    $userId = $row['userId'];
                    $sql1 = "SELECT * FROM user WHERE userId =$userId AND jStatus=1 ORDER BY userId desc LIMIT 1";

                    if ($result1 = $con->query($sql1)) {
                        if ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) {
                            $data['user'] = $row1;
                            if(isset($data['user']['sPasswordSha512'])){
                                unset($data['user']['sPasswordSha512']);
                            }
                            $responseCode = 1;
                        }
                    }

                    //auto update session on last day (dtEnd)
                    $dtEnd = new DateTime($row['dtEnd']);
                    $todayDate = (new DateTime($dtStamp))->format('Y-m-d');
                    $endDate = $dtEnd->format('Y-m-d');

                    if($todayDate==$endDate){
                        $dtUp = DateTimeHelper::addDaysInDatetime($dtEnd,AppConfig::SESSION_DURATION_DAYS);
                        $sql2 = "UPDATE session set dtEnd='$dtUp' WHERE sessionId=$sessionId";
                        $result2 = $con->query($sql2);
                        $resultSet[$rsCounter++] = $result2;
                    }
                }
            }
        } catch (Exception $e) {
        } finally {
            $con->close();
        }

        return $data;
    }


    public function delete($sessionId)
    {
        $response = array("status" => 0, "msg" => "Operation not performed");
        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;
        $con = $this->getConnection();
        try {
            $con->begin_transaction();
            $sql = "UPDATE session set jStatus =2, dtStamp='$dateTime' WHERE sessionId=$sessionId";
            $result = $con->query($sql);
            $resultSet[$rsCounter++] = $result;
        } catch (Exception $e) {
        } finally {
            if (in_array(false, $resultSet)) {
                $con->rollback();
                $responseCode = 0;
            } else {
                $con->commit();
                $responseCode = 1;
            }
            $con->close();
        }

        if ($responseCode == 1) {
            $response = array("status" => 0, "msg" => "Deleted Successfully");
        } else {
            $response = array("status" => 0, "msg" => "failed to delete session");
        }

        return $response;
    }

    public function add($sUserId)
    {
        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();
        $dtEnd = DateTimeHelper::addDaysInDatetime($dtStamp,AppConfig::SESSION_DURATION_DAYS);

        $sToken = hash('sha512', uniqid());

        $sessionId = '';
        try {
            $con->begin_transaction();
            $sql = "INSERT INTO session VALUES('0','$sUserId','$sToken','$dtStamp','$dtEnd','$dtStamp',1)";
            $result = $con->query($sql);
            $sessionId = $con->insert_id;
            $resultSet[$rsCounter++] = $result;

        } catch (Exception $e) {
        } finally {
            if (in_array(false, $resultSet)) {
                $con->rollback();
                $responseCode = 0;
            } else {
                $con->commit();
                $responseCode = 1;
            }
            $con->close();
        }

        if ($responseCode == 1) {
            return $sessionId;
        }

        return null;
    }

}
