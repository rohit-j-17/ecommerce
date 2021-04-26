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

class OrderDatabase extends Database
{
    public function get($sessionUser, $orderId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $data = array();
        $con = $this->getConnection();
        try {
            $sql = "SELECT * FROM `order` WHERE orderId = $orderId AND jStatus=1 ORDER BY orderId desc LIMIT 1";

            if ($result = $con->query($sql)) {
                if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $userId = $row['userId'];
                    if ($sessionUser['jRole']==EUserRole::CUSTOMER && $sessionUser['userId'] != $userId) {
                        return array("status" => 0, "msg" => "Un-authorized user");
                    }
                    $data = $row;

                    $sql1 = "SELECT * FROM `order_item` WHERE  orderId = $orderId AND jStatus=1 ORDER BY orderItemId desc";

                    $items = array();
                    if ($result1 = $con->query($sql1)) {
                        while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) {
                            $items[] = $row1;
                        }
                    }
                    $data['orderItems'] = $items;

                    if($sessionUser['jRole']==EUserRole::ADMIN){
                        $sql2 = "SELECT * FROM user WHERE userId = $userId AND jStatus=1 ORDER BY userId desc LIMIT 1";
                        if ($result2 = $con->query($sql2)) {
                            if ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                                $data['user'] = $row2;
                                if(isset($data['user']['sPasswordSha512'])){
                                    unset($data['user']['sPasswordSha512']);
                                }
                            }
                        }
                    }
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

    public function getAll($sessionUser)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        $data = array();
        $con = $this->getConnection();
        try {

            if ($sessionUser['jRole'] == EUserRole::ADMIN) {
                $sql = "SELECT * FROM `order` WHERE jStatus=1 ORDER BY orderId desc";
            } else {
                $sql = "SELECT * FROM `order` WHERE jStatus=1 AND userId = " . $sessionUser['userId'] . " ORDER BY orderId desc";
            }

            if ($result = $con->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $data[] = $row;
                    $orderId = $row['orderId'];
                    $userId = $row['userId'];

                    $currentIndex = sizeof($data) - 1;
                    $sql1 = "SELECT * FROM `order_item` WHERE  orderId = $orderId AND jStatus=1 ORDER BY orderItemId desc";

                    $items = array();
                    if ($result1 = $con->query($sql1)) {
                        while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) {
                            $items[] = $row1;
                        }
                    }
                    $data[$currentIndex]['orderItems'] = $items;

                    if($sessionUser['jRole']==EUserRole::ADMIN){
                        $sql2 = "SELECT * FROM user WHERE userId = $userId AND jStatus=1 ORDER BY userId desc LIMIT 1";
                        if ($result2 = $con->query($sql2)) {
                            if ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                                $data[$currentIndex]['user'] = $row2;
                                if(isset($data[$currentIndex]['user']['sPasswordSha512'])){
                                    unset($data[$currentIndex]['user']['sPasswordSha512']);
                                }
                            }
                        }
                    }
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

    public function delete($sessionUser, $orderId)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");
        $dateTime = DateTimeHelper::getCurrentDatetime();
        $resultSet = array();
        $rsCounter = 0;
        $con = $this->getConnection();
        try {
            $con->begin_transaction();
            $sql = "UPDATE order set jStatus =2, dtStamp='$dateTime' WHERE orderId=$orderId";
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
            return array("status" => EResponseCode::FAILED, "msg" => "failed to delete Order");
        }

        return $response;
    }
    public function add($sessionUser, $dTotalAmount, $jDeliveryMode, $jPaymentMode, $orderItemsRaw)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        if ($sessionUser['jRole'] != EUserRole::CUSTOMER) {
            return array("status" => 0, "msg" => "Un-authorized operation");
        }

        $userId = $sessionUser['userId'];

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();

        $orderId = null;

        try {
            $con->begin_transaction();

            $orderItems = array();

            for ($i = 0; $i < sizeof($orderItemsRaw); $i++) {
                if (!(isset($orderItemsRaw[$i]['productId']) && isset($orderItemsRaw[$i]['jQuantity'])
                    && isset($orderItemsRaw[$i]['sSize']))) {
                    return array("status" => 0, "msg" => "Invalid order items");
                }

                $itemExist = false;
                for ($j = 0; $j < sizeof($orderItems); $j++) {
                    if ($orderItems[$j]['productId'] == $orderItemsRaw[$i]['productId'] &&
                        $orderItems[$j]['sSize'] == $orderItemsRaw[$i]['sSize']) {
                        $orderItems[$j]['jQuantity'] += $orderItemsRaw[$i]['jQuantity'];
                        $itemExist = true;
                        break;
                    }
                }

                if (!$itemExist) {
                    array_push($orderItems, $orderItemsRaw[$i]);
                }
            }

            // echo "<pre>";
            // print_r($orderItems);

            $user = null;
            $jRole = EUserRole::CUSTOMER;
            $sql0 = "SELECT * FROM user WHERE userId = $userId AND jStatus=1 AND jRole = $jRole ORDER BY userId desc LIMIT 1";
            if ($result0 = $con->query($sql0)) {
                if ($row0 = $result0->fetch_array(MYSQLI_ASSOC)) {
                    $user = $row0;
                }
            }

            if ($user == null) {
                return array("status" => EResponseCode::FAILED, "msg" => "Customer not found");
            }

            $orderTotal = 0;
            $orderItemsFinal = array();

            foreach ($orderItems as $orderItem) {
                $productId = $orderItem['productId'];
                $jQuantity = $orderItem['jQuantity'];
                $sSize = $orderItem['sSize'];
                $product = null;

                $sql1 = "SELECT * FROM product WHERE productId = $productId AND jStatus=1 ORDER BY productId desc";
                if ($result1 = $con->query($sql1)) {
                    if ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) {
                        $product = $row1;
                    }
                }

                if ($product == null) {
                    return array("status" => EResponseCode::FAILED, "msg" => "Product not found");
                }

                if (strpos($product['sSize'], $sSize)===false) {
                    return array("status" => EResponseCode::FAILED, "msg" => "Product is not available in selected size");
                }

                $product['jQuantity'] = $jQuantity;
                $product['sSize'] = $sSize;

                $sql2 = "SELECT * FROM product_image WHERE productId = $productId AND jStatus=1 ORDER BY dtStamp desc LIMIT 1";
                if ($result2 = $con->query($sql2)) {
                    if ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                        $product['sImgPath'] = $row2['sImgPath'];
                    }
                }

                if (!isset($product['sImgPath'])) {
                    return array("status" => EResponseCode::FAILED, "msg" => "Something went wrong...! Image not found");
                }

                $orderTotal += ($product['sPrice'] * $jQuantity);
                array_push($orderItemsFinal, $product);
            }

            
            // echo "<pre>";
            // print_r($orderItemsFinal);
            // echo "dTotalAmount=".$dTotalAmount;
            // echo "orderTotal=".$orderTotal;

            if ($dTotalAmount != $orderTotal) {
                return array("status" => EResponseCode::FAILED, "msg" => "Incorrect order amount");
            }

            if (sizeof($orderItems) != sizeof($orderItemsFinal)) {
                return array("status" => EResponseCode::FAILED, "msg" => "Invalid order items found");
            }

            $currentStatus = EOrderStatus::ACCEPTED;
            $jDeliveryMode = EDeliveryMode::HOME;
            $jPaymentMode = EPaymentMode::COD;

            $sql = "INSERT INTO `order` VALUES('0','$userId','$dtStamp','$dTotalAmount','$currentStatus',' $jDeliveryMode','$jPaymentMode','$dtStamp',1)";
            $result = $con->query($sql);
            $orderId = $con->insert_id;
            $resultSet[$rsCounter++] = $result;

            foreach ($orderItemsFinal as $item) {
                $productId = $item['productId'];
                $jQuantity = $item['jQuantity'];
                $sSize = $item['sSize'];
                $sCode = $item['sCode'];
                $sName = $item['sName'];
                $sDescription = $item['sDescription'];
                $sImgPath = $item['sImgPath'];
                $sPrice = $item['sPrice'] * $jQuantity;

                $sql = "INSERT INTO order_item VALUES('0','$orderId','$productId','$jQuantity', '$sSize','$sCode','$sName','$sDescription','$sPrice','$sImgPath',' $dtStamp',1)";
                $result = $con->query($sql);
                $resultSet[$rsCounter++] = $result;
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
            return array("status" => EResponseCode::SUCCESS, "msg" => "Order added successfully", "data" => array("orderId" => $orderId));
        } else {
            return array("status" => EResponseCode::FAILED, "msg" => "Failed to add Order");
        }

        return $response;
    }

    public function update($sessionUser, $orderId, $currentStatus)
    {
        $response = array("status" => EResponseCode::FAILED, "msg" => "Operation not performed");

        if ($sessionUser['jRole'] != EUserRole::CUSTOMER) {
            if ($currentStatus != EOrderStatus . CANCELLED) {
                return array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid user action on order");
            }
        } else {
            if (!($currentStatus == EOrderStatus . ACCEPTED || $currentStatus == EOrderStatus . REJECTED ||
                $currentStatus == EOrderStatus . DELIVERED)) {
                return array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid user action on order");
            }
        }

        $con = $this->getConnection();
        $resultSet = array();
        $rsCounter = 0;
        $dtStamp = DateTimeHelper::getCurrentDatetime();
        try {
            $con->begin_transaction();

            if ($sessionUser['jRole'] != EUserRole::CUSTOMER) {
                $sql0 = "SELECT * FROM `order` WHERE orderId = $orderId AND jStatus=1 ORDER BY orderId desc LIMIT 1";

                if ($result0 = $con->query($sql0)) {
                    if ($row0 = $result0->fetch_array(MYSQLI_ASSOC)) {
                        if ($sessionUser['userId'] != $row['userId']) {
                            return array("status" => 0, "msg" => "Un-authorized user");
                        }
                    }
                }
            }

            $sql = "UPDATE `order` set userId ='$userId', dtPlaced = '$dtStamp', dTotalAmount = '$dTotalAmpount', currentStatus='$currentStatus',jDeliveryMode='$jDeliveryMode',jPaymentMode = '$jPaymentMode', dtStamp = '$dtStamp' WHERE orderId='$orderId'";
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
            return array("status" => EResponseCode::FAILED, "msg" => "failed to Updated order");
        }

        return $response;
    }

}
