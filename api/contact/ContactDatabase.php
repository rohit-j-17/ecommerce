<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../utility/DateTimeHelper.php';
include_once '../../enum/EUserRole.php';
include_once '../../enum/EDeliveryMode.php';
include_once '../../enum/EPaymentMode.php';
include_once '../../enum/EOrderStatus.php';
include_once '../../enum/EResponseCode.php';

header('Content-type: text/html; charset=utf-8');

class ContactDatabase extends Database
{
    public function getAll()
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $admin = EUserRole::ADMIN;
            $sql = "SELECT * FROM contact WHERE jStatus=$admin  ORDER BY contactId desc";

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
    public function add($sName, $sEmail, $sMobile, $sMessage)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        try {
            $con->begin_transaction();
            $customer = EUserRole::CUSTOMER;
            $sql = "INSERT INTO contact VALUES('0','$sName','$sEmail','$sMobile','$sMessage','$dtStamp',$customer)";
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Contact added successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Failed to add contact");
        }

        return $response;
    }


}
