<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../utility/DateTimeHelper.php';
include_once '../../enum/EResponseCode.php';

class CategoryDatabase extends Database
{
    public function get($catId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM category WHERE categoryId =$catId AND jStatus=1 ORDER BY categoryId desc";

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
            $sql = "SELECT * FROM category WHERE jStatus=1 ORDER BY categoryId desc";

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

    public function delete($categoryId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;
        $con = $this->getConnection();
        try {
            $con->begin_transaction();
            $sql = "UPDATE category set jStatus =2, dtStamp='$dateTime' WHERE categoryId=$categoryId";
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
            return array("status" => EResponseCode::FAILED, "msg" => "failed to delete category");
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
                $upPath = 'upload/category/cat_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);

                $sImgPath = $upPath;
            }

            $sql = "INSERT INTO category VALUES('0','$sName','$sDescription','$sImgPath',' $dtStamp',1)";
            $result = $con->query($sql);
            $categoryId = $con->insert_id;
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Cagtegory added successfully",
                "data" => array("categoryId" => $categoryId));
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Failed to add category");
        }

        return $response;
    }

    public function update($categoryId, $sName, $sDescription, $imgObj)
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
                $upPath = 'upload/category/cat_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);
                $sImgPath = $upPath;

                $sql = "UPDATE category set sName ='$sName', sDescription='$sDescription',dtStamp='$dtStamp',sImgPath = '$sImgPath' WHERE categoryId='$categoryId'";
            } else {
                $sql = "UPDATE category set sName ='$sName', sDescription='$sDescription',dtStamp='$dtStamp' WHERE categoryId='$categoryId'";
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Updated Successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "failed to Updated category");
        }

        return $response;
    }

}
