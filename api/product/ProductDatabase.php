<?php
include_once '../../config/Database.php';
include_once '../../config/Host.php';
include_once '../../utility/DateTimeHelper.php';
include_once '../../enum/EResponseCode.php';

class ProductDatabase extends Database
{
    public function get($productId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM product WHERE productId = $productId AND jStatus=1 ORDER BY productId desc";

            if ($result = $con->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data = $row;

                    $sql1 = "SELECT * FROM product_image WHERE productId = $productId AND jStatus=1 ORDER BY productImgId desc";
                    $data1 = array();
                    if ($result1 = $con->query($sql1)) {
                        while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) {
                            $data1[] = $row1;
                        }
                    }
                    $data['image'] = $data1;
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
            $sql = "SELECT * FROM product WHERE jStatus=1 ORDER BY productId desc";

            if ($result = $con->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data[] = $row;
                    $productId = $row['productId'];
                    $currentIndex = sizeof($data) - 1;

                    $sql2 = "SELECT * FROM product_image WHERE productId=$productId AND jStatus=1 ORDER BY productImgId desc";

                    $data2 = array();
                    if ($result2 = $con->query($sql2)) {
                        while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                            $data2[] = $row2;
                        }
                    }
                    $data[$currentIndex]['image'] = $data2;
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
            $sql = "SELECT * FROM product WHERE jStatus=1 AND categoryId = '$categoryId' ORDER BY productId desc";

            if ($result = $con->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data[] = $row;
                    $productId = $row['productId'];

                    $currentIndex = sizeof($data) - 1;

                    $sql2 = "SELECT * FROM product_image WHERE productId=$productId AND jStatus=1 ORDER BY productImgId desc";

                    $data2 = array();
                    if ($result2 = $con->query($sql2)) {
                        while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                            $data2[] = $row2;
                        }
                    }
                    $data[$currentIndex]['image'] = $data2;
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

    public function getAllBySubcategory($subcategoryId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM product WHERE jStatus=1 AND subcategoryId = '$subcategoryId' ORDER BY productId desc";

            if ($result = $con->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data[] = $row;
                    $productId = $row['productId'];

                    $currentIndex = sizeof($data) - 1;

                    $sql2 = "SELECT * FROM product_image WHERE productId=$productId AND jStatus=1 ORDER BY productImgId desc";

                    $data2 = array();
                    if ($result2 = $con->query($sql2)) {
                        while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                            $data2[] = $row2;
                        }
                    }
                    $data[$currentIndex]['image'] = $data2;
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

    public function delete($productId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;
        $con = $this->getConnection();
        try {
            $con->begin_transaction();
            $sql = "UPDATE product set jStatus =2, dtStamp='$dateTime' WHERE productId=$productId";
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
            return array("status" => EResponseCode::FAILED, "msg" => "failed to delete Product");
        }

        return $response;
    }

    public function add($categoryId, $subCategoryId, $sCode, $sName, $sDescription, $sPrice,$sSize, $imgObj)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        $productId = '';
        try {
            $con->begin_transaction();

            $sql = "INSERT INTO product VALUES('0','$categoryId','$subCategoryId','$sCode','$sName','$sDescription','$sPrice','$sSize','$dtStamp',1)";

            $result = $con->query($sql);
            $productId = $con->insert_id;
            $resultSet[$rsCounter++] = $result;

            $sImgPath = '';
            if (isset($imgObj)) {
                $tempPath = $imgObj['tmp_name'];
                $imgName = str_replace(" ", "_", $imgObj['name']);
                $upPath = 'upload/product/pro_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);
                $sImgPath = $upPath;

                $sql1 = "INSERT INTO product_image VALUES('0','$productId','$sImgPath','$dtStamp',1)";
                $result1 = $con->query($sql1);
                $resultSet[$rsCounter++] = $result1;

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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Product added successfully",
                "data" => array("productId" => $productId));
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Product to add ");
        }

        return $response;
    }

    public function update($productId, $categoryId, $subCategoryId, $sCode, $sName, $sDescription, $sPrice,$sSize, $imgIds)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        try {
            $con->begin_transaction();

            $sql = "UPDATE product set categoryId = '$categoryId',subCategoryId = '$subCategoryId', sCode = '$sCode', sName ='$sName', sDescription='$sDescription',sPrice = '$sPrice',sSize='$sSize,',dtStamp='$dtStamp' WHERE productId='$productId'";
            $result = $con->query($sql);
            $resultSet[$rsCounter++] = $result;

            if (sizeof($imgIds) > 0) {

                foreach ($imgIds as $imgrow) {
                    $sql1 = "UPDATE product_image set  dtStamp = '$dtStamp',jStatus =2 where productImgId = '$imgrow'";
                    $result1 = $con->query($sql1);
                    $resultSet[$rsCounter++] = $result1;
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Product  Updated Successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Failed To Update Product ");
        }

        return $response;
    }
    public function add_img($productId, $imgObj)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        try {
            $con->begin_transaction();

            $sImgPath = '';
            if (isset($imgObj)) {
                $tempPath = $imgObj['tmp_name'];
                $imgName = str_replace(" ", "_", $imgObj['name']);
                $upPath = 'upload/product/pro_' . md5('ppp' . $dtStamp) . '_' . $imgName;
                $newFilePath = (new Host())->getPath() . $upPath;
                move_uploaded_file($tempPath, $newFilePath);

                $sImgPath = $upPath;
            }

            $sql = "INSERT INTO product_image VALUES('0','$productId','$sImgPath','$dtStamp',1)";
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Product Image added successfully");
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Failed to add Product Image");
        }

        return $response;
    }

}
