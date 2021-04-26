<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../utility/DateTimeHelper.php';
include_once '../../enum/EResponseCode.php';

class SubCategoryDatabase extends Database
{
    public function get($subCategoryId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM sub_category WHERE subCategoryId =$subCategoryId AND jStatus=1 ORDER BY subCategoryId desc";

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
            $sql = "SELECT * FROM sub_category WHERE jStatus=1 ORDER BY subCategoryId desc";

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
    public function getAllByCategory($categoryId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM sub_category WHERE jStatus=1 AND categoryId= '$categoryId' ORDER BY subCategoryId desc";

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

    public function delete($subCategoryId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $con = $this->getConnection();
        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;

        try {
            $con->begin_transaction();
            $sql = "UPDATE sub_category set jStatus =2, dtStamp='$dateTime' WHERE subCategoryId=$subCategoryId";
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

    public function add($categoryId, $sName, $sDescription, $imgObj)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        $subCategoryId = '';
        try {
            $con->begin_transaction();

            $sImgPath = '';
            if (isset($imgObj)) {
                $tempPath = $imgObj['tmp_name'];
                $imgName = str_replace(" ", "_", $imgObj['name']);
                $upPath = 'upload/subcategory/subcat_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);

                $sImgPath = $upPath;

            }

            $sql = "INSERT INTO sub_category VALUES ('0','$categoryId','$sName','$sDescription','$sImgPath','$dtStamp',1)";

            $result = $con->query($sql);
            $subCategoryId = $con->insert_id;
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Sub-Category added successfully",
                "data" => array("subCategoryId" => $subCategoryId));
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Failed to add Sub-Category");
        }

        return $response;
    }

    public function update($subCategoryId, $categoryId, $sName, $sDescription, $imgObj)
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
                $upPath = 'upload/subcategory/subcat_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);
                $sImgPath = $upPath;

                $sql = "UPDATE sub_category set categoryId = '$categoryId', sName ='$sName', sDescription='$sDescription',dtStamp='$dtStamp',sImgPath = '$sImgPath' WHERE subCategoryId='$subCategoryId'";
            } else {
                $sql = "UPDATE sub_category set  categoryId = '$categoryId', sName ='$sName', sDescription='$sDescription',dtStamp='$dtStamp' WHERE subCategoryId='$subCategoryId'";
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Updated  Successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "failed to Updated Sub Category");
        }

        return $response;
    }

}
