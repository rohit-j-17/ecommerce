<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../utility/DateTimeHelper.php';
include_once '../../enum/EResponseCode.php';

class BannerDatabase extends Database
{
    public function get($bannerId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM banner WHERE bannerId =$bannerId AND jStatus=1 ORDER BY bannerId desc";

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
            $response = array("status" => EResponseCode::SUCCESS, "msg" => "Success", "data" => $data);
        } else {
            $response = array("status" => EResponseCode::FAILED, "msg" => "failed to retrive data");
        }

        return $response;
    }

    public function getAll()
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM banner WHERE jStatus=1 ORDER BY bannerId desc";

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
            $response = array("status" => EResponseCode::SUCCESS, "msg" => "Success", "data" => $data);
        } else {
            $response = array("status" => EResponseCode::FAILED, "msg" => "failed to retrive data");
        }

        return $response;
    }

    public function delete($bannerId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;
        $con = $this->getConnection();
        try {
            $con->begin_transaction();
            $sql = "UPDATE banner set jStatus =2, dtStamp='$dateTime' WHERE bannerId=$bannerId";
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
            $response = array("status" => EResponseCode::SUCCESS, "msg" => "Deleted Successfully");
        } else {
            $response = array("status" => EResponseCode::FAILED, "msg" => "failed to delete banner");
        }

        return $response;
    }

    public function add($sName, $sDescription, $imgObj)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        $categoryId = '';
        try {
            $con->begin_transaction();

            $sImgPath = '';
            if (isset($imgObj)) {
                $tempPath = $imgObj['tmp_name'];
                $imgName = str_replace(" ", "_", $imgObj['name']);
                $upPath = 'upload/banner/ban_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);

                $sImgPath = $upPath;
            }

            $sql = "INSERT INTO banner VALUES('0','$sName','$sDescription','$sImgPath',' $dtStamp',1)";
            $result = $con->query($sql);
            $bannerId = $con->insert_id;
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
            $response = array("status" => EResponseCode::SUCCESS, "msg" => "Banner added successfully",
                "data" => array("bannerId" => $bannerId));
        } else {
            $response = array("status" => EResponseCode::FAILED, "msg" => "Failed to add category");
        }

        return $response;
    }

    public function update( $bannerId,$sName, $sDescription, $imgObj)
    {

        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();
        try {
            $con->begin_transaction();
            if ($imgObj != null) {
                $sImgPath = '';
                $tempPath = $imgObj['tmp_name'];
                $imgName = str_replace(" ", "_", $imgObj['name']);
                $upPath = 'upload/banner/ban_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);
                $sImgPath = $upPath;

                $sql = "UPDATE banner set sName ='$sName', sDescription='$sDescription',dtStamp='$dtStamp',sImgPath = '$sImgPath' WHERE bannerId='$bannerId'";
            } else {
                $sql = "UPDATE banner set sName ='$sName', sDescription='$sDescription',dtStamp='$dtStamp' WHERE bannerId='$bannerId'";
            }
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
            $response = array("status" => EResponseCode::SUCCESS, "msg" => "Updated Successfully");
        } else {
            $response = array("status" => EResponseCode::FAILED, "msg" => "failed to Updated banner");
        }

        return $response;
    }

}
