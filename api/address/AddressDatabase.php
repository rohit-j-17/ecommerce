<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../utility/DateTimeHelper.php';

class AddressDatabase extends Database
{
    public function get($addressId)
    {
        $response = array("status" => 0, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM address WHERE addressId = $addressId AND jStatus=1 ORDER BY addressId desc";

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
            $response = array("status" => 1, "msg" => "Success", "data" => $data);
        } else {
            $response = array("status" => 0, "msg" => "failed to retrive data");
        }

        return $response;
    }

    public function getAll()
    {
        $response = array("status" => 0, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM address WHERE jStatus=1 ORDER BY addressId desc";

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
            $response = array("status" => 1, "msg" => "Success", "data" => $data);
        } else {
            $response = array("status" => 0, "msg" => "failed to retrive data");
        }

        return $response;
    }

    public function delete($addressId)
    {
        $response = array("status" => 0, "msg" => "Operation not performed");
        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;
        $con = $this->getConnection();
        try {
            $con->begin_transaction();
            $sql = "UPDATE address set jStatus =2, dtStamp='$dateTime' WHERE addressId=$addressId";
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
            $response = array("status" => 1, "msg" => "Deleted Successfully");
        } else {
            $response = array("status" => 0, "msg" => "failed to delete Address");
        }

        return $response;
    }

    public function add($userId, $sLine1, $sLine2, $sCity, $sState, $jPincode)
    {
        $response = array("status" => 0, "msg" => "Operation not performed");

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        try {
            $con->begin_transaction();
            $sql = "INSERT INTO address VALUES('0','$userId','$sLine1','$sLine2','$sCity','$sState','$jPincode','$dtStamp',1)";
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
            $response = array("status" => 1, "msg" => "Address added successfully");
        } else {
            $response = array("status" => 0, "msg" => "Failed to add Address");
        }

        return $response;
    }

}
