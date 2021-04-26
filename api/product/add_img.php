<?php
include_once 'ProductDatabase.php';
include_once '../../enum/EResponseCode.php';

$response = array("status" => EResponseCode::FAILED, "msg" => "failed");

if (isset($_POST['input'])) {
    $imgObj = null;
    if (isset($_FILES['image'])) {
        $imgObj = $_FILES['image'];
    }

    if ($imgObj != null) {
        $json = $_POST['input'];
        $data = json_decode($json, true);

        $header = $data['header'];
        $payload = $data['payload'];

        if (isset($payload['operation'])) {
            if ($payload['operation'] == "ADD_IMG") {
                if (isset($payload['productId']) && $imgObj) {
                    $product_image = new ProductDatabase();
                    $response = $product_image->add_img($payload['productId'], $imgObj);

                } else {
                    return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid input");
                }
            }
        } else {
            return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Please specify operation");
        }
    } else {
        return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "no image added");
    }

} else {
    return $response = array("status" => EResponseCode::INVALID_INPUT, "msg" => "Invalid input");
}

echo json_encode($response);
