<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../utility/DateTimeHelper.php';
include_once '../../utility/OtpUtility.php';
include_once '../../enum/EUserRole.php';
include_once '../../enum/EUserVerification.php';
include_once '../../email/EmailSender.php';
include_once '../../enum/EResponseCode.php';

class UserDatabase extends Database
{
    public function get($userId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM user WHERE userId = $userId AND jStatus=1 ORDER BY userId desc";

            if ($result = $con->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data = $row;
                }
            }
        } catch (Exception $e) {
        } finally {
            $con->close();
        }

        if (sizeof($data) > 0) {
            return array("status" => EResponseCode::SUCCESS, "msg" => "Success", "data" => $data);
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "failed to retrive data");
        }

        return $response;
    }

    public function getAll()
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM user WHERE jStatus=1 ORDER BY userId desc";

            if ($result = $con->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data[] = $row;
                }
            }
        } catch (Exception $e) {
        } finally {
            $con->close();
        }

        if (sizeof($data) > 0) {
            return array("status" => EResponseCode::SUCCESS, "msg" => "Success", "data" => $data);
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "failed to retrive data");
        }

        return $response;
    }

    public function delete($userId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;
        $con = $this->getConnection();
        try {
            $con->begin_transaction();
            $sql = "UPDATE user set jStatus =2, dtStamp='$dateTime' WHERE userId=$userId";
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Deleted Successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "failed to delete User");
        }

        return $response;
    }

    public function add($sFirstName, $sLastName, $sEmail,$sPasswordSha512, $sMobile,$jRole)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        $userId =null;

        try {
            $con->begin_transaction();
            $jVerfied = UserVerification::UNVERIFIED;
            $jRole = EUserRole::CUSTOMER;
            $password = hash('sha512', 'sPasswordSha512');

            $sOtp = hash('sha512', OtpUtility::generateNumericOTP());

            $sql = "INSERT INTO user VALUES('0','$sFirstName','$sLastName','$sEmail','$password','$sMobile','$jRole','$jVerfied','$sOtp','$dtStamp',1)";
            $result = $con->query($sql);
            $userId = $con->insert_id;
            $resultSet[$rsCounter++] = $result;

            if($result){
                if(!(new EmailSender)->sendOtp($sEmail,$sOtp)){
                    $response = array("status" => 0, "msg" => "Failed to send otp");
                    $resultSet[$rsCounter++] = false;
                }
            }
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "User added successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Failed to add User");
        }

        return $response;
    }

    public function update($userId,$sFirstName, $sLastName, $sEmail, $sMobile)
    {

        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();
        try {
            $con->begin_transaction();

            $sql = "UPDATE `user` set sFirstName ='$sFirstName', sLastName = '$sLastName', sEmail = '$sEmail',sMobile='$sMobile', dtStamp = '$dtStamp' WHERE userId='$userId'";
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Updated Successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "failed to Updated user");
        }

        return $response;
    }

}
